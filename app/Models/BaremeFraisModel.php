<?php

namespace App\Models;

use CodeIgniter\Model;

class BaremeFraisModel extends Model
{
    protected $table = 'baremes_frais';
    protected $primaryKey = 'id_bareme';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id_type_operation',
        'montant_min',
        'montant_max',
        'frais',
        'actif',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findForAmount(int $typeOperationId, int $montant): ?array
    {
        return $this->where('id_type_operation', $typeOperationId)
            ->where('actif', 1)
            ->where('montant_min <=', $montant)
            ->groupStart()
                ->where('montant_max >=', $montant)
                ->orWhere('montant_max', null)
            ->groupEnd()
            ->orderBy('montant_min', 'DESC')
            ->first();
    }
}
