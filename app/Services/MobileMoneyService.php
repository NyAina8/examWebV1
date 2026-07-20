<?php

namespace App\Services;

use App\Models\CompteMobileMoneyModel;
use App\Models\OperationModel;
use App\Models\TypeOperationModel;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use InvalidArgumentException;
use RuntimeException;

class MobileMoneyService
{
    private BaseConnection $db;
    private CompteMobileMoneyModel $comptes;
    private OperationModel $operations;
    private TypeOperationModel $typesOperations;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
        $this->comptes = new CompteMobileMoneyModel();
        $this->operations = new OperationModel();
        $this->typesOperations = new TypeOperationModel();
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

    public function recupererCompteClient(int|string $identifiant): ?array
    {
        $compte = $this->recupererCompte($identifiant);

        if ($compte === null) {
            return null;
        }

        return $this->db->table('comptes_mobile_money')
            ->select('comptes_mobile_money.*, clients.nom, clients.prenom, clients.email, prefixes_telephoniques.prefixe, prefixes_telephoniques.operateur')
            ->join('clients', 'clients.id_client = comptes_mobile_money.id_client')
            ->join('prefixes_telephoniques', 'prefixes_telephoniques.id_prefixe = comptes_mobile_money.id_prefixe')
            ->where('comptes_mobile_money.id_compte', $compte['id_compte'])
            ->get()
            ->getRowArray();
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

    public function deposer(int|string $identifiant, int $montant): array
    {
        if ($montant <= 0) {
            throw new InvalidArgumentException('Le montant du dépôt doit être supérieur à zéro.');
        }

        $compte = $this->recupererCompte($identifiant);

        if ($compte === null) {
            throw new RuntimeException('Compte Mobile Money introuvable.');
        }

        if ($compte['statut'] !== 'actif') {
            throw new RuntimeException('Ce compte est désactivé.');
        }

        $typeDepot = $this->typesOperations
            ->where('code', 'depot')
            ->where('actif', 1)
            ->first();

        if ($typeDepot === null) {
            throw new RuntimeException("Le type d'opération dépôt n'est pas actif.");
        }

        $ancienSolde = (int) $compte['solde'];

        if ($montant > PHP_INT_MAX - $ancienSolde) {
            throw new InvalidArgumentException('Le montant dépasse les limites du système.');
        }

        $nouveauSolde = $ancienSolde + $montant;

        $this->db->transBegin();

        try {
            $soldeModifie = $this->comptes->update($compte['id_compte'], ['solde' => $nouveauSolde]);

            if (! $soldeModifie) {
                throw new RuntimeException("Le solde n'a pas pu être mis à jour.");
            }

            $this->operations->insert([
                'reference' => $this->genererReference(),
                'id_type_operation' => (int) $typeDepot['id_type_operation'],
                'id_compte_destination' => (int) $compte['id_compte'],
                'montant' => $montant,
                'frais' => 0,
                'solde_destination_apres' => $nouveauSolde,
                'statut' => 'validee',
                'description' => 'Dépôt client',
                'created_at' => date('Y-m-d H:i:s'),
            ], true);

            $operationId = (int) $this->operations->getInsertID();

            if ($operationId <= 0) {
                throw new RuntimeException("L'opération de dépôt n'a pas pu être enregistrée.");
            }

            $this->db->transCommit();
        } catch (\Throwable $exception) {
            $this->db->transRollback();

            throw $exception;
        }

        return [
            'id_operation' => $operationId,
            'ancien_solde' => $ancienSolde,
            'nouveau_solde' => $nouveauSolde,
        ];
    }

    private function genererReference(): string
    {
        return 'MM' . date('YmdHis') . strtoupper(bin2hex(random_bytes(3)));
    }
}
