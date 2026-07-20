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

    public function findActiveForNumero(string $numeroTelephone): ?array
    {
        foreach ($this->where('actif', 1)->orderBy('prefixe', 'DESC')->findAll() as $prefixe) {
            if (str_starts_with($numeroTelephone, $prefixe['prefixe'])) {
                return $prefixe;
            }
        }

        return null;
    }
}
