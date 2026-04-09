@extends('admin.layout', ['title' => $isEditing ? 'Edit User' : 'Add User'])

@section('content')
    <section class="header">
        <div>
            <h2>{{ $isEditing ? 'Edit User' : 'Add User' }}</h2>
            <p>These user records are the source of truth for both the admin area and the face app frontend. Saving a user will sync the person record to all active devices.</p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('admin.users.index') }}">Back to users</a>
            @if ($isEditing)
                <form method="post" action="{{ route('admin.users.resync', $managedUser) }}">
                    @csrf
                    <button class="secondary" type="submit">Resync Now</button>
                </form>
            @endif
        </div>
    </section>

    <section class="card">
        <form method="post" action="{{ $isEditing ? route('admin.users.update', $managedUser) : route('admin.users.store') }}" class="form-grid">
            @csrf
            @if ($isEditing)
                @method('put')
            @endif

            <div class="field">
                <label for="employee_id">Employee ID</label>
                <input id="employee_id" name="employee_id" value="{{ old('employee_id', $managedUser->employee_id) }}" required>
            </div>

            <div class="field">
                <label for="name">Name</label>
                <input id="name" name="name" value="{{ old('name', $managedUser->name) }}" required>
            </div>

            <div class="field">
                <label for="role">Role</label>
                <input id="role" name="role" value="{{ old('role', $managedUser->role) }}">
            </div>

            <div class="field">
                <label for="department">Department</label>
                <input id="department" name="department" value="{{ old('department', $managedUser->department) }}">
            </div>

            <div class="field">
                <label for="access_level">Access Level</label>
                <input id="access_level" name="access_level" value="{{ old('access_level', $managedUser->access_level) }}">
            </div>

            <div class="field">
                <label for="joined_on">Joined On</label>
                <input id="joined_on" name="joined_on" type="date" value="{{ old('joined_on', optional($managedUser->joined_on)->format('Y-m-d')) }}">
            </div>

            <div class="field">
                <label for="mobile">Mobile</label>
                <input id="mobile" name="mobile" value="{{ old('mobile', $managedUser->mobile) }}">
            </div>

            <div class="field">
                <label for="card_no">Card Number</label>
                <input id="card_no" name="card_no" value="{{ old('card_no', $managedUser->card_no) }}">
            </div>

            <div class="field">
                <label for="id_card">ID Card</label>
                <input id="id_card" name="id_card" value="{{ old('id_card', $managedUser->id_card) }}">
            </div>

            <div class="field">
                <label for="voucher_code">Voucher Code</label>
                <input id="voucher_code" name="voucher_code" value="{{ old('voucher_code', $managedUser->voucher_code) }}">
            </div>

            <div class="field">
                <label for="verify_pwd">Verify Password</label>
                <input id="verify_pwd" name="verify_pwd" value="{{ old('verify_pwd', $managedUser->verify_pwd) }}">
            </div>

            <div class="field">
                <label for="person_type">Person Type</label>
                <select id="person_type" name="person_type">
                    @foreach ([1 => 'Employee', 2 => 'Guest', 3 => 'Blacklist'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('person_type', $managedUser->person_type ?? 1) == $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="verify_style">Verify Style</label>
                <input id="verify_style" name="verify_style" type="number" min="0" value="{{ old('verify_style', $managedUser->verify_style ?? 1) }}">
            </div>

            <div class="field">
                <label for="ac_group_number">Access Group</label>
                <input id="ac_group_number" name="ac_group_number" type="number" min="0" value="{{ old('ac_group_number', $managedUser->ac_group_number ?? 0) }}">
            </div>

            <div class="field checkbox">
                <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $managedUser->is_active ?? true))>
                <label for="is_active">Active and eligible for sync</label>
            </div>

            <div class="field full">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes">{{ old('notes', $managedUser->notes) }}</textarea>
            </div>

            @if ($isEditing && $managedUser->photo_public_url)
                <div class="field full">
                    <label>Current Face Photo</label>
                    <div class="subtle" style="margin-bottom: 8px;">Latest photo captured through the face app.</div>
                    <img src="{{ $managedUser->photo_public_url }}" alt="Face photo" style="max-width: 180px; border-radius: 18px; border: 1px solid var(--line);">
                </div>
            @endif

            <div class="field full">
                <div class="actions">
                    <button type="submit">{{ $isEditing ? 'Save User' : 'Create User' }}</button>
                </div>
            </div>
        </form>
    </section>
@endsection
