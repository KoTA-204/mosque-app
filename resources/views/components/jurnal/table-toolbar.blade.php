@props([
    'route'        => '',
    'perPage'      => 10,
    'search'       => '',
    'hiddenParams' => [],
])

<div class="flex items-center justify-between gap-3 px-5 py-4
            border-b border-gray-100 dark:border-gray-800 flex-wrap">

    {{-- Kiri: Show N entries --}}
    <form method="GET" action="{{ $route }}" id="perPageForm" class="flex items-center gap-2">
        {{-- Hidden params: semua filter saat ini kecuali per_page --}}
        <input type="hidden" name="search" value="{{ $search }}">
        @foreach($hiddenParams as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach

        <span class="text-sm text-gray-500 dark:text-gray-400">Show</span>
        <select name="per_page"
                onchange="document.getElementById('perPageForm').submit()"
                class="text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1.5
                       bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300
                       outline-none focus:border-green-400">
            @foreach([10, 25, 50] as $val)
                <option value="{{ $val }}" {{ $perPage == $val ? 'selected' : '' }}>
                    {{ $val }}
                </option>
            @endforeach
        </select>
        <span class="text-sm text-gray-500 dark:text-gray-400">entries</span>
    </form>

    {{-- Kanan: Filter + Search --}}
    <form method="GET" action="{{ $route }}" id="filterForm"
          class="flex items-center gap-2 flex-wrap">
        <input type="hidden" name="per_page" value="{{ $perPage }}">

        {{-- Slot filter tambahan (periode, tipe, status, dll.) --}}
        {{ $filters ?? '' }}

        {{-- Search --}}
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Search..."
                   class="pl-9 pr-4 py-1.5 text-sm border border-gray-200 dark:border-gray-700
                          rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300
                          outline-none focus:border-green-400 w-48 placeholder-gray-400">
        </div>
    </form>
</div>