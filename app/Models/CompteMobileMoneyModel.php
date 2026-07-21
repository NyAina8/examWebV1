<?php

namespace App\Models;

use CodeIgniter\Model;

class CompteMobileMoneyModel extends Model
{
    protected $table = 'comptes_mobile_money';
    protected $primaryKey = 'id_compte';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id_client',
        'id_prefixe',
        'numero_telephone',
        'solde',
        'statut',
        'pourcentage_epargne',
        'solde_epargne',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findByNumero(string $numeroTelephone): ?array
    {
        return $this->where('numero_telephone', $numeroTelephone)->first();
    }
}
