@extends('layouts.guest')
@section('title', 'Check Email')
@section('content')

<div class="flex min-h-screen">

    @include('partials.auth.stepper', ['currentStep' => 2])

    <div class="flex flex-1 items-center justify-center px-6 py-12">
        <div class="w-full max-w-md flex flex-col items-center text-center">

            <div class="relative w-20 h-20 flex items-center justify-center mb-6">
                <div class="absolute inset-0 rounded-full border border-gray-200 dark:border-gray-700 opacity-50"></div>
                <div class="w-14 h-14 rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>

            <h1 class="text-2xl font-extrabold text-gray-800 dark:text-white mb-2">Cek Email Anda</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">
                Kami mengirim instruksi ke<br>
                <span class="font-semibold text-gray-700 dark:text-gray-300">
                    {{ session('reset_email', 'email anda') }}
                </span>
            </p>

            <a href="mailto:" class="w-full">
                <x-ui.button size="md" variant="primary" class="w-full !rounded-3xl justify-center">
                    Buka aplikasi email
                </x-ui.button>
            </a>

            <div class="mt-4 flex items-center justify-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                <span>Tidak menerima email?</span>

                <form action="{{ route('auth.forgot-password.resend') }}" method="POST" class="inline">
                    @csrf
                    <button
                        type="submit"
                        class="font-bold text-brand-500 hover:text-brand-600 transition-colors"
                    >Klik disini</button>
                </form>
            </div>

            @include('partials.auth.step-dots', ['currentStep' => 2])

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    window.addEventListener('storage', function(e) {
        if (e.key === 'reset_link_opened') {
            localStorage.removeItem('reset_link_opened');
            window.location.href = '{{ route("auth.login") }}';
        }
    });
</script>
@endpush