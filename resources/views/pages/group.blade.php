<x-layouts.site title="Group companies" description="The Hadeed Motors group in Antigua: Finance & Development Co. (FDC), Caribbean Premium Motors (BMW) and HML Autos.">
    <x-page-header title="Part of the Hadeed group" lead="Hadeed Motors sits alongside a finance house and a premium franchise. Here's who does what." code="GRP" label="Group companies" photo="antigua-coast" />
    <section class="wrap mt-14 grid gap-6 md:mt-20 md:grid-cols-3">
        @foreach (hadeed('group') as $c)
            <article class="panel flex flex-col p-6 md:p-7">
                <h2 class="text-xl font-bold font-sans">{{ $c['name'] }}</h2>
                <p class="mt-2 text-ink-2">{{ $c['what'] }}</p>
                <div class="mt-auto flex flex-wrap gap-3 pt-6">
                    @if ($c['url'])<a href="{{ $c['url'] }}" class="btn btn-outline btn-sm" target="_blank" rel="noopener">Visit<x-icon name="external" class="h-4 w-4" /></a>@endif
                    @if ($c['phone'])<a href="{{ tel_href($c['phone']) }}" class="btn btn-outline btn-sm"><x-icon name="phone" class="h-4 w-4" />{{ format_phone($c['phone']) }}</a>@endif
                </div>
            </article>
        @endforeach
    </section>
</x-layouts.site>
