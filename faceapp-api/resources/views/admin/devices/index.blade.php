@extends('admin.layout', ['title' => 'Manage Devices'])

@section('content')
    <section class="header">
        <div>
            <h2>Devices</h2>
            <p>Add devices for each client and branch, store the device key and secret here, and trigger operational actions like heartbeat callback setup, open door, and reboot.</p>
        </div>
    </section>

    <div class="grid cols-2">
        <section class="card">
            <h3>{{ $editingDevice ? 'Edit device' : 'Add device' }}</h3>
            <form method="post" action="{{ $editingDevice ? route('admin.devices.update', $editingDevice) : route('admin.devices.store') }}" class="form-grid">
                @csrf
                @if ($editingDevice)
                    @method('put')
                @endif

                <div class="field">
                    <label for="device_key">Device Key</label>
                    <input id="device_key" name="device_key" value="{{ old('device_key', $editingDevice?->device_key) }}" required>
                </div>

                <div class="field">
                    <label for="name">Display Name</label>
                    <input id="name" name="name" value="{{ old('name', $editingDevice?->name) }}" placeholder="Main Lobby Gate">
                </div>

                <div class="field">
                    <label for="client_name">Client</label>
                    <input id="client_name" name="client_name" value="{{ old('client_name', $editingDevice?->client_name) }}" placeholder="Client or company name">
                </div>

                <div class="field">
                    <label for="branch_name">Branch / Store</label>
                    <input id="branch_name" name="branch_name" value="{{ old('branch_name', $editingDevice?->branch_name) }}" placeholder="Branch or location name">
                </div>

                <div class="field">
                    <label for="secret">Device Secret</label>
                    <input id="secret" name="secret" type="password" value="" placeholder="{{ $editingDevice ? 'Leave blank to keep current secret' : 'Required' }}">
                </div>

                <div class="field">
                    <label for="display_order">Display Order</label>
                    <input id="display_order" name="display_order" type="number" min="0" value="{{ old('display_order', $editingDevice?->display_order ?? 0) }}">
                </div>

                <div class="field">
                    <label for="person_type_default">Default Person Type</label>
                    <select id="person_type_default" name="person_type_default">
                        @foreach ([1 => 'Employee', 2 => 'Guest', 3 => 'Blacklist'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('person_type_default', $editingDevice?->person_type_default ?? 1) == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="verify_style_default">Default Verify Style</label>
                    <input id="verify_style_default" name="verify_style_default" type="number" min="0" value="{{ old('verify_style_default', $editingDevice?->verify_style_default ?? 1) }}">
                </div>

                <div class="field">
                    <label for="ac_group_number_default">Default Access Group</label>
                    <input id="ac_group_number_default" name="ac_group_number_default" type="number" min="0" value="{{ old('ac_group_number_default', $editingDevice?->ac_group_number_default ?? 0) }}">
                </div>

                <div class="field">
                    <label for="photo_quality_default">Photo Quality Check</label>
                    <select id="photo_quality_default" name="photo_quality_default">
                        <option value="1" @selected(old('photo_quality_default', $editingDevice?->photo_quality_default ?? 1) == 1)>Loose</option>
                        <option value="0" @selected(old('photo_quality_default', $editingDevice?->photo_quality_default ?? 1) == 0)>Strict</option>
                    </select>
                </div>

                <div class="field checkbox">
                    <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $editingDevice?->is_active ?? true))>
                    <label for="is_active">Active for sync and face enrollment</label>
                </div>

                <div class="field full">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" placeholder="Optional device notes or deployment details">{{ old('notes', $editingDevice?->notes) }}</textarea>
                </div>

                <div class="field full">
                    <div class="actions">
                        <button type="submit">{{ $editingDevice ? 'Save Device' : 'Add Device' }}</button>
                        @if ($editingDevice)
                            <a class="btn secondary" href="{{ route('admin.devices.index') }}">New Device</a>
                            <form method="post" action="{{ route('admin.devices.destroy', $editingDevice) }}" onsubmit="return confirm('Remove this device from managed devices?')">
                                @csrf
                                @method('delete')
                                <button type="submit" class="danger">Delete Device</button>
                            </form>
                        @endif
                    </div>
                </div>
            </form>
        </section>

        <section class="card">
            <h3>Managed Device List</h3>
            @if ($devices->isEmpty())
                <p class="subtle">No devices have been added yet.</p>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>State</th>
                                <th>Online</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($devices as $device)
                                <tr>
                                    <td>
                                        <strong>{{ $device->display_name }}</strong>
                                        <div class="subtle">{{ $device->device_key }}</div>
                                        @if ($device->client_name || $device->branch_name)
                                            <div class="subtle">{{ trim(($device->client_name ? $device->client_name.' / ' : '').($device->branch_name ?? '')) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($device->is_managed)
                                            <span class="pill {{ $device->is_active ? 'good' : 'warn' }}">{{ $device->is_active ? 'Active' : 'Inactive' }}</span>
                                        @else
                                            <span class="pill warn">Callback only</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($device->is_online)
                                            <span class="pill good">Online</span>
                                        @elseif ($device->last_seen_at)
                                            <span class="pill warn">Stale</span>
                                        @else
                                            <span class="pill bad">No heartbeat</span>
                                        @endif
                                        <div class="subtle">
                                            Last seen: {{ $device->last_seen_at?->diffForHumans() ?: 'Never' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="inline-actions">
                                            <a class="btn secondary small" href="{{ route('admin.devices.edit', $device) }}">Edit</a>
                                            <form method="post" action="{{ route('admin.devices.status', $device) }}">@csrf<button class="small secondary" type="submit">Status</button></form>
                                            <form method="post" action="{{ route('admin.devices.configure-callbacks', $device) }}">@csrf<button class="small secondary" type="submit">Callbacks</button></form>
                                            <form method="post" action="{{ route('admin.devices.open-door', $device) }}">@csrf<button class="small" type="submit">Open Door</button></form>
                                            <form method="post" action="{{ route('admin.devices.reboot', $device) }}">@csrf<button class="small secondary" type="submit">Reboot</button></form>
                                            <form method="post" action="{{ route('admin.devices.resync-users', $device) }}">@csrf<button class="small secondary" type="submit">Resync Users</button></form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
