@props(['title'])

{{-- Overlay --}}
<div id="drawerOverlay"
     class="fixed inset-0 z-40 hidden bg-black/30"
     onclick="closeDrawer()"></div>

{{-- Panel --}}
<div id="drawer"
     class="fixed right-0 top-0 z-50 h-full w-full max-w-md translate-x-full transform
            overflow-y-auto bg-white dark:bg-gray-900
            border-l border-gray-200 dark:border-gray-800
            shadow-xl transition-transform duration-300">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 px-5 py-4">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
        <button onclick="closeDrawer()"
                class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200
                       hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Content (diisi via JS) --}}
    <div id="drawerContent" class="p-5">
        {{ $slot ?? '' }}
        {{-- Loading state default (ditampilkan sebelum JS mengisi konten) --}}
        <div class="flex items-center justify-center py-10 text-gray-400 dark:text-gray-600 gap-2">
            <svg class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
            <span class="text-sm">Memuat...</span>
        </div>
    </div>
</div>