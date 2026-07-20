<?php

namespace App\Models;

use CodeIgniter\Model;

class TypeOperationModel extends Model
{
    protected $table = 'types_operations';
    protected $primaryKey = 'id_type_operation';
    protected $returnType = 'array';
    protected $allowedFields = [
        'code',
        'libelle',
        'actif',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
