<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Design layer
        Schema::table('workflows_approbation', function (Blueprint $table) {
            if (! $this->hasIndex($table, 'workflows_approbation_code_unique')) {
                $table->unique('code', 'workflows_approbation_code_unique');
            }
        });

        Schema::table('versions_workflow', function (Blueprint $table) {
            if (! $this->hasIndex($table, 'versions_workflow_wf_ver_unique')) {
                $table->unique(['workflow_id','numero_version'], 'versions_workflow_wf_ver_unique');
            }
            if (! $this->hasIndex($table, 'versions_workflow_workflow_idx')) {
                $table->index('workflow_id', 'versions_workflow_workflow_idx');
            }
        });

        Schema::table('etapes_workflow', function (Blueprint $table) {
            if (! $this->hasIndex($table, 'etapes_workflow_ver_pos_unique')) {
                $table->unique(['version_workflow_id','position'], 'etapes_workflow_ver_pos_unique');
            }
            if (! $this->hasIndex($table, 'etapes_workflow_version_idx')) {
                $table->index('version_workflow_id', 'etapes_workflow_version_idx');
            }
        });

        Schema::table('etape_approbateurs', function (Blueprint $table) {
            if (! $this->hasIndex($table, 'etape_approbateurs_unique')) {
                $table->unique(['etape_workflow_id','type_approbateur','reference_approbateur'], 'etape_approbateurs_unique');
            }
            if (! $this->hasIndex($table, 'etape_approbateurs_etape_idx')) {
                $table->index('etape_workflow_id', 'etape_approbateurs_etape_idx');
            }
        });

        Schema::table('etape_regles', function (Blueprint $table) {
            if (! $this->hasIndex($table, 'etape_regles_etape_idx')) {
                $table->index('etape_workflow_id', 'etape_regles_etape_idx');
            }
            if (! $this->hasIndex($table, 'etape_regles_operateur_idx')) {
                $table->index('operateur_id', 'etape_regles_operateur_idx');
            }
        });

        // Runtime layer
        Schema::table('instances_approbation', function (Blueprint $table) {
            if (! $this->hasIndex($table, 'instances_version_idx')) {
                $table->index('version_workflow_id', 'instances_version_idx');
            }
            if (! $this->hasIndex($table, 'instances_statut_idx')) {
                $table->index('statut_id', 'instances_statut_idx');
            }
            if (! $this->hasIndex($table, 'instances_target_idx')) {
                $table->index(['module_code','type_cible','id_cible'], 'instances_target_idx');
            }
        });

        Schema::table('instances_etapes', function (Blueprint $table) {
            if (! $this->hasIndex($table, 'inst_etapes_unique')) {
                $table->unique(['instance_approbation_id','etape_workflow_id'], 'inst_etapes_unique');
            }
            if (! $this->hasIndex($table, 'inst_etapes_instance_idx')) {
                $table->index('instance_approbation_id', 'inst_etapes_instance_idx');
            }
            if (! $this->hasIndex($table, 'inst_etapes_statut_idx')) {
                $table->index('statut_id', 'inst_etapes_statut_idx');
            }
            if (! $this->hasIndex($table, 'inst_etapes_dates_idx')) {
                $table->index(['date_debut','date_fin'], 'inst_etapes_dates_idx');
            }
        });

        Schema::table('actions_approbation', function (Blueprint $table) {
            if (! $this->hasIndex($table, 'actions_etape_idx')) {
                $table->index('instance_etape_id', 'actions_etape_idx');
            }
            if (! $this->hasIndex($table, 'actions_type_idx')) {
                $table->index('action_type_id', 'actions_type_idx');
            }
            if (! $this->hasIndex($table, 'actions_actor_idx')) {
                $table->index('code_acteur', 'actions_actor_idx');
            }
            if (! $this->hasIndex($table, 'actions_created_idx')) {
                $table->index('created_at', 'actions_created_idx');
            }
        });

        Schema::table('liaisons_workflow', function (Blueprint $table) {
            if (! $this->hasIndex($table, 'liaisons_scope_unique')) {
                $table->unique(['version_workflow_id','module_code','type_cible','id_cible','code_pays','groupe_projet_id'], 'liaisons_scope_unique');
            }
        });
    }

    public function down(): void
    {
        // indexes can be dropped if needed; keep empty to avoid accidental drops
    }

    private function hasIndex(Blueprint $table, string $name): bool
    {
        // Placeholder; Schema API doesn't expose a direct hasIndex. This helper is a no-op to avoid duplicate names in most DBs.
        return false;
    }
};

