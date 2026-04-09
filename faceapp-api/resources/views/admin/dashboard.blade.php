@extends('admin.layout', ['title' => 'Admin Overview'])

@section('content')
    <section class="header">
        <div>
            <h2>Overview</h2>
            <p>The admin panel now owns device credentials, sync status, and user records. Active devices receive user updates and face enrollments automatically.</p>
        </div>
    </section>

    <section class="stats">
        <article class="stat">
            <span class="stat-label">Managed Devices</span>
            <span class="stat-value">{{ $managedDeviceCount }}</span>
        </article>
        <article class="stat">
            <span class="stat-label">Active Devices</span>
            <span class="stat-value">{{ $activeDeviceCount }}</span>
        </article>
        <article class="stat">
            <span class="stat-label">Online Devices</span>
            <span class="stat-value">{{ $onlineDeviceCount }}</span>
        </article>
        <article class="stat">
            <span class="stat-label">Managed Users</span>
            <span class="stat-value">{{ $managedUserCount }}</span>
        </article>
    </section>

    <div class="grid cols-2">
        <section class="card">
            <div class="split">
                <h3>Devices</h3>
                <a class="btn secondary small" href="{{ route('admin.devices.index') }}">Manage devices</a>
            </div>
            @if ($devices->isEmpty())
                <p class="subtle">No managed devices have been added yet.</p>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>Status</th>
                                <th>People</th>
                                <th>Faces</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($devices as $device)
                                <tr>
                                    <td>
                                        <strong>{{ $device->display_name }}</strong>
                                        <div class="subtle">{{ $device->device_key }}</div>
                                    </td>
                                    <td>
                                        @if ($device->is_online)
                                            <span class="pill good">Online</span>
                                        @elseif ($device->last_seen_at)
                                            <span class="pill warn">Heartbeat stale</span>
                                        @else
                                            <span class="pill bad">No heartbeat</span>
                                        @endif
                                    </td>
                                    <td>{{ $device->person_count ?? '-' }}</td>
                                    <td>{{ $device->face_count ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="card">
            <div class="split">
                <h3>Recent Enrollments</h3>
                <a class="btn secondary small" href="{{ route('admin.users.index') }}">Manage users</a>
            </div>
            @if ($recentEnrollments->isEmpty())
                <p class="subtle">No enrollments have been recorded yet.</p>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Status</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentEnrollments as $enrollment)
                                <tr>
                                    <td>
                                        <strong>{{ $enrollment->name }}</strong>
                                        <div class="subtle">{{ $enrollment->employee_id }}</div>
                                    </td>
                                    <td>
                                        <span class="pill {{ $enrollment->status === 'verified' ? 'good' : ($enrollment->status === 'partial' ? 'warn' : 'bad') }}">{{ ucfirst($enrollment->status) }}</span>
                                    </td>
                                    <td>{{ $enrollment->updated_at?->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <section class="card" style="margin-top: 20px;">
        <h3>Recent Device Events</h3>
        @if ($recentEvents->isEmpty())
            <p class="subtle">No heartbeat or access record events have been captured yet.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Device</th>
                            <th>Person</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentEvents as $event)
                            <tr>
                                <td>{{ str_replace('_', ' ', $event->event_type) }}</td>
                                <td>{{ $event->device_key }}</td>
                                <td>{{ $event->person_sn ?: '-' }}</td>
                                <td>{{ $event->event_time?->diffForHumans() ?? $event->created_at?->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
