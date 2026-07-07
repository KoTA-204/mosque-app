@extends('layouts.guest')
@section('title', 'Success')
@section('content')

<div class="flex min-h-screen">

    @include('partials.auth.stepper', ['currentStep' => 4])

    <div class="flex flex-1 items-center justify-center px-6 py-12">
        <div class="w-full max-w-md flex flex-col items-center text-center">

            <div class="relative w-20 h-20 flex items-center justify-center mb-6">
                <div class="absolute inset-0 rounded-full border border-brand-200 dark:border-brand-800 opacity-50"></div>
                <div class="w-14 h-14 rounded-full border-2 border-brand-500 bg-brand-50 dark:bg-brand-950 flex items-center justify-center shadow-sm">
                    <svg class="w-7 h-7 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>

            <h1 class="text-2xl font-extrabold text-gray-800 dark:text-white mb-2">Berhasil!</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-8 leading-relaxed">
                Password anda berhasil diubah.<br>
                Kembali ke halaman Log in untuk masuk.
            </p>

            <a href="{{ route('auth.login') }}" class="w-full">
                <x-ui.button size="md" variant="primary" class="w-full !rounded-3xl justify-center">Login Sekarang</x-ui.button>
            </a>

            @include('partials.auth.step-dots', ['currentStep' => 4])

        </div>
    </div>
</div>

@endsection