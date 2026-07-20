<?php

namespace App\Models;

use CodeIgniter\Model;

class OperateurMobileMoneyModel extends Model
{
    protected $table = 'operateurs';
    protected $primaryKey = 'id_operateur';
    protected $returnType = 'array';
    protected $allowedFields = [
        'code',
        'nom',
        'principal',
        'commission_transfert_externe',
        'actif',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
