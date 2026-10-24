<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FinanceStatus;
use App\Enums\LeadType;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Finance pre-qualifications as a pipeline, for the finance desk (and admins, who can't open documents). */
class FinanceController extends Controller
{
    public function index(): View
    {
        $leads = Lead::where('type', LeadType::Finance)->with(['vehicle', 'assignee'])->withCount('files')->latest('id')->limit(400)->get();

        return view('admin.finance.index', [
            'columns' => collect(FinanceStatus::cases())->map(fn (FinanceStatus $s) => [
                'status' => $s,
                'leads' => $leads->filter(fn (Lead $l) => $l->finance_status === $s)->values(),
            ]),
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        abort_unless($lead->type === LeadType::Finance, 404);
        $data = $request->validate(['finance_status' => ['required', Rule::enum(FinanceStatus::class)]]);
        $status = FinanceStatus::from($data['finance_status']);
        /** @var User $user */
        $user = $request->user();

        if ($lead->finance_status !== $status) {
            $lead->finance_status = $status;
            $lead->first_contacted_at ??= $status !== FinanceStatus::Received ? now() : null;
            $lead->save();
            $lead->notes()->create(['user_id' => $user->id, 'kind' => 'finance', 'body' => 'Finance status: '.$status->label().'.']);
        }

        return back()->with('status', "{$lead->reference}: {$status->label()}.");
    }
}
