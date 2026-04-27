@extends('layouts.guest')
@section('title', 'Login')
@section('content')

<div class="relative flex w-screen min-h-screen">
    <div class="flex flex-col flex-1 items-center justify-center px-6 py-12 lg:px-16">
        <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 mb-8">
            <img src="{{ asset('images/logo.png') }}"
                alt="Masjid Luqmanul Hakim"
                class="h-14 w-auto"
                onerror="this.style.display='none'">
        </a>

        <div class="w-full max-w-sm">

            <h1 class="w-full max-w-md flex flex-col items-center text-center mb-2 text-3xl font-sans font-bold text-gray-800 dark:text-white">Log in</h1>
            <p class="w-full max-w-md flex flex-col items-center text-center mb-8 text-sm text-gray-500 dark:text-gray-400">Gunakan kredensial yang diberikan oleh Admin.</p>

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
                </label>
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
                class="w-full rounded-3xl bg-yellow-400 py-2 text-white font-sans font-medium hover:bg-gray-400 transition">
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
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('loginBtn');
        btn.disabled = true;
        btn.textContent = 'Masuk...';
    });
</script>
@endpush

@endsection