<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceEvent;
use App\Models\Enrollment;
use App\Services\GatewaySdkClient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class DeviceMonitorController extends Controller
{
    public function index(GatewaySdkClient $gateway): View
    {
        $gatewayStatus = null;
        $gatewayError = null;
        $callbackUrls = [
            'record' => $this->callbackUrl('/api/device/callbacks/records'),
            'heartbeat' => $this->callbackUrl('/api/device/callbacks/heartbeat'),
            'person_registration' => $this->callbackUrl('/api/device/callbacks/person-registrations'),
        ];

        try {
            $gatewayStatus = $gateway->deviceStatus();
        } catch (Throwable $exception) {
            $gatewayError = $exception->getMessage();
        }

        return view('devices.index', [
            'configuredDeviceKey' => config('gateway.device_key'),
            'gatewayStatus' => $gatewayStatus,
            'gatewayError' => $gatewayError,
            'devices' => Device::query()
                ->orderByDesc('last_seen_at')
                ->orderByDesc('updated_at')
                ->get(),
            'recentEvents' => DeviceEvent::query()
                ->latest('event_time')
                ->latest()
                ->limit(20)
                ->get(),
            'recentEnrollments' => Enrollment::query()
                ->latest()
                ->limit(20)
                ->get(),
            'callbackUrls' => $callbackUrls,
            'heartbeatIntervalSeconds' => (int) config('gateway.monitoring.heartbeat_interval_seconds', 60),
            'onlineWindowSeconds' => (int) config('gateway.monitoring.online_window_seconds', 180),
        ]);
    }

    public function configureCallbacks(GatewaySdkClient $gateway): RedirectResponse
    {
        $callbackUrls = [
            'record' => $this->callbackUrl('/api/device/callbacks/records'),
            'heartbeat' => $this->callbackUrl('/api/device/callbacks/heartbeat'),
            'person_registration' => $this->callbackUrl('/api/device/callbacks/person-registrations'),
        ];

        try {
            $response = $gateway->setServerConfig([
                'sevUploadRecRecordUrl' => $callbackUrls['record'],
                'sevUploadDevHeartbeatUrl' => $callbackUrls['heartbeat'],
                'sevUploadRegPersonUrl' => $callbackUrls['person_registration'],
                'sevUploadDevHeartbeatInterval' => (int) config('gateway.monitoring.heartbeat_interval_seconds', 60),
            ]);
        } catch (Throwable $exception) {
            return redirect()
                ->route('devices.monitor.index')
                ->with('error', 'Failed to push callback URLs to the gateway: '.$exception->getMessage());
        }

        return redirect()
            ->route('devices.monitor.index')
            ->with('status', 'Callback URLs pushed to the device through the gateway.')
            ->with('gateway_config_response', $response);
    }

    protected function callbackUrl(string $path): string
    {
        $baseUrl = (string) config('gateway.monitoring.callback_base_url', '');

        if ($baseUrl !== '') {
            return rtrim($baseUrl, '/').'/'.ltrim($path, '/');
        }

        return url($path);
    }
}
