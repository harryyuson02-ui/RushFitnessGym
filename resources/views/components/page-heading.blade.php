@props(['eyebrow', 'title', 'description' => null])

<div class="max-w-3xl">
    <p class="text-[0.7rem] font-bold uppercase tracking-[0.16em] text-lime-700">{{ $eyebrow }}</p>
    <h1 class="mt-1.5 text-3xl font-bold tracking-tight text-zinc-950 sm:text-4xl">{{ $title }}</h1>
    @if ($description)<p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500 sm:text-base">{{ $description }}</p>@endif
</div>
