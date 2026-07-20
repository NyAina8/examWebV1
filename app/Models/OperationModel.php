<?php

namespace App\Models;

use CodeIgniter\Model;

class OperationModel extends Model
{
    protected $table = 'operations';
    protected $primaryKey = 'id_operation';
    protected $returnType = 'array';
    protected $allowedFields = [
        'reference',
        'id_type_operation',
        'id_compte_source',
        'id_compte_destination',
        'id_operateur_source',
        'id_operateur_destination',
        'numero_destinataire',
        'montant',
        'frais',
        'pourcentage_commission',
        'frais_retrait_inclus',
        'commission_interoperateur',
        'montant_reverser',
        'total_debite',
        'montant_recu',
        'id_envoi_multiple',
        'solde_source_apres',
        'solde_destination_apres',
        'statut',
        'description',
        'created_at',
    ];
    protected $useTimestamps = false;
}
