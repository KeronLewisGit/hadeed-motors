<?php

namespace App\Http\Controllers;

use App\Enums\Condition;
use App\Models\Setting;
use App\Models\Vehicle;
use App\Support\Booking;
use App\Support\Lineup;
use App\Support\LoanEstimator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'featured' => Vehicle::available()->with('coverPhoto')->where('condition', Condition::Used)
                ->orderByDesc('featured')->latest()->limit(4)->get(),
            'usedCount' => Vehicle::available()->where('condition', Condition::Used)->count(),
            'financeRate' => Setting::financeRate(),
        ]);
    }

    public function service(): View
    {
        return view('pages.service', [
            'serviceDays' => Booking::serviceDays(),
        ]);
    }

    /** Finance page. The estimator works as a plain GET form too, using the same formula as the JS. */
    public function finance(Request $request): View
    {
        $rate = Setting::financeRate();
        $price = amount_input($request->query('price'));
        $deposit = amount_input($request->query('deposit'));
        $term = (int) input_string($request->query('term'));
        $term = in_array($term, (array) config('hadeed.finance.terms'), true) ? $term : 60;

        return view('pages.finance', [
            'rate' => $rate,
            'input' => ['price' => $price ?: null, 'deposit' => $deposit ?: null, 'term' => $term],
            'estimate' => $price > 0 ? LoanEstimator::estimate($price, $deposit, $term, $rate) : null,
        ]);
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function group(): View
    {
        return view('pages.group');
    }

    public function contact(): View
    {
        return view('pages.contact', ['interests' => Lineup::options()]);
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function credits(): View
    {
        return view('pages.credits');
    }

    public function sitemapPage(): View
    {
        return view('pages.sitemap', [
            'stock' => Vehicle::listed()->orderBy('make')->orderBy('model')->get(['slug', 'year', 'make', 'model', 'trim', 'condition']),
        ]);
    }

    public function sitemap(): Response
    {
        $static = ['home', 'lineup.index', 'inventory.index', 'finance', 'finance.apply', 'service', 'parts', 'trade-in', 'test-drive',
            'about', 'group', 'contact', 'privacy', 'credits', 'sitemap.page'];
        $urls = collect($static)->map(fn (string $name) => ['loc' => route($name), 'lastmod' => null])
            ->concat(collect(Lineup::brands())->map(fn (string $b) => ['loc' => route('lineup.brand', $b), 'lastmod' => null]))
            ->concat(collect(Lineup::all())->map(fn (array $m, string $key) => ['loc' => route('lineup.show', [$m['brand'], $key]), 'lastmod' => null])->values())
            ->concat(Vehicle::listed()->where('condition', Condition::Used)->get()->map(fn (Vehicle $v) => [
                'loc' => route('inventory.show', $v),
                'lastmod' => $v->updated_at?->toAtomString(),
            ]));

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }

    /**
     * The old single-page site used /index.html?page=…&model=…; send those links
     * (bookmarks, search results, Facebook posts) to the matching new page.
     */
    public function legacy(Request $request): RedirectResponse
    {
        $page = input_string($request->query('page'));
        $model = input_string($request->query('model'));

        if ($page === 'vehicle' && ($brand = Lineup::brandOf($model))) {
            return redirect()->route('lineup.show', [$brand, $model], 301);
        }

        $route = match ($page) {
            'vehicles' => 'lineup.index',
            'about-us', 'who-we-are', 'about/who-we-are' => 'about',
            'contact', 'request-callback', 'faqs' => 'contact',
            'schedule-service', 'owners/schedule-service' => 'service',
            'parts-enquiry', 'owners/parts-enquiry' => 'parts',
            'schedule-test-drive' => 'test-drive',
            default => 'home',
        };

        return redirect()->route($route, [], 301);
    }
}
