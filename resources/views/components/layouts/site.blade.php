@props(['title' => null, 'description' => null, 'jsonLd' => [], 'mobileBar' => true, 'ogImage' => 'showroom', 'testDriveUrl' => null])
@php
    $pageTitle = $title ? $title.' · '.hadeed('short_name') : hadeed('short_name').' · Nissan and Suzuki in Antigua since 1972';
    $metaDescription = $description ?? 'Hadeed Motors, Old Parham Road, St John\'s: Antigua and Barbuda\'s Nissan and Suzuki dealer since 1972. New and pre-owned cars, test drives, finance with FDC, service and genuine parts.';
    $phone = hadeed('phone');
    $email = hadeed('email');
    $address = hadeed('address');
    $nav = [
        ['route' => 'lineup.index', 'match' => 'lineup.*', 'label' => 'New vehicles'],
        ['route' => 'inventory.index', 'match' => 'inventory.*', 'label' => 'Pre-owned'],
        ['route' => 'finance', 'match' => 'finance*', 'label' => 'Finance'],
        ['route' => 'service', 'match' => ['service', 'parts'], 'label' => 'Service & parts'],
        ['route' => 'about', 'match' => ['about', 'group'], 'label' => 'About'],
        ['route' => 'contact', 'match' => 'contact', 'label' => 'Contact'],
    ];
    $organization = [
        '@context' => 'https://schema.org',
        '@type' => 'AutoDealer',
        'name' => hadeed('name'),
        'url' => route('home'),
        'telephone' => '+'.us_digits($phone),
        'email' => $email,
        'foundingDate' => (string) hadeed('founded'),
        'logo' => asset('img/brand/hadeed-hm.png'),
        'image' => asset('img/showroom-1920.webp'),
        'brand' => [['@type' => 'Brand', 'name' => 'Nissan'], ['@type' => 'Brand', 'name' => 'Suzuki']],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $address['building'].', '.$address['street'],
            'addressLocality' => $address['city'],
            'addressCountry' => $address['country_code'],
        ],
        'openingHoursSpecification' => \App\Support\Hours::schema(),
        'areaServed' => 'Antigua and Barbuda',
    ];
    $tdUrl = $testDriveUrl ?? route('test-drive');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    @if (hadeed('preview'))
        <meta name="robots" content="noindex, nofollow">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ hadeed('name') }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ str_starts_with($ogImage, 'http') ? $ogImage : asset('img/'.$ogImage.'-1920.webp') }}">
    <meta property="og:locale" content="en_AG">
    <meta name="twitter:card" content="summary_large_image">
    @foreach (array_merge([$organization], $jsonLd) as $ld)
        <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endforeach
</head>
<body class="min-h-screen {{ $mobileBar ? 'pb-[5.25rem] lg:pb-0' : '' }}" x-data="{ menu: false }" @keydown.escape.window="menu = false">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-surface focus:px-4 focus:py-3 focus:shadow-md">Skip to content</a>

    @if (hadeed('preview'))
        <div class="bg-accent text-sm text-accent-ink">
            <p class="wrap flex items-start gap-2 py-2.5"><x-icon name="alert" class="mt-0.5 h-4 w-4 flex-none" /><span><strong class="font-bold">Concept preview prepared for Hadeed Motors Ltd.</strong> This is not the official Hadeed Motors website. Requests sent here do not reach the dealership.</span></p>
        </div>
    @endif

    <header class="sticky top-0 z-40 bg-graphite-900 text-white">
        <div class="wrap flex h-[4.5rem] items-center gap-2 min-[400px]:gap-3">
            <a href="{{ route('home') }}" class="mr-auto inline-flex min-h-11 flex-none items-center rounded" aria-label="Hadeed Motors, home"><x-logo tone="light" /></a>

            <nav class="hidden items-center lg:flex" aria-label="Main">
                @foreach ($nav as $item)
                    @php($current = request()->routeIs($item['match']))
                    <a href="{{ route($item['route']) }}" @class(['relative whitespace-nowrap px-2.5 py-2.5 text-[0.9375rem] font-semibold transition-colors xl:px-3.5', 'text-white' => $current, 'text-white/75 hover:text-white' => ! $current]) @if($current) aria-current="page" @endif>
                        {{ $item['label'] }}
                        @if ($current)<span class="absolute inset-x-2.5 -bottom-[0.95rem] h-[3px] rounded-full bg-accent xl:inset-x-3.5" aria-hidden="true"></span>@endif
                    </a>
                @endforeach
            </nav>

            <a href="{{ tel_href($phone) }}" class="hidden min-h-11 flex-none items-center gap-2 rounded px-2 text-sm font-semibold text-white/85 hover:text-white xl:inline-flex"><x-icon name="phone" class="h-4 w-4" />{{ format_phone($phone) }}</a>
            <button type="button" onclick="toggleTheme()" class="hidden h-11 w-11 flex-none items-center justify-center rounded-full text-white/75 hover:bg-white/10 hover:text-white sm:inline-flex" aria-label="Switch light or dark theme">
                <x-icon name="moon" class="h-5 w-5 dark-hide" /><x-icon name="sun" class="hidden h-5 w-5 dark-show" />
            </button>
            <a href="{{ $tdUrl }}" class="btn btn-accent btn-sm hidden flex-none whitespace-nowrap lg:inline-flex">Book a test drive<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
            <button type="button" class="inline-flex h-11 w-11 flex-none items-center justify-center rounded-full ring-1 ring-white/25 lg:hidden" @click="menu = !menu" :aria-expanded="menu.toString()" aria-controls="mobile-menu" aria-label="Menu">
                <x-icon name="menu" x-show="!menu" /><x-icon name="x" x-show="menu" x-cloak />
            </button>
        </div>
        <div id="mobile-menu" class="border-t border-white/10 bg-graphite-900 lg:hidden" x-show="menu" x-cloak x-transition.opacity>
            <nav class="wrap flex flex-col py-3" aria-label="Mobile">
                <a href="{{ route('home') }}" class="rounded px-1 py-3 text-xl font-bold">Home</a>
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}" class="rounded px-1 py-3 text-xl font-bold" @if(request()->routeIs($item['match'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
                <a href="{{ route('trade-in') }}" class="rounded px-1 py-3 text-xl font-bold">Value my trade-in</a>
                <div class="mt-3 grid gap-3 border-t border-white/10 pt-5 sm:grid-cols-2">
                    <a href="{{ $tdUrl }}" class="btn btn-accent">Book a test drive</a>
                    <a href="{{ tel_href($phone) }}" class="btn btn-on-dark"><x-icon name="phone" class="h-4 w-4" />{{ format_phone($phone) }}</a>
                </div>
                <button type="button" onclick="toggleTheme()" class="mt-3 flex min-h-11 items-center gap-2 rounded px-1 text-left font-semibold text-white/75"><x-icon name="moon" class="h-5 w-5" />Switch light or dark theme</button>
            </nav>
        </div>
    </header>

    <main id="main">
        {{ $slot }}
    </main>

    <footer class="mt-24 bg-graphite-950 text-white/75">
        <div class="ticks text-white" aria-hidden="true"></div>
        <div class="wrap grid gap-12 py-16 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1.1fr]">
            <div>
                <x-logo tone="light" />
                <p class="mt-5 max-w-xs text-white/70">Antigua and Barbuda's Nissan and Suzuki dealer. Family-run on Old Parham Road since 1972.</p>
                <a href="{{ tel_href($phone) }}" class="font-display mt-6 inline-flex min-h-12 items-center gap-2.5 text-2xl font-bold text-white hover:text-accent"><x-icon name="phone" class="h-5 w-5" />{{ format_phone($phone) }}</a>
                <p class="mt-1"><a class="inline-flex min-h-11 items-center hover:text-white" href="mailto:{{ $email }}">{{ $email }}</a></p>
            </div>
            <div>
                <h2 class="kicker font-sans text-white/55">Buy</h2>
                <ul class="mt-4 space-y-1">
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('lineup.brand', 'nissan') }}">New Nissan</a></li>
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('lineup.brand', 'suzuki') }}">New Suzuki</a></li>
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('inventory.index') }}">Pre-owned stock</a></li>
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('finance') }}">Finance with FDC</a></li>
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('trade-in') }}">Value my trade-in</a></li>
                </ul>
            </div>
            <div>
                <h2 class="kicker font-sans text-white/55">Own</h2>
                <ul class="mt-4 space-y-1">
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('service') }}#book">Book a service</a></li>
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('parts') }}">Order a part</a></li>
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('about') }}">About Hadeed Motors</a></li>
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('group') }}">Group companies</a></li>
                    <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('contact') }}">Contact and hours</a></li>
                </ul>
            </div>
            <div>
                <h2 class="kicker font-sans text-white/55">Showroom</h2>
                <address class="mt-5 not-italic leading-relaxed">
                    {{ $address['building'] }}<br>{{ $address['street'] }}, {{ $address['city'] }}<br>{{ $address['country'] }}
                </address>
                <a class="mt-1 inline-flex min-h-11 items-center gap-1.5 hover:text-white" href="{{ hadeed('map_url') }}" rel="noopener" target="_blank">Get directions<x-icon name="external" class="h-3.5 w-3.5" /><span class="sr-only">(opens Google Maps)</span></a>
                <dl class="mt-4 space-y-1 text-sm">
                    @foreach (\App\Support\Hours::rows() as $days => $time)
                        <div class="flex justify-between gap-4"><dt>{{ $days }}</dt><dd class="num whitespace-nowrap text-white">{{ $time }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="wrap flex flex-col gap-3 py-6 text-sm text-white/60 md:flex-row md:items-center md:justify-between">
                <p>© {{ date('Y') }} {{ rtrim(hadeed('name'), '.') }}. Nissan and Suzuki are trademarks of their owners.</p>
                <p class="flex flex-wrap gap-x-5">
                    <a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('privacy') }}">Privacy</a>
                    <a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('credits') }}">Photo credits</a>
                    <a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('sitemap.page') }}">Site map</a>
                    <a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('login') }}">Staff sign in</a>
                </p>
            </div>
        </div>
    </footer>

    @if ($mobileBar)
        {{-- Thumb-reach actions on phones: call, WhatsApp (when configured), test drive --}}
        <nav aria-label="Quick actions" class="fixed inset-x-0 bottom-0 z-30 border-t border-white/10 bg-graphite-900/95 px-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 backdrop-blur lg:hidden">
            <div class="flex gap-2">
                <a href="{{ tel_href($phone) }}" class="btn btn-on-dark flex-1 px-2 text-[0.9375rem]"><x-icon name="phone" class="h-4 w-4" />Call</a>
                @if (hadeed('whatsapp'))
                    <a href="{{ wa_href(null, 'Hello Hadeed Motors, I have a question.') }}" class="btn btn-on-dark flex-1 px-2 text-[0.9375rem]" rel="noopener" target="_blank"><x-icon name="whatsapp" class="h-4 w-4" />WhatsApp</a>
                @endif
                <a href="{{ $tdUrl }}" class="btn btn-accent flex-[1.4] whitespace-nowrap px-2 text-[0.9375rem]">Test drive</a>
            </div>
        </nav>
    @endif
</body>
</html>
