@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center mb-2">
    <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-1">{{ $title }}</h2>
    <p class="text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
</div>
