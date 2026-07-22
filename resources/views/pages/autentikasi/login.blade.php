@extends('layouts.guest')
@section('title', 'Login')
@section('content')

<div class="relative flex w-screen min-h-screen">
        {{-- Tombol Back ke Landing Page --}}
        <a href="{{ route('landing') }}"
            class="absolute top-6 left-6 z-10 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/80 backdrop-blur-sm border border-gray-200 text-sm font-sans font-medium text-gray-600 hover:text-gray-800 hover:bg-white shadow-sm transition dark:bg-gray-800/80 dark:border-gray-700 dark:text-gray-300 dark:hover:text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>

        <div class="flex flex-col flex-1 items-center justify-center px-6 py-12 lg:px-16">
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 mb-8">
                <img src="{{ asset('images/logo.png') }}"
                    alt="Masjid Luqmanul Hakim"
                    class="h-24 w-auto"
                    onerror="this.style.display='none'">
            </a>

        <div class="w-full max-w-sm">

            <h1 class="w-full max-w-md flex flex-col items-center text-center mb-2 text-3xl font-sans font-bold text-gray-800 dark:text-white">Log in</h1>

            @if(session('status') === 'session-expired' || request()->has('expired'))
            <div class="mb-5 flex items-center gap-3 bg-gray-100 border border-gray-300 rounded-xl px-4 py-3" id="alert-session">
                <svg class="w-4 h-4 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-red-500 flex-1">Sesi Anda telah berakhir. Silakan login kembali.</p>
                <button onclick="document.getElementById('alert-session').remove()"
                    class="text-gray-400 hover:text-gray-600 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            @endif

            @if(session('auth_redirect'))
            <div class="mb-5 flex items-center gap-3 bg-gray-100 border border-gray-300 rounded-xl px-4 py-3" id="alert-intended">
                <svg class="w-4 h-4 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-red-500 flex-1">Anda harus login terlebih dahulu untuk mengakses halaman tersebut.</p>
                <button onclick="document.getElementById('alert-intended').remove()"
                    class="text-gray-400 hover:text-gray-600 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            @endif

            {{-- Alert Error --}}
            @if (session('error'))
                <x-ui.alert
                    variant="error"
                    title="Login Gagal"
                    :message="session('error')"
                    class="mb-5"
                />
            @endif

            {{-- Form --}}
            <form action="{{ route('auth.login.post') }}" method="POST" novalidate>
            @csrf

            {{-- Email --}}
            <div class="mb-5">
                <label class="mb-2 block text-sm font-sans font-medium text-gray-700 dark:text-gray-300">
                    Email
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="email-anda@gmail.com"
                    required
                    class="w-full rounded-3xl border px-4 py-2 text-sm focus:ring-2 dark:bg-gray-800 dark:text-white
                        @error('email')
                            border-red-400 focus:border-red-500 focus:ring-red-200
                        @else
                            border-gray-300 focus:border-brand-500 focus:ring-brand-200 dark:border-gray-600
                        @enderror"
                >
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-5">
                <label class="mb-2 block text-sm font-sans font-medium text-gray-700 dark:text-gray-300">
                    Password
                </label>
                <input
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                    class="w-full rounded-3xl border border-gray-300 px-4 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                >
                @error('password')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember --}}
            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        name="remember"
                        {{ old('remember') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Ingatkan saya</span>
                </label>

                <a href="{{ route('auth.forgot-password') }}"
                    class="text-sm font-sans font-medium text-accent-400 hover:text-accent-500">
                    Lupa Password?
                </a>
            </div>

            <button
                type="submit"
                class="w-full rounded-3xl bg-brand-400 py-2 text-white font-sans font-medium hover:bg-gray-400 transition">
                Sign in
            </button>
        </form>

        </div>

        {{-- Footer --}}
        <p class="absolute bottom-6 left-6 text-xs text-gray-400">
            &copy; Masjid LH {{ date('Y') }}
        </p>

    </div>

    <div class="hidden lg:flex lg:w-[55%] items-center justify-center p-6 bg-gray-50 dark:bg-gray-900">
        <div class="w-full max-w-2xl rounded-2xl overflow-hidden shadow-2xl">

            {{-- Foto Masjid --}}
            <div class="h-[380px] overflow-hidden">
                <img src="{{ asset('images/hero/banner-1.jpg') }}"
                    alt="Masjid Luqmanul Hakim"
                    class="w-full h-full object-cover object-center"
                    onerror="this.src='https://placehold.co/800x380/2D8C3E/ffffff?text=Masjid+Luqmanul+Hakim'">
            </div>

            {{-- Testimoni --}}
            <div class="bg-gray-400 px-8 py-6">
                <p class="text-sm italic text-white/90 mb-4 leading-relaxed">
                    "Masjid ini adalah pusat kegiatan dan syiar Islam yang luar biasa bagi kami semua."
                </p>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-sans font-bold text-white">Ahmad Fauzi</p>
                        <p class="text-xs text-white/60">Ketua DKM</p>
                    </div>
                    <div class="text-accent-400 text-lg tracking-widest">
                        &#9733;&#9733;&#9733;&#9733;&#9733;
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

@push('scripts')
<script>
    document.querySelector('form').addEventListener('submit', function () {
        const btn = this.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Masuk...';
        }
    });
</script>
@endpush

@endsection