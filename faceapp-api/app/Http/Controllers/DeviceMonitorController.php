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
            'callbackUrls' => [
                'record' => route('api.devices.callbacks.record'),
                'heartbeat' => route('api.devices.callbacks.heartbeat'),
                'person_registration' => route('api.devices.callbacks.person-registration'),
            ],
            'heartbeatIntervalSeconds' => (int) config('gateway.monitoring.heartbeat_interval_seconds', 60),
            'onlineWindowSeconds' => (int) config('gateway.monitoring.online_window_seconds', 180),
        ]);
    }

    public function configureCallbacks(GatewaySdkClient $gateway): RedirectResponse
    {
        try {
            $response = $gateway->setServerConfig([
                'sevUploadRecRecordUrl' => route('api.devices.callbacks.record'),
                'sevUploadDevHeartbeatUrl' => route('api.devices.callbacks.heartbeat'),
                'sevUploadRegPersonUrl' => route('api.devices.callbacks.person-registration'),
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
}
