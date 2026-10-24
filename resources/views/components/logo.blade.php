@props(['tone' => 'auto', 'compact' => false, 'small' => false])
{{--
    The HM monogram in the dealership's own red and blue (sampled from the logo on
    hadeedmotors.com), set on a white plate so it reads on graphite and on white.
    Swap for the original artwork when the client supplies it.
--}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <svg viewBox="0 0 52 34" class="h-8 w-auto flex-none min-[400px]:h-9" aria-hidden="true">
        <rect x="0.75" y="0.75" width="50.5" height="32.5" rx="5" fill="#fff" stroke="{{ $tone === 'light' ? 'rgb(255 255 255 / .35)' : 'var(--line-strong)' }}" stroke-width="1.5"/>
        <path fill="var(--hm-red)" d="M7 8h5.6v6.7h6.2V8h5.6v18h-5.6v-6.6h-6.2V26H7z"/>
        <path fill="var(--hm-blue)" d="M26.6 8h5.7l3.5 8.4L39.3 8H45v18h-5.3V16.6l-2.6 6.3h-2.6l-2.6-6.3V26h-5.3z"/>
    </svg>
    @unless ($compact)
        <span class="leading-none {{ $tone === 'light' ? 'text-white' : 'text-ink' }}">
            <span class="font-display block whitespace-nowrap font-bold tracking-[-0.02em] {{ $small ? 'text-[0.95rem]' : 'text-[1.05rem] min-[400px]:text-[1.2rem]' }}">Hadeed Motors</span>
            <span class="mt-1 block whitespace-nowrap text-[0.6875rem] font-semibold uppercase tracking-[0.16em] {{ $tone === 'light' ? 'text-white/70' : 'text-ink-3' }}">St John's · Since 1972</span>
        </span>
    @endunless
</span>
