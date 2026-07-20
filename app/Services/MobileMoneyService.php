<?php

namespace App\Services;

use App\Models\CompteMobileMoneyModel;
use App\Models\OperationModel;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use InvalidArgumentException;
use RuntimeException;

class MobileMoneyService
{
    private BaseConnection $db;
    private CompteMobileMoneyModel $comptes;
    private OperationModel $operations;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
        $this->comptes = new CompteMobileMoneyModel();
        $this->operations = new OperationModel();
    }

    public function recupererCompte(int|string $identifiant): ?array
    {
        if (is_int($identifiant) || ctype_digit($identifiant)) {
            $compte = $this->comptes->find((int) $identifiant);

            if ($compte !== null) {
                return $compte;
            }
        }

        return $this->comptes->findByNumero((string) $identifiant);
    }

    public function consulterSolde(int|string $identifiant): int
    {
        $compte = $this->recupererCompte($identifiant);

        if ($compte === null) {
            throw new RuntimeException('Compte Mobile Money introuvable.');
        }

        return (int) $compte['solde'];
    }

    public function modifierSolde(int|string $identifiant, int $nouveauSolde): bool
    {
        if ($nouveauSolde < 0) {
            throw new InvalidArgumentException('Le solde ne peut pas être négatif.');
        }

        $compte = $this->recupererCompte($identifiant);

        if ($compte === null) {
            throw new RuntimeException('Compte Mobile Money introuvable.');
        }

        return $this->comptes->update($compte['id_compte'], ['solde' => $nouveauSolde]);
    }

    public function enregistrerOperation(array $donnees): int
    {
        $donnees['reference'] ??= $this->genererReference();
        $donnees['frais'] ??= 0;
        $donnees['statut'] ??= 'validee';
        $donnees['created_at'] ??= date('Y-m-d H:i:s');

        $this->db->transStart();
        $this->operations->insert($donnees, true);
        $operationId = (int) $this->operations->getInsertID();
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException("L'opération n'a pas pu être enregistrée.");
        }

        return $operationId;
    }

    private function genererReference(): string
    {
        return 'MM' . date('YmdHis') . strtoupper(bin2hex(random_bytes(3)));
    }
}
