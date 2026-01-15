<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowApprobation;

class WorkflowApprobationPolicy
{
    public function viewAny(User $user): bool { return $user->can('workflow.viewAny'); }
    public function view(User $user, WorkflowApprobation $wf): bool { return $user->can('workflow.view'); }
    public function create(User $user): bool { return $user->can('workflow.create'); }
    public function update(User $user, WorkflowApprobation $wf): bool { return $user->can('workflow.update'); }
    public function publish(User $user, WorkflowApprobation $wf): bool { return $user->can('workflow.publish'); }
    public function delete(User $user, WorkflowApprobation $wf): bool { return $user->can('workflow.delete'); }
    public function bind(User $user, WorkflowApprobation $wf): bool { return $user->can('workflow.bind'); }
    public function admin(User $user): bool { return $user->can('workflow.admin'); }
}
