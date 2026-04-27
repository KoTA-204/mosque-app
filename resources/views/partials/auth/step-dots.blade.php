<div class="flex items-center justify-center gap-2 mt-8" aria-hidden="true">
    @for ($i = 1; $i <= 4; $i++)
        <span @class([
            'block w-2 h-2 rounded-full transition-all duration-200',
            'bg-accent-400 w-4'     => $i === $currentStep,
            'bg-gray-200 dark:bg-gray-700' => $i !== $currentStep,
        ])></span>
    @endfor
</div>