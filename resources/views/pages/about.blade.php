<x-layouts.site title="About us" description="Hadeed Motors opened in 1972 under Senator Aziz Hadeed with the Datsun (Nissan) franchise. Today it sells Nissan and Suzuki from Old Parham Road, St John's.">
    <x-page-header title="A family business, on Old Parham Road since 1972." lead="Putting the ownership of a motor car within the reach of the average family in Antigua and Barbuda." code="1972" label="About us" photo="showroom" position="20% 50%" />

    <section class="wrap mt-14 grid gap-12 md:mt-20 lg:grid-cols-[1.3fr_1fr] lg:gap-16">
        <div class="prose-copy measure text-[1.0625rem] text-ink-2">
            <p class="lead !text-ink">When Hadeed Motors Ltd. started in 1972 under the watchful eye of Senator Aziz Hadeed, it did so with a simple philosophy: putting the ownership of a motor car within the reach of the average family in Antigua and Barbuda.</p>
            <p>The company began with the Nissan dealership, when the cars still wore the Datsun badge. Daihatsu followed in 1992, Suzuki in 1995, and later Infiniti. Today the showroom on Old Parham Road sells new Nissan and Suzuki vehicles alongside quality pre-owned stock, with a workshop and parts counter on the same site.</p>
            <p>Several of Antigua's car-rental companies speak highly of the support they received from Hadeed Motors, which helped them grow into major players in the rental industry. The company credits long relationships with customers and partners, among them the Gregory brothers (Conroy and Caswald), Sonny Francis, Eugene Matthew, Oakland Richards, Allan Gomes and Steadman "Stead" Colbourne, for helping it grow.</p>
            <p>In 1984 the group added Finance &amp; Development Co., which still lends from the floor above the showroom, so a customer can choose a car and arrange the loan in one visit. In 2016 Business View Caribbean described Hadeed Motors as Antigua and Barbuda's largest auto dealer.</p>
            <h2>What we stand for</h2>
            <p>The Hadeed Group aims to give customers superior value; employees, business partners and the community a share in its success; and shareholders fair returns. It commits to being a good corporate citizen, to prudent, sustainable growth, and to the highest ethical standards.</p>
        </div>
        <aside class="space-y-8">
            <ol class="relative space-y-7 border-l-2 border-line pl-8" aria-label="Milestones">
                @foreach (hadeed('history') as $h)
                    <li class="relative">
                        <span class="absolute -left-[2.45rem] top-1 h-4 w-4 rounded-full border-[3px] border-bg bg-accent" aria-hidden="true"></span>
                        @if (ctype_digit($h['year']))<x-odo :value="$h['year']" class="text-lg" />@else<p class="font-display text-lg font-medium">{{ $h['year'] }}</p>@endif
                        <p class="mt-2 text-ink-2">{{ $h['text'] }}</p>
                    </li>
                @endforeach
            </ol>
            <x-photo key="shirley-heights" alt="English Harbour seen from Shirley Heights, Antigua" sizes="(min-width: 1024px) 35vw, 100vw" class="aspect-[3/2] rounded-[14px]" />
        </aside>
    </section>

    <section class="wrap mt-20">
        <div class="grid gap-5 md:grid-cols-3">
            <a href="{{ route('group') }}" class="panel group p-6 hover:border-ink"><p class="text-lg font-bold">Group companies</p><p class="mt-1 text-ink-2">FDC, Caribbean Premium Motors and more.</p><x-icon name="arrow-right" class="mt-3 h-5 w-5 transition-transform group-hover:translate-x-1" /></a>
            <a href="{{ route('lineup.index') }}" class="panel group p-6 hover:border-ink"><p class="text-lg font-bold">The new line-up</p><p class="mt-1 text-ink-2">Every Nissan and Suzuki we sell new.</p><x-icon name="arrow-right" class="mt-3 h-5 w-5 transition-transform group-hover:translate-x-1" /></a>
            <a href="{{ route('contact') }}" class="panel group p-6 hover:border-ink"><p class="text-lg font-bold">Visit the showroom</p><p class="mt-1 text-ink-2">Hours, directions and phone numbers.</p><x-icon name="arrow-right" class="mt-3 h-5 w-5 transition-transform group-hover:translate-x-1" /></a>
        </div>
    </section>
</x-layouts.site>
