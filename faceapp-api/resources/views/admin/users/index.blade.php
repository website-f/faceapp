@extends('admin.layout', ['title' => 'Manage Users'])

@section('content')
    <section class="header">
        <div>
            <h2>Users</h2>
            <p>Manage users centrally, filter by device tab, and keep sync status visible per device. When a face is enrolled from the frontend, it will be pushed to every active device listed here.</p>
        </div>
        <div class="actions">
            <a class="btn" href="{{ route('admin.users.create') }}">Add User</a>
        </div>
    </section>

    <div class="tabs">
        <a href="{{ route('admin.users.index', array_filter(['q' => $search])) }}" class="{{ $selectedDeviceId ? '' : 'active' }}">All Devices</a>
        @foreach ($devices as $device)
            <a href="{{ route('admin.users.index', array_filter(['device_id' => $device->id, 'q' => $search])) }}" class="{{ $selectedDeviceId === $device->id ? 'active' : '' }}">{{ $device->display_name }}</a>
        @endforeach
    </div>

    <section class="card" style="margin-bottom: 18px;">
        <form method="get" action="{{ route('admin.users.index') }}" class="form-grid">
            @if ($selectedDeviceId)
                <input type="hidden" name="device_id" value="{{ $selectedDeviceId }}">
            @endif
            <div class="field">
                <label for="q">Search users</label>
                <input id="q" name="q" value="{{ $search }}" placeholder="Name, employee ID, department, role">
            </div>
            <div class="field checkbox">
                <button type="submit">Filter</button>
            </div>
        </form>
    </section>

    <section class="card">
        <h3>User Directory</h3>
        @if ($users->isEmpty())
            <p class="subtle">No users match the current filter.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Profile</th>
                            <th>Sync Status</th>
                            <th>Latest Enrollment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            @php
                                $latestEnrollment = $latestEnrollments->get($user->id)?->first();
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    <div class="subtle">{{ $user->employee_id }}</div>
                                </td>
                                <td>
                                    <div>{{ $user->role ?: 'No role set' }}</div>
                                    <div class="subtle">{{ $user->department ?: 'No department' }}</div>
                                    <div class="subtle">Access: {{ $user->access_level ?: 'Not set' }}</div>
                                </td>
                                <td>
                                    <div class="inline-actions">
                                        @foreach ($user->syncs->sortBy(fn ($sync) => $sync->device?->display_order ?? PHP_INT_MAX) as $sync)
                                            @if (! $selectedDeviceId || $selectedDeviceId === $sync->device_id)
                                                <span class="pill {{ $sync->face_status === 'verified' ? 'good' : ($sync->sync_status === 'synced' ? 'info' : ($sync->sync_status === 'failed' ? 'bad' : 'warn')) }}">
                                                    {{ $sync->device?->display_name ?? 'Unknown device' }}: {{ $sync->face_status ?: $sync->sync_status }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                    @if ($user->syncs->isEmpty())
                                        <div class="subtle">No device sync yet.</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($latestEnrollment)
                                        <span class="pill {{ $latestEnrollment->status === 'verified' ? 'good' : ($latestEnrollment->status === 'partial' ? 'warn' : 'bad') }}">{{ ucfirst($latestEnrollment->status) }}</span>
                                        <div class="subtle">{{ $latestEnrollment->updated_at?->diffForHumans() }}</div>
                                    @else
                                        <div class="subtle">No enrollment run yet.</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="inline-actions">
                                        <a class="btn secondary small" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                                        <form method="post" action="{{ route('admin.users.resync', $user) }}">@csrf<button class="small secondary" type="submit">Resync</button></form>
                                        <form method="post" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user from local records and active devices?')">
                                            @csrf
                                            @method('delete')
                                            <button class="small danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
