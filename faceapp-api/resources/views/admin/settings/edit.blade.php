@extends('admin.layout', ['title' => 'System Settings'])

@section('content')
    <section class="header">
        <div>
            <h2>System Settings</h2>
            <p>These values replace the old single-device `.env` behavior. Device credentials live on each managed device, while these URLs and retry windows apply across the whole stack.</p>
        </div>
    </section>

    <section class="card">
        <form method="post" action="{{ route('admin.settings.update') }}" class="form-grid">
            @csrf
            @method('put')

            <div class="field full">
                <label for="gateway_base_url">Gateway Base URL</label>
                <input id="gateway_base_url" name="gateway_base_url" type="url" value="{{ old('gateway_base_url', $settings->gateway_base_url) }}" placeholder="http://gateway:8190/api">
            </div>

            <div class="field">
                <label for="public_storage_base_url">Public Storage Base URL</label>
                <input id="public_storage_base_url" name="public_storage_base_url" type="url" value="{{ old('public_storage_base_url', $settings->public_storage_base_url) }}" placeholder="https://face-api.example.com/storage">
            </div>

            <div class="field">
                <label for="gateway_image_base_url">Gateway Image Base URL</label>
                <input id="gateway_image_base_url" name="gateway_image_base_url" type="url" value="{{ old('gateway_image_base_url', $settings->gateway_image_base_url) }}" placeholder="http://face-api.example.com/storage">
            </div>

            <div class="field">
                <label for="gateway_callback_base_url">Gateway Callback Base URL</label>
                <input id="gateway_callback_base_url" name="gateway_callback_base_url" type="url" value="{{ old('gateway_callback_base_url', $settings->gateway_callback_base_url) }}" placeholder="http://face-api.example.com">
            </div>

            <div class="field">
                <label for="heartbeat_interval_seconds">Heartbeat Interval Seconds</label>
                <input id="heartbeat_interval_seconds" name="heartbeat_interval_seconds" type="number" min="10" max="300" value="{{ old('heartbeat_interval_seconds', $settings->heartbeat_interval_seconds ?: 60) }}">
            </div>

            <div class="field">
                <label for="online_window_seconds">Online Window Seconds</label>
                <input id="online_window_seconds" name="online_window_seconds" type="number" min="30" max="900" value="{{ old('online_window_seconds', $settings->online_window_seconds ?: 180) }}">
            </div>

            <div class="field">
                <label for="person_verify_retries">Person Verify Retries</label>
                <input id="person_verify_retries" name="person_verify_retries" type="number" min="1" max="20" value="{{ old('person_verify_retries', $settings->person_verify_retries ?: 5) }}">
            </div>

            <div class="field">
                <label for="person_verify_delay_ms">Person Verify Delay ms</label>
                <input id="person_verify_delay_ms" name="person_verify_delay_ms" type="number" min="0" max="10000" value="{{ old('person_verify_delay_ms', $settings->person_verify_delay_ms ?: 1000) }}">
            </div>

            <div class="field">
                <label for="face_verify_retries">Face Verify Retries</label>
                <input id="face_verify_retries" name="face_verify_retries" type="number" min="1" max="20" value="{{ old('face_verify_retries', $settings->face_verify_retries ?: 5) }}">
            </div>

            <div class="field">
                <label for="face_verify_delay_ms">Face Verify Delay ms</label>
                <input id="face_verify_delay_ms" name="face_verify_delay_ms" type="number" min="0" max="10000" value="{{ old('face_verify_delay_ms', $settings->face_verify_delay_ms ?: 1500) }}">
            </div>

            <div class="field full">
                <div class="actions">
                    <button type="submit">Save Settings</button>
                </div>
            </div>
        </form>
    </section>
@endsection
