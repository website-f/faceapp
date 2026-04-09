<?php

namespace App\Services;

use App\Models\Device;
use App\Models\ManagedUser;
use App\Models\ManagedUserDeviceSync;
use Illuminate\Support\Collection;
use RuntimeException;
use Throwable;

class ManagedUserSyncService
{
    public function __construct(
        protected readonly GatewaySdkClient $gateway,
        protected readonly SystemSettingsService $settings,
    ) {}

    public function activeManagedDevices(): Collection
    {
        return Device::query()
            ->where('is_managed', true)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }

    public function syncUserAcrossActiveDevices(ManagedUser $user): array
    {
        $devices = $this->activeManagedDevices();

        if ($devices->isEmpty()) {
            throw new RuntimeException('No active managed devices are configured.');
        }

        return $devices
            ->map(fn (Device $device): array => $this->syncUserToDeviceWithResult($user, $device))
            ->all();
    }

    public function syncUserToDevice(ManagedUser $user, Device $device): ManagedUserDeviceSync
    {
        $client = $this->gateway->forDevice($device);
        $sync = ManagedUserDeviceSync::firstOrNew([
            'managed_user_id' => $user->id,
            'device_id' => $device->id,
        ]);

        $mergeResponse = $client->upsertPerson([
            'employee_id' => $user->employee_id,
            'name' => $user->name,
            'person_type' => $user->person_type ?: $device->person_type_default,
            'verify_style' => $user->verify_style ?: $device->verify_style_default,
            'ac_group_number' => $user->ac_group_number ?: $device->ac_group_number_default,
        ]);

        $verificationResponse = $this->verifyPersonSync($client, $user->employee_id);

        if (! $client->personExists($verificationResponse)) {
            $sync->fill([
                'sync_status' => 'failed',
                'gateway_person_response' => [
                    'merge_response' => $mergeResponse,
                    'verification_response' => $verificationResponse,
                ],
                'last_error_message' => 'Gateway did not confirm the user record.',
            ])->save();

            throw new RuntimeException('Gateway did not confirm the user record on device '.$device->display_name.'.');
        }

        $sync->fill([
            'sync_status' => 'synced',
            'last_synced_at' => now(),
            'last_error_message' => null,
            'gateway_person_response' => [
                'merge_response' => $mergeResponse,
                'verification_response' => $verificationResponse,
            ],
        ])->save();

        return $sync->fresh();
    }

    public function deleteUserAcrossActiveDevices(ManagedUser $user): array
    {
        $devices = $this->activeManagedDevices();

        return $devices
            ->map(fn (Device $device): array => $this->deleteUserFromDeviceWithResult($user, $device))
            ->all();
    }

    protected function syncUserToDeviceWithResult(ManagedUser $user, Device $device): array
    {
        try {
            $sync = $this->syncUserToDevice($user, $device);

            return [
                'device_id' => $device->id,
                'device_key' => $device->device_key,
                'device_name' => $device->display_name,
                'status' => 'synced',
                'sync' => $sync->toArray(),
            ];
        } catch (Throwable $exception) {
            return [
                'device_id' => $device->id,
                'device_key' => $device->device_key,
                'device_name' => $device->display_name,
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ];
        }
    }

    protected function deleteUserFromDeviceWithResult(ManagedUser $user, Device $device): array
    {
        $client = $this->gateway->forDevice($device);
        $sync = ManagedUserDeviceSync::firstOrNew([
            'managed_user_id' => $user->id,
            'device_id' => $device->id,
        ]);

        try {
            $response = $client->deletePerson($user->employee_id);

            $sync->fill([
                'sync_status' => 'deleted',
                'face_status' => 'deleted',
                'last_error_message' => null,
                'gateway_person_response' => $response,
            ])->save();

            return [
                'device_id' => $device->id,
                'device_key' => $device->device_key,
                'device_name' => $device->display_name,
                'status' => 'deleted',
            ];
        } catch (Throwable $exception) {
            $sync->fill([
                'sync_status' => 'failed',
                'last_error_message' => $exception->getMessage(),
            ])->save();

            return [
                'device_id' => $device->id,
                'device_key' => $device->device_key,
                'device_name' => $device->display_name,
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ];
        }
    }

    protected function verifyPersonSync(GatewaySdkClient $client, string $employeeId): array
    {
        $attempts = max(1, $this->settings->personVerifyRetries());
        $delayMilliseconds = max(0, $this->settings->personVerifyDelayMs());
        $lastResponse = [];

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $lastResponse = $client->findPerson($employeeId);

            if ($client->personExists($lastResponse)) {
                return $lastResponse;
            }

            if ($attempt < $attempts && $delayMilliseconds > 0) {
                usleep($delayMilliseconds * 1000);
            }
        }

        return $lastResponse;
    }
}
