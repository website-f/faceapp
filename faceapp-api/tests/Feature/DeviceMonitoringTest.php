<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Enrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeviceMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_heartbeat_callbacks(): void
    {
        $response = $this->post('/api/device/callbacks/heartbeat', [
            'deviceKey' => 'DEVICE1234567890',
            'time' => '1712659200000',
            'ip' => '10.1.1.5',
            'personCount' => '12',
            'faceCount' => '9',
            'version' => '2.0.0',
            'freeDiskSpace' => '1024',
        ]);

        $response
            ->assertOk()
            ->assertSeeText('ok');

        $device = Device::query()->where('device_key', 'DEVICE1234567890')->firstOrFail();

        $this->assertSame('10.1.1.5', $device->last_ip);
        $this->assertSame(12, $device->person_count);
        $this->assertSame(9, $device->face_count);
        $this->assertSame('2.0.0', $device->last_version);

        $this->assertDatabaseHas('device_events', [
            'device_key' => 'DEVICE1234567890',
            'event_type' => 'heartbeat',
        ]);
    }

    public function test_dashboard_displays_devices_and_recent_enrollments(): void
    {
        Device::create([
            'device_key' => 'DEVICE1234567890',
            'last_ip' => '10.1.1.5',
            'last_seen_at' => now(),
        ]);

        Enrollment::create([
            'public_id' => 'ENR-TEST0001',
            'employee_id' => 'EMP-001',
            'name' => 'Nadia Tan',
            'status' => 'failed',
            'device_key' => 'DEVICE1234567890',
            'error_message' => 'Gateway did not confirm the uploaded face.',
        ]);

        config()->set('gateway.device_key', 'DEVICE1234567890');
        config()->set('gateway.secret', 'secret123');
        config()->set('gateway.base_url', 'http://gateway.local/api');

        Http::fake([
            'http://gateway.local/api/device/get' => Http::response([
                'code' => '200',
                'msg' => 'ok',
                'data' => [
                    'deviceKey' => 'DEVICE1234567890',
                    'online' => true,
                ],
            ]),
        ]);

        $response = $this->get('/devices');

        $response
            ->assertOk()
            ->assertSeeText('FaceApp Device Monitor')
            ->assertSeeText('DEVICE1234567890')
            ->assertSeeText('Gateway Reachable')
            ->assertSeeText('Nadia Tan')
            ->assertSeeText('Gateway did not confirm the uploaded face.');
    }

    public function test_it_pushes_callback_urls_to_the_gateway(): void
    {
        config()->set('app.url', 'https://api.example.com');
        config()->set('gateway.device_key', 'DEVICE1234567890');
        config()->set('gateway.secret', 'secret123');
        config()->set('gateway.base_url', 'http://gateway.local/api');
        config()->set('gateway.monitoring.heartbeat_interval_seconds', 75);

        $expectedRecordUrl = route('api.devices.callbacks.record');
        $expectedHeartbeatUrl = route('api.devices.callbacks.heartbeat');
        $expectedPersonRegistrationUrl = route('api.devices.callbacks.person-registration');

        Http::fake([
            'http://gateway.local/api/device/setSevConfig' => Http::response([
                'code' => '200',
                'msg' => 'ok',
                'data' => true,
            ]),
        ]);

        $response = $this->post('/devices/configure-callbacks');

        $response
            ->assertRedirect('/devices')
            ->assertSessionHas('status');

        Http::assertSent(function (ClientRequest $request) use ($expectedHeartbeatUrl, $expectedPersonRegistrationUrl, $expectedRecordUrl): bool {
            return $request->url() === 'http://gateway.local/api/device/setSevConfig'
                && $request['deviceKey'] === 'DEVICE1234567890'
                && $request['secret'] === 'secret123'
                && $request['sevUploadRecRecordUrl'] === $expectedRecordUrl
                && $request['sevUploadDevHeartbeatUrl'] === $expectedHeartbeatUrl
                && $request['sevUploadRegPersonUrl'] === $expectedPersonRegistrationUrl
                && (string) $request['sevUploadDevHeartbeatInterval'] === '75';
        });
    }
}
