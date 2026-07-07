@extends('layouts.guest')
@section('title', 'Reset Password')
@section('content')

<div class="flex min-h-screen">

    @include('partials.auth.stepper', ['currentStep' => 3])

    <div class="flex flex-1 items-center justify-center px-6 py-12">
        <div class="w-full max-w-md flex flex-col items-center text-center">

            <div class="relative w-20 h-20 flex items-center justify-center mb-6">
                <div class="absolute inset-0 rounded-full border border-gray-200 dark:border-gray-700 opacity-50"></div>
                <div class="w-14 h-14 rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>

            <h1 class="text-2xl font-extrabold text-gray-800 dark:text-white mb-2">Buat Password Baru</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">Password baru anda harus berbeda dengan yang sebelumnya.</p>

            @if (session('error'))
                <x-ui.alert variant="error" title="Error" :message="session('error')" class="mb-5 text-left w-full" />
            @endif
    
            <form
                action="{{ route('password.update') }}"
                method="POST"
                class="w-full text-left"
                novalidate
                x-data="{
                    password: '',
                    confirm: '',
                    get hasLength()  { return this.password.length >= 8 },
                    get hasSpecial() { return /[^A-Za-z0-9]/.test(this.password) },
                }"
            >
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                {{-- Password --}}
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <input
                        type="password"
                        name="password"
                        x-model="password"
                        required
                        class="w-full rounded-3xl border px-4 py-2 text-sm focus:ring-2 dark:bg-gray-800 dark:text-white
                            @error('password') border-red-400 focus:border-red-500 focus:ring-red-200
                            @else border-gray-300 focus:border-brand-500 focus:ring-brand-200 dark:border-gray-600 @enderror"
                    >
                </div>

                <div class="mb-5">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password                    </label>
                    <input
                        type="password"
                        name="password_confirmation"
                        x-model="confirm"
                        required
                        class="w-full rounded-3xl border px-4 py-2 text-sm focus:ring-2 dark:bg-gray-800 dark:text-white
                            @error('password_confirmation') border-red-400 focus:border-red-500 focus:ring-red-200
                            @else border-gray-300 focus:border-brand-500 focus:ring-brand-200 dark:border-gray-600 @enderror"
                    >
                </div>

                <div class="flex flex-col gap-2 mb-6">
                    <div class="flex items-center gap-2" :class="hasLength ? 'text-brand-600 dark:text-brand-400' : 'text-gray-400'">
                        <div class="w-5 h-5 rounded-full border flex items-center justify-center flex-shrink-0 transition-colors"
                            :class="hasLength ? 'border-brand-500 bg-brand-50 dark:bg-brand-950' : 'border-gray-300 dark:border-gray-600'">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-xs font-medium">Minimal 8 karakter</span>
                    </div>
                    <div class="flex items-center gap-2" :class="hasSpecial ? 'text-brand-600 dark:text-brand-400' : 'text-gray-400'">
                        <div class="w-5 h-5 rounded-full border flex items-center justify-center flex-shrink-0 transition-colors"
                            :class="hasSpecial ? 'border-brand-500 bg-brand-50 dark:bg-brand-950' : 'border-gray-300 dark:border-gray-600'">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-xs font-medium">Mengandung minimal 1 spesial karakter</span>
                    </div>
                </div>

                <button type="submit" :disabled="!hasLength || !hasSpecial" class="w-full inline-flex justify-center rounded-3xl bg-brand-500 px-4 py-2 text-white font-semibold hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">Ubah Password</button>

            </form>

            @include('partials.auth.step-dots', ['currentStep' => 3])

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    localStorage.setItem('reset_link_opened', Date.now());
</script>
@endpush