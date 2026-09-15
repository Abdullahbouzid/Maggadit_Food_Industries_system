@extends('dashboard.partials.layout')

@section('title', __('Profile'))

@section('content')
<div class="py-5">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-5">

        {{-- Profile Information --}}
        <div class="nova-card">
            <div class="nova-card-header">
                <h2 class="nova-card-title">
                    <i class="fas fa-id-card" style="color: var(--nova-primary);"></i>
                    {{ __('Profile Information') }}
                </h2>
            </div>
            <div class="nova-card-body">
                <p class="nova-card-text" style="margin: 0 0 1.25rem;">{{ __("Update your account's profile information and email address.") }}</p>

                {{-- Email Verification --}}
                <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                </form>

                <form method="post" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')

                    <div class="nova-form-group">
                        <label class="nova-label">{{ __('Name') }}</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="nova-input">
                        @error('name')
                            <p class="nova-text-danger nova-fs-sm" style="margin-top: 0.25rem;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">{{ __('Email') }}</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="nova-input">
                        @error('email')
                            <p class="nova-text-danger nova-fs-sm" style="margin-top: 0.25rem;">{{ $message }}</p>
                        @enderror

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="nova-alert warning" style="margin-top: 0.75rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <p class="nova-fw-semibold">{{ __('Your email address is unverified.') }}</p>
                                    <p class="nova-fs-sm" style="opacity: 0.8;">{{ __("Click here to re-send the verification email.") }}</p>
                                </div>
                                <button form="send-verification" class="nova-btn nova-btn-sm nova-btn-primary" style="margin-inline-start: auto;">
                                    {{ __('Resend') }}
                                </button>
                            </div>

                            @if (session('status') === 'verification-link-sent')
                                <div class="nova-alert success" style="margin-top: 0.75rem;">
                                    <i class="fas fa-check-circle"></i>
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="nova-flex nova-gap-2" style="margin-top: 1rem;">
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save"></i> {{ __('Save') }}
                        </button>

                        @if (session('status') === 'profile-updated')
                            <p class="nova-alert success nova-flex nova-items-center" style="margin: 0;">
                                <i class="fas fa-check"></i> {{ __('Saved.') }}
                            </p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Password --}}
        <div class="nova-card">
            <div class="nova-card-header">
                <h2 class="nova-card-title">
                    <i class="fas fa-lock" style="color: var(--nova-primary);"></i>
                    {{ __('Update Password') }}
                </h2>
            </div>
            <div class="nova-card-body">
                <p class="nova-card-text" style="margin: 0 0 1.25rem;">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>

                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="nova-form-group">
                        <label class="nova-label">{{ __('Current Password') }}</label>
                        <input type="password" name="current_password" autocomplete="current-password" class="nova-input">
                        @error('current_password')
                            <p class="nova-text-danger nova-fs-sm" style="margin-top: 0.25rem;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">{{ __('New Password') }}</label>
                        <input type="password" name="password" autocomplete="new-password" class="nova-input">
                        @error('password')
                            <p class="nova-text-danger nova-fs-sm" style="margin-top: 0.25rem;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="nova-form-group">
                        <label class="nova-label">{{ __('Confirm Password') }}</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password" class="nova-input">
                    </div>

                    <div class="nova-flex nova-gap-2" style="margin-top: 1rem;">
                        <button type="submit" class="nova-btn nova-btn-primary">
                            <i class="fas fa-save"></i> {{ __('Save') }}
                        </button>

                        @if (session('status') === 'password-updated')
                            <p class="nova-alert success nova-flex nova-items-center" style="margin: 0;">
                                <i class="fas fa-check"></i> {{ __('Saved.') }}
                            </p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete Account --}}
        <div class="nova-card">
            <div class="nova-card-header">
                <h2 class="nova-card-title" style="color: var(--nova-danger);">
                    <i class="fas fa-trash-alt"></i>
                    {{ __('Delete Account') }}
                </h2>
            </div>
            <div class="nova-card-body">
                <p class="nova-card-text" style="margin: 0 0 1rem;">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}</p>

                <form id="deleteUserForm" method="post" action="{{ route('profile.destroy') }}" class="nova-flex nova-gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="nova-btn nova-btn-danger">
                        <i class="fas fa-trash-alt"></i> {{ __('Delete Account') }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
