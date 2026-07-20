<?php

namespace App\Models;

use CodeIgniter\Model;

class PrefixeTelephoniqueModel extends Model
{
    protected $table = 'prefixes_telephoniques';
    protected $primaryKey = 'id_prefixe';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id_operateur',
        'prefixe',
        'operateur',
        'actif',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findActiveForNumero(string $numeroTelephone): ?array
    {
        $prefixes = $this->select('prefixes_telephoniques.*, operateurs.nom AS nom_operateur, operateurs.actif AS operateur_actif, operateurs.commission_transfert_externe')
            ->join('operateurs', 'operateurs.id_operateur = prefixes_telephoniques.id_operateur')
            ->where('prefixes_telephoniques.actif', 1)
            ->where('operateurs.actif', 1)
            ->orderBy('prefixes_telephoniques.prefixe', 'DESC')
            ->findAll();

        foreach ($prefixes as $prefixe) {
            if (str_starts_with($numeroTelephone, $prefixe['prefixe'])) {
                return $prefixe;
            }
        }

        return null;
    }
}
