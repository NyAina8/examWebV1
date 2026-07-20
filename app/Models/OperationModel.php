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
        'montant',
        'frais',
        'solde_source_apres',
        'solde_destination_apres',
        'statut',
        'description',
        'created_at',
    ];
    protected $useTimestamps = false;
}
