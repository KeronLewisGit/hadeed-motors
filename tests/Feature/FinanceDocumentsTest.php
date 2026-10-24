<?php

namespace Tests\Feature;

use App\Enums\FinanceStatus;
use App\Enums\LeadType;
use App\Enums\Role;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** ID and pay slips are the most sensitive things on the system: only the finance role opens them. */
class FinanceDocumentsTest extends TestCase
{
    use RefreshDatabase;

    private Lead $lead;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->lead = Lead::factory()->type(LeadType::Finance)->create([
            'finance_status' => FinanceStatus::Received,
            'details' => ['income_band' => '8_12', 'employer' => 'Secret Employer Ltd', 'deposit' => 20000, 'residency' => 'citizen', 'employment' => 'employed', 'years_employed' => 'o5'],
        ]);
        foreach (['id' => 'passport.pdf', 'payslip' => 'slip.pdf'] as $kind => $name) {
            Storage::disk('local')->put("leads/{$this->lead->id}/{$kind}.pdf", '%PDF-1.4 test');
            $this->lead->files()->create(['kind' => $kind, 'path' => "leads/{$this->lead->id}/{$kind}.pdf", 'original_name' => $name, 'mime' => 'application/pdf', 'size' => 14]);
        }
    }

    public function test_finance_role_can_open_documents_and_it_is_logged(): void
    {
        $officer = $this->staff(Role::Finance);
        foreach ($this->lead->files as $file) {
            $this->actingAs($officer)->get(route('admin.leads.file', [$this->lead, $file]))->assertOk()->assertDownload();
        }
        $this->assertSame(2, $this->lead->notes()->where('body', 'like', 'Opened %')->count());
        $this->actingAs($officer)->get(route('admin.leads.show', $this->lead))->assertSee('Secret Employer Ltd')->assertSee('EC$8,000 to EC$12,000');
    }

    public function test_admin_sales_and_service_cannot_open_documents(): void
    {
        foreach ([Role::Admin, Role::Sales, Role::Service] as $role) {
            $user = $this->staff($role);
            foreach ($this->lead->files as $file) {
                $this->actingAs($user)->get(route('admin.leads.file', [$this->lead, $file]))->assertForbidden();
            }
        }
        $this->assertSame(0, $this->lead->notes()->where('body', 'like', 'Opened %')->count());
    }

    public function test_sales_sees_the_lead_but_not_the_financial_details(): void
    {
        $this->actingAs($this->staff(Role::Sales))->get(route('admin.leads.show', $this->lead))->assertOk()
            ->assertSee($this->lead->name)->assertSee('Finance team only')
            ->assertDontSee('Secret Employer Ltd')->assertDontSee('EC$8,000 to EC$12,000')->assertDontSee('EC$20,000');
        // Admins see the figures (to manage the pipeline) but still get a lock on the documents.
        $this->actingAs($this->staff(Role::Admin))->get(route('admin.leads.show', $this->lead))->assertSee('Secret Employer Ltd')->assertSee('Finance team only');
    }

    public function test_guests_and_mismatched_files_are_refused(): void
    {
        $file = $this->lead->files->first();
        $this->get(route('admin.leads.file', [$this->lead, $file]))->assertRedirect('/login');

        $other = Lead::factory()->type(LeadType::Finance)->create();
        $this->actingAs($this->staff(Role::Finance))->get(route('admin.leads.file', [$other, $file]))->assertForbidden();
    }

    public function test_documents_are_never_reachable_from_the_public_web_root(): void
    {
        foreach ($this->lead->files as $file) {
            $this->assertFileDoesNotExist(public_path($file->path));
            $this->get('/'.$file->path)->assertNotFound();
            $this->get('/storage/'.$file->path)->assertNotFound();
        }
    }
}
