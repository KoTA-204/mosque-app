@extends('layouts.guest')
@section('title', 'Forgot Password')
@section('content')

<div class="flex min-h-screen">

    @include('partials.auth.stepper', ['currentStep' => 1])

    <div class="flex flex-1 items-center justify-center px-6 py-12">
        <div class="w-full max-w-md flex flex-col items-center text-center">

            <div class="relative w-20 h-20 flex items-center justify-center mb-6">
                <div class="absolute inset-0 rounded-full border border-gray-200 dark:border-gray-700 opacity-50"></div>
                <div class="w-14 h-14 rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
            </div>

            <h1 class="text-2xl font-extrabold text-gray-800 dark:text-white mb-2">Lupa Password?</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">Instruksi akan dikirimkan ke email anda.</p>

            @if (session('error'))
                <x-ui.alert variant="error" title="Error" :message="session('error')" class="mb-5 text-left w-full" />
            @endif

            <form action="{{ route('auth.forgot-password.post') }}" method="POST" class="w-full text-left" novalidate>
                @csrf
                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="email-anda@gmail.com"
                        required
                        class="w-full rounded-3xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <x-ui.button type="submit" size="md" variant="primary" class="w-full !rounded-3xl justify-center">Reset password</x-ui.button>
            </form>

            <a href="{{ route('auth.login') }}"
                class="w-full flex justify-end items-center gap-2 mt-4 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-brand-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Log in
            </a>

            @include('partials.auth.step-dots', ['currentStep' => 1])

        </div>
    </div>
</div>

@endsection