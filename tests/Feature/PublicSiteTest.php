<?php

namespace Tests\Feature;

use App\Enums\Condition;
use App\Enums\VehicleStatus;
use App\Models\Setting;
use App\Models\Vehicle;
use App\Support\LoanEstimator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_public_page_renders(): void
    {
        $vehicle = Vehicle::factory()->create(['slug' => 'test-car']);

        foreach (['/', '/new', '/new/nissan', '/new/suzuki', '/new/suzuki/jimny', '/new/nissan/frontier', '/pre-owned', '/pre-owned/test-car',
            '/finance', '/finance/apply', '/service', '/parts', '/trade-in', '/test-drive', '/test-drive?vehicle=test-car', '/test-drive?model=xl7',
            '/about', '/group', '/contact', '/privacy', '/photo-credits', '/sitemap'] as $url) {
            $this->get($url)->assertOk()->assertSee('Hadeed Motors');
        }
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml')->assertSee(route('inventory.show', $vehicle), false);
    }

    public function test_every_inner_page_uses_the_shared_page_header(): void
    {
        foreach (['/new', '/pre-owned', '/finance', '/service', '/parts', '/trade-in', '/test-drive', '/about', '/group', '/contact', '/privacy', '/photo-credits', '/sitemap'] as $url) {
            $this->get($url)->assertSee('class="ticks text-white"', false)->assertSee('plate-tag on-dark', false);
        }
    }

    public function test_the_page_carries_none_of_the_old_sites_placeholder_text(): void
    {
        $html = $this->get('/')->getContent();
        foreach (['Car 1', 'description 1', 'Slide 2', 'adssadf', 'Develpment', 'Premoum', 'bbc.com', 'Lorem ipsum', 'Bermuda'] as $junk) {
            $this->assertStringNotContainsString($junk, (string) $html);
        }
    }

    public function test_unknown_brand_or_model_is_404(): void
    {
        $this->get('/new/toyota')->assertNotFound();
        $this->get('/new/nissan/jimny')->assertNotFound();
        $this->get('/new/suzuki/nope')->assertNotFound();
    }

    public function test_old_single_page_links_redirect(): void
    {
        $this->get('/index.html?page=vehicle&model=jimny')->assertRedirect('/new/suzuki/jimny')->assertStatus(301);
        $this->get('/index.html?page=schedule-service')->assertRedirect('/service');
        $this->get('/index.html?page=parts-enquiry')->assertRedirect('/parts');
        $this->get('/index.html?page=schedule-test-drive')->assertRedirect('/test-drive');
        $this->get('/index.html?page=about-us')->assertRedirect('/about');
        $this->get('/index.html')->assertRedirect('/');
    }

    public function test_inventory_filters_work_and_ignore_junk(): void
    {
        Vehicle::factory()->create(['make' => 'Nissan', 'model' => 'X-Trail', 'body_type' => 'SUV', 'transmission' => 'automatic', 'year' => 2019, 'price' => 52000]);
        Vehicle::factory()->create(['make' => 'Suzuki', 'model' => 'Swift', 'body_type' => 'Hatchback', 'transmission' => 'manual', 'year' => 2018, 'price' => 31000]);
        Vehicle::factory()->create(['make' => 'Suzuki', 'model' => 'Vitara', 'body_type' => 'SUV', 'transmission' => 'automatic', 'year' => 2021, 'price' => null]);

        $this->get('/pre-owned?make=Suzuki')->assertSee('Swift')->assertSee('Vitara')->assertDontSee('X-Trail');
        $this->get('/pre-owned?body=SUV&transmission=automatic')->assertSee('X-Trail')->assertSee('Vitara')->assertDontSee('Swift');
        $this->get('/pre-owned?year_min=2020')->assertSee('Vitara')->assertDontSee('Swift');
        // A price cap leaves out "Call for price" cars.
        $this->get('/pre-owned?price_max=45000')->assertSee('Swift')->assertDontSee('X-Trail')->assertDontSee('Vitara');
        $this->get('/pre-owned?make[]=x&body=<script>&year_min=abc&sort=drop')->assertOk()->assertSee('X-Trail');
    }

    public function test_hidden_and_long_sold_cars_are_not_listed(): void
    {
        Vehicle::factory()->create(['model' => 'Visible']);
        $hidden = Vehicle::factory()->create(['model' => 'Hiddenmodel', 'published' => false]);
        $sold = Vehicle::factory()->create(['model' => 'Oldsold', 'status' => VehicleStatus::Sold, 'sold_at' => now()->subDays(10)]);
        Vehicle::factory()->create(['model' => 'Justsold', 'status' => VehicleStatus::Sold, 'sold_at' => now()->subDay()]);

        $this->get('/pre-owned')->assertSee('Visible')->assertSee('Justsold')->assertDontSee('Hiddenmodel')->assertDontSee('Oldsold');
        $this->get(route('inventory.show', $hidden))->assertNotFound();
        $this->get(route('inventory.show', $sold))->assertNotFound();
    }

    public function test_new_stock_shows_on_its_model_page(): void
    {
        Vehicle::factory()->create(['condition' => Condition::New, 'make' => 'Suzuki', 'model' => 'Jimny', 'model_key' => 'jimny', 'trim' => 'GL', 'mileage_km' => null]);

        $this->get('/new/suzuki/jimny')->assertSee('Jimny on the lot now');
        $this->get('/new/suzuki/xl7')->assertDontSee('on the lot now');
    }

    public function test_prices_default_to_call_for_price(): void
    {
        $v = Vehicle::factory()->create(['price' => null]);
        $this->get(route('inventory.show', $v))->assertSee('Call for price');
        $v->update(['price' => 45900]);
        $this->get(route('inventory.show', $v))->assertSee('EC$45,900')->assertSee('about US$17,000');
    }

    public function test_loan_estimator_is_hidden_until_a_rate_is_set(): void
    {
        $this->get('/finance')->assertDontSee('Estimate a monthly payment')->assertSee('Get pre-qualified');
        $v = Vehicle::factory()->create(['price' => 60000]);
        $this->get(route('inventory.show', $v))->assertDontSee('a month');

        Setting::put('finance_indicative_rate', '9');
        $this->get('/finance')->assertSee('Estimate a monthly payment')->assertSee('9% a year');
        $this->get(route('inventory.show', $v))->assertSee('a month')->assertSee('An estimate only');
    }

    public function test_estimator_works_without_javascript_using_the_same_formula(): void
    {
        Setting::put('finance_indicative_rate', '9');
        $expected = money(LoanEstimator::monthly(50000, 9, 60), true);

        $this->get('/finance?price=60000&deposit=10000&term=60')->assertSee($expected)->assertSee('Borrowing EC$50,000 over 60 months');
    }

    public function test_loan_formula(): void
    {
        $this->assertSame(1037.92, LoanEstimator::monthly(50000, 9, 60));
        $this->assertSame(1000.0, LoanEstimator::monthly(60000, 0, 60));
        $this->assertSame(0.0, LoanEstimator::monthly(0, 9, 60));
        $this->assertNull(LoanEstimator::estimate(60000, 0, 60, null));
        // Deposit larger than the price is capped, never a negative loan.
        $this->assertSame(0, LoanEstimator::estimate(20000, 50000, 60, 9)['principal']);
    }

    public function test_js_and_php_estimators_share_one_formula(): void
    {
        $js = (string) file_get_contents(base_path('resources/js/app.js'));
        $this->assertStringContainsString('(principal * r) / (1 - Math.pow(1 + r, -months))', $js);
        $this->assertStringContainsString('annualRate / 100 / 12', $js);
    }

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
