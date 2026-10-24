@props(['tone' => 'auto', 'compact' => false, 'small' => false])
{{--
    Hadeed Motors' own HM mark, the file the dealership uses on hadeedmotors.com
    (public/img/brand/hadeed-hm.png). It is red and blue on transparent, so it
    sits on a white plate to stay legible on the graphite header, footer and in
    dark mode.
--}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <span class="flex h-8 flex-none items-center rounded-[5px] bg-white px-[7px] ring-1 ring-inset min-[400px]:h-9 {{ $tone === 'light' ? 'ring-white/35' : 'ring-line-strong' }}">
        <img src="{{ asset('img/brand/hadeed-hm.png') }}" width="123" height="58" alt="" class="h-[19px] w-auto min-[400px]:h-[22px]">
    </span>
    @unless ($compact)
        <span class="leading-none {{ $tone === 'light' ? 'text-white' : 'text-ink' }}">
            <span class="font-display block whitespace-nowrap font-bold tracking-[-0.02em] {{ $small ? 'text-[0.95rem]' : 'text-[1.05rem] min-[400px]:text-[1.2rem]' }}">Hadeed Motors</span>
            <span class="mt-1 block whitespace-nowrap text-[0.6875rem] font-semibold uppercase tracking-[0.16em] {{ $tone === 'light' ? 'text-white/70' : 'text-ink-3' }}">St John's · Since 1972</span>
        </span>
    @endunless
</span>
