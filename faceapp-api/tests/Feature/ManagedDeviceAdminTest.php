<?php

namespace Tests\Feature;

use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagedDeviceAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_a_device_does_not_delete_it(): void
    {
        $device = Device::query()->create([
            'device_key' => 'DEVICE1234567890',
            'name' => 'Main Gate',
            'secret' => 'secret123',
            'is_managed' => true,
            'is_active' => true,
            'display_order' => 0,
            'person_type_default' => 1,
            'verify_style_default' => 1,
            'ac_group_number_default' => 0,
            'photo_quality_default' => 1,
        ]);

        $response = $this->put(route('admin.devices.update', $device), [
            'device_key' => 'DEVICE1234567890',
            'name' => 'Updated Main Gate',
            'client_name' => 'Client A',
            'branch_name' => 'Branch 1',
            'secret' => '',
            'is_active' => '1',
            'display_order' => 2,
            'person_type_default' => 1,
            'verify_style_default' => 1,
            'ac_group_number_default' => 0,
            'photo_quality_default' => 1,
            'notes' => 'Updated notes',
        ]);

        $response
            ->assertRedirect(route('admin.devices.edit', $device))
            ->assertSessionHas('status', 'Device updated.');

        $this->assertDatabaseCount('devices', 1);
        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'device_key' => 'DEVICE1234567890',
            'name' => 'Updated Main Gate',
            'client_name' => 'Client A',
            'branch_name' => 'Branch 1',
            'display_order' => 2,
            'is_managed' => true,
            'is_active' => true,
        ]);
    }
}
