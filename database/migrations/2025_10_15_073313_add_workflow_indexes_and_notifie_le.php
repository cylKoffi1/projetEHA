<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // instance_approbations
        Schema::table('instance_approbations', function (Blueprint $t) {
            if (!Schema::hasColumn('instance_approbations', 'date_fin')) {
                $t->timestamp('date_fin')->nullable()->after('instantane');
            }
            $t->index(['module_code','type_cible','id_cible'], 'ia_module_type_id_idx');
            $t->index(['statut_id'], 'ia_statut_idx');
        });

        // instance_etapes
        Schema::table('instance_etapes', function (Blueprint $t) {
            if (!Schema::hasColumn('instance_etapes', 'notifie_le')) {
                $t->timestamp('notifie_le')->nullable()->after('date_debut');
            }
            $t->index(['instance_approbation_id'], 'ie_instance_idx');
            $t->index(['statut_id'], 'ie_statut_idx');
        });

        // liaisons_workflow
        Schema::table('liaisons_workflow', function (Blueprint $t) {
            $t->index(['module_code','type_cible','id_cible'], 'lw_module_type_id_idx');
            $t->index(['par_defaut'], 'lw_default_idx');
            $t->index(['code_pays'], 'lw_pays_idx');
            $t->index(['groupe_projet_id'], 'lw_groupe_idx');
        });

        // version_workflow
        Schema::table('versions_workflow', function (Blueprint $t) {
            $t->index(['workflow_id','numero_version'], 'vw_wf_num_idx');
            $t->index(['publie'], 'vw_publie_idx');
        });

        // etape_workflow
        Schema::table('etapes_workflow', function (Blueprint $t) {
            $t->index(['version_workflow_id','position'], 'ew_version_pos_idx');
        });

        // action_approbations
        Schema::table('actions_approbation', function (Blueprint $t) {
            $t->index(['created_at'], 'aa_created_idx');
            $t->index(['instance_etape_id'], 'aa_step_idx');
            $t->index(['code_acteur'], 'aa_actor_idx');
            $t->index(['action_type_id'], 'aa_type_idx');
        });
    }

    public function down(): void
    {
        // Optionnel : drop indexes/columns si besoin
    }
};
