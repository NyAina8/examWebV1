<?php

namespace App\Models;

use CodeIgniter\Model;

class PrefixeTelephoniqueModel extends Model
{
    protected $table = 'prefixes_telephoniques';
    protected $primaryKey = 'id_prefixe';
    protected $returnType = 'array';
    protected $allowedFields = [
        'prefixe',
        'operateur',
        'actif',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
