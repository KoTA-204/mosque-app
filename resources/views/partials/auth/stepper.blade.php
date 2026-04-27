@php
    $steps = [
        1 => ['icon' => 'user',    'label' => 'Detail pribadi',  'sub' => 'Masukkan email'],
        2 => ['icon' => 'mail',    'label' => 'Cek email',       'sub' => 'Link dikirim ke email anda'],
        3 => ['icon' => 'lock',    'label' => 'Buat password',   'sub' => 'Buat password yang unik'],
        4 => ['icon' => 'check',   'label' => 'Berhasil!',       'sub' => 'Kembali ke halaman Log in'],
    ];
 
    $icons = [
        'user'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
        'mail'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
        'lock'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>',
        'check' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
    ];
@endphp
 
<aside class="hidden lg:flex flex-col w-96 min-h-screen bg-gray-50 dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 px-8 py-8">

    <a href="{{ route('landing') }}" class="flex items-center gap-3 mb-12">
        <img src="{{ asset('images/logo.png') }}"
            alt="Logo"
            class="h-9 w-9 object-contain"
            onerror="this.style.display='none'">
        <span class="text-sm font-bold text-gray-800 dark:text-white leading-tight">Masjid Luqmanul Hakim</span>
    </a>
 
    {{-- Stepper --}}
    <nav class="flex-1" aria-label="Langkah reset password">
        @foreach ($steps as $number => $step)
            @php
                $isDone   = $number < $currentStep;
                $isActive = $number === $currentStep;
            @endphp
 
            <div class="flex items-start gap-4 {{ !$loop->last ? 'mb-0' : '' }}">
 
                {{-- Icon + Connector --}}
                <div class="flex flex-col items-center">
                    <div @class([
                        'w-10 h-10 rounded-full flex items-center justify-center border-2 flex-shrink-0 transition-all duration-200',
                        'border-brand-500 bg-brand-500 text-white'  => $isActive,
                        'border-gray-200 bg-white text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500' => !$isActive && !$isDone,
                        'border-brand-300 bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400' => $isDone,
                    ])>
                        @if ($isDone)
                            {!! $icons['check'] !!}
                        @else
                            {!! $icons[$step['icon']] !!}
                        @endif
                    </div>
 
                    {{-- Connector Line --}}
                    @if (!$loop->last)
                        <div @class([
                            'w-px flex-1 my-1',
                            'min-h-[32px]',
                            'bg-brand-300 dark:bg-brand-700' => $isDone,
                            'bg-gray-200 dark:bg-gray-700'   => !$isDone,
                        ])></div>
                    @endif
                </div>
 
                {{-- Text --}}
                <div class="{{ !$loop->last ? 'pb-8' : '' }} pt-1.5">
                    <span @class([
                        'block text-sm leading-tight',
                        'font-bold text-gray-800 dark:text-white'          => $isActive,
                        'font-semibold text-gray-800 dark:text-white'      => $isDone,
                        'font-medium text-gray-400 dark:text-gray-500'     => !$isActive && !$isDone,
                    ])>
                        {{ $step['label'] }}
                    </span>
                    <span class="block text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                        {{ $step['sub'] }}
                    </span>
                </div>
 
            </div>
        @endforeach
    </nav>
 
    {{-- Footer --}}
    <div class="pt-6 border-t border-gray-200 dark:border-gray-800 flex items-center gap-4">
        <span class="text-xs text-gray-400">&copy; Masjid LH {{ date('Y') }}</span>
        <a href="mailto:help@masjidlh.com"
            class="text-xs text-gray-400 hover:text-brand-500 transition-colors flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            help@masjidlh.com
        </a>
    </div>
 
</aside>