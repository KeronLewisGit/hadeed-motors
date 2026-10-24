<?php

namespace App\Policies;

use App\Enums\LeadType;
use App\Enums\Role;
use App\Models\Lead;
use App\Models\LeadFile;
use App\Models\User;

class LeadPolicy
{
    /** Each role sees the lead types it works (Role::leadTypes); admins see all. */
    public function view(User $user, Lead $lead): bool
    {
        return $user->canWork($lead->type);
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->canWork($lead->type);
    }

    /** Employment, income band and deposit on a finance application. */
    public function viewFinanceDetails(User $user, Lead $lead): bool
    {
        return $lead->type === LeadType::Finance && $user->hasRole(Role::Finance, Role::Admin);
    }

    /** ID and pay slips open for the finance role only, not even admins. Other uploads follow view(). */
    public function openFile(User $user, Lead $lead, LeadFile $file): bool
    {
        if ($file->lead_id !== $lead->id) {
            return false;
        }
        if ($file->isFinanceDocument()) {
            return $user->role === Role::Finance;
        }

        return $this->view($user, $lead);
    }
}
