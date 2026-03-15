<p class="text-muted mb-3">Ensure your account is using a long, random password to stay secure.</p>

@if (session('status') === 'password-updated')
    <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
        <i class="bi bi-check-circle-fill me-1"></i> Password updated successfully.
        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form method="POST" action="{{ route('password.update') }}" data-submit-spinner>
    @csrf
    @method('put')

    <div class="mb-3">
        <label for="update_password_current_password" class="form-label fw-semibold">Current Password</label>
        <input type="password" id="update_password_current_password" name="current_password"
               class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif"
               autocomplete="current-password">
        @if($errors->updatePassword->has('current_password'))
            <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
        @endif
    </div>

    <div class="mb-3">
        <label for="update_password_password" class="form-label fw-semibold">New Password</label>
        <input type="password" id="update_password_password" name="password"
               class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif"
               autocomplete="new-password">
        @if($errors->updatePassword->has('password'))
            <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
        @endif
    </div>

    <div class="mb-3">
        <label for="update_password_password_confirmation" class="form-label fw-semibold">Confirm Password</label>
        <input type="password" id="update_password_password_confirmation" name="password_confirmation"
               class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif"
               autocomplete="new-password">
        @if($errors->updatePassword->has('password_confirmation'))
            <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
        @endif
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check-lg me-1"></i> Update Password
    </button>
</form>
