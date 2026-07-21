<?php

namespace App\Services;

use App\Models\CompteMobileMoneyModel;
use App\Models\ClientModel;
use App\Models\OperationModel;
use App\Models\BaremeFraisModel;
use App\Models\OperateurMobileMoneyModel;
use App\Models\PrefixeTelephoniqueModel;
use App\Models\TypeOperationModel;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use InvalidArgumentException;
use RuntimeException;

class MobileMoneyService
{
    private BaseConnection $db;
    private ClientModel $clients;
    private CompteMobileMoneyModel $comptes;
    private OperationModel $operations;
    private BaremeFraisModel $baremesFrais;
    private OperateurMobileMoneyModel $operateurs;
    private PrefixeTelephoniqueModel $prefixes;
    private TypeOperationModel $typesOperations;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
        $this->clients = new ClientModel($this->db);
        $this->comptes = new CompteMobileMoneyModel($this->db);
        $this->operations = new OperationModel($this->db);
        $this->baremesFrais = new BaremeFraisModel($this->db);
        $this->operateurs = new OperateurMobileMoneyModel($this->db);
        $this->prefixes = new PrefixeTelephoniqueModel($this->db);
        $this->typesOperations = new TypeOperationModel($this->db);
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
            ->select('comptes_mobile_money.*, clients.nom, clients.prenom, clients.email, prefixes_telephoniques.prefixe, prefixes_telephoniques.operateur, prefixes_telephoniques.id_operateur, operateurs.nom AS nom_operateur')
            ->join('clients', 'clients.id_client = comptes_mobile_money.id_client')
            ->join('prefixes_telephoniques', 'prefixes_telephoniques.id_prefixe = comptes_mobile_money.id_prefixe')
            ->join('operateurs', 'operateurs.id_operateur = prefixes_telephoniques.id_operateur', 'left')
            ->where('comptes_mobile_money.id_compte', $compte['id_compte'])
            ->get()
            ->getRowArray();
    }

    public function connecterOuCreerCompteClient(string $numeroTelephone): array
    {
        if (! preg_match('/^03[0-9]{8}$/', $numeroTelephone)) {
            throw new InvalidArgumentException('Le numéro de téléphone est invalide.');
        }

        $prefixe = $this->prefixes->findActiveForNumero($numeroTelephone);

        if ($prefixe === null) {
            throw new RuntimeException('Le préfixe de ce numéro n’est pas actif.');
        }

        $compte = $this->recupererCompteClient($numeroTelephone);

        if ($compte !== null) {
            return $compte;
        }

        $this->db->transBegin();

        try {
            $clientId = (int) $this->clients->insert([
                'nom' => 'Client',
                'prenom' => $numeroTelephone,
            ], true);

            if ($clientId <= 0) {
                throw new RuntimeException("Le client n'a pas pu être créé.");
            }

            $compteId = (int) $this->comptes->insert([
                'id_client' => $clientId,
                'id_prefixe' => (int) $prefixe['id_prefixe'],
                'numero_telephone' => $numeroTelephone,
                'solde' => 0,
                'statut' => 'actif',
            ], true);

            if ($compteId <= 0) {
                throw new RuntimeException("Le compte Mobile Money n'a pas pu être créé.");
            }

            $this->db->transCommit();
        } catch (\Throwable $exception) {
            $this->db->transRollback();

            throw $exception;
        }

        $compte = $this->recupererCompteClient($numeroTelephone);

        if ($compte === null) {
            throw new RuntimeException('Le compte créé est introuvable.');
        }

        return $compte;
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

    public function retirer(int|string $identifiant, int $montant): array
    {
        if ($montant <= 0) {
            throw new InvalidArgumentException('Le montant du retrait doit être supérieur à zéro.');
        }

        $compte = $this->recupererCompte($identifiant);

        if ($compte === null) {
            throw new RuntimeException('Compte Mobile Money introuvable.');
        }

        if ($compte['statut'] !== 'actif') {
            throw new RuntimeException('Ce compte est désactivé.');
        }

        $typeRetrait = $this->typesOperations
            ->where('code', 'retrait')
            ->where('actif', 1)
            ->first();

        if ($typeRetrait === null) {
            throw new RuntimeException("Le type d'opération retrait n'est pas actif.");
        }

        $bareme = $this->baremesFrais->findForAmount((int) $typeRetrait['id_type_operation'], $montant);
        $frais = $bareme === null ? 0 : (int) $bareme['frais'];

        if ($montant > PHP_INT_MAX - $frais) {
            throw new InvalidArgumentException('Le montant et les frais dépassent les limites du système.');
        }

        $total = $montant + $frais;
        $ancienSolde = (int) $compte['solde'];

        if ($ancienSolde < $total) {
            throw new RuntimeException('Solde insuffisant pour couvrir le montant du retrait et les frais.');
        }

        $nouveauSolde = $ancienSolde - $total;
        $dateOperation = date('Y-m-d H:i:s');

        $this->db->transBegin();

        try {
            $soldeModifie = $this->comptes->update($compte['id_compte'], ['solde' => $nouveauSolde]);

            if (! $soldeModifie) {
                throw new RuntimeException("Le solde n'a pas pu être mis à jour.");
            }

            $this->operations->insert([
                'reference' => $this->genererReference(),
                'id_type_operation' => (int) $typeRetrait['id_type_operation'],
                'id_compte_source' => (int) $compte['id_compte'],
                'montant' => $montant,
                'frais' => $frais,
                'solde_source_apres' => $nouveauSolde,
                'statut' => 'validee',
                'description' => 'Retrait client',
                'created_at' => $dateOperation,
            ], true);

            $operationId = (int) $this->operations->getInsertID();

            if ($operationId <= 0) {
                throw new RuntimeException("L'opération de retrait n'a pas pu être enregistrée.");
            }

            $this->db->transCommit();
        } catch (\Throwable $exception) {
            $this->db->transRollback();

            throw $exception;
        }

        return [
            'id_operation' => $operationId,
            'ancien_solde' => $ancienSolde,
            'montant' => $montant,
            'frais' => $frais,
            'total' => $total,
            'nouveau_solde' => $nouveauSolde,
            'date_operation' => $dateOperation,
        ];
    }

    public function recupererRetraitClient(int $operationId, int $compteId): ?array
    {
        return $this->db->table('operations')
            ->select('operations.*, types_operations.libelle AS type_operation, types_operations.code AS type_code, comptes_mobile_money.numero_telephone')
            ->join('types_operations', 'types_operations.id_type_operation = operations.id_type_operation')
            ->join('comptes_mobile_money', 'comptes_mobile_money.id_compte = operations.id_compte_source')
            ->where('operations.id_operation', $operationId)
            ->where('operations.id_compte_source', $compteId)
            ->where('types_operations.code', 'retrait')
            ->get()
            ->getRowArray();
    }

    public function calculerTransfert(int|string $expediteurId, string $numeroDestinataire, int $montant, bool $inclureFraisRetrait = false): array
    {
        if ($montant <= 0) {
            throw new InvalidArgumentException('Le montant du transfert doit être supérieur à zéro.');
        }

        if (! preg_match('/^03[0-9]{8}$/', $numeroDestinataire)) {
            throw new InvalidArgumentException('Le numéro du destinataire est invalide.');
        }

        $operateurDestination = $this->recupererOperateurParNumero($numeroDestinataire);

        if ($operateurDestination === null) {
            throw new RuntimeException('Le préfixe du destinataire n’est pas actif.');
        }

        $expediteur = $this->recupererCompte($expediteurId);

        if ($expediteur === null) {
            throw new RuntimeException('Compte expéditeur introuvable.');
        }

        if ($expediteur['statut'] !== 'actif') {
            throw new RuntimeException('Votre compte est désactivé.');
        }

        $operateurSource = $this->recupererOperateurParPrefixe((int) $expediteur['id_prefixe']);

        if ($operateurSource === null) {
            throw new RuntimeException("L'opérateur source est introuvable ou inactif.");
        }

        $destinataire = $this->recupererCompte($numeroDestinataire);

        $transfertExterne = (int) $operateurSource['id_operateur'] !== (int) $operateurDestination['id_operateur'];

        if ($destinataire === null && ! $transfertExterne) {
            throw new RuntimeException('Le compte destinataire est introuvable.');
        }

        if ($destinataire !== null && $destinataire['statut'] !== 'actif') {
            throw new RuntimeException('Le compte destinataire est désactivé.');
        }

        if ($destinataire !== null && (int) $expediteur['id_compte'] === (int) $destinataire['id_compte']) {
            throw new InvalidArgumentException('Vous ne pouvez pas transférer vers votre propre numéro.');
        }

        $typeTransfert = $this->typesOperations
            ->where('code', 'transfert')
            ->where('actif', 1)
            ->first();

        if ($typeTransfert === null) {
            throw new RuntimeException("Le type d'opération transfert n'est pas actif.");
        }

        $bareme = $this->baremesFrais->findForAmount((int) $typeTransfert['id_type_operation'], $montant);
        $fraisTransfert = $bareme === null ? 0 : (int) $bareme['frais'];

        $typeRetrait = $this->typesOperations
            ->where('code', 'retrait')
            ->where('actif', 1)
            ->first();
        $baremeRetrait = $typeRetrait === null ? null : $this->baremesFrais->findForAmount((int) $typeRetrait['id_type_operation'], $montant);
        $fraisRetraitInclus = $inclureFraisRetrait && $baremeRetrait !== null ? (int) $baremeRetrait['frais'] : 0;
        $montantRecu = $montant + $fraisRetraitInclus;
        $pourcentageCommission = $transfertExterne ? (float) $operateurDestination['commission_transfert_externe'] : 0.0;
        $commission = $transfertExterne ? (int) round($montant * ($pourcentageCommission / 100)) : 0;
        $montantReverser = $commission;

        if (
            $montant > PHP_INT_MAX - $fraisTransfert
            || $montant + $fraisTransfert > PHP_INT_MAX - $fraisRetraitInclus
            || $montant + $fraisTransfert + $fraisRetraitInclus > PHP_INT_MAX - $commission
        ) {
            throw new InvalidArgumentException('Le montant et les frais dépassent les limites du système.');
        }

        $totalDebit = $montant + $fraisTransfert + $fraisRetraitInclus + $commission;
        $ancienSoldeExpediteur = (int) $expediteur['solde'];
        $ancienSoldeDestinataire = $destinataire === null ? null : (int) $destinataire['solde'];

        if ($ancienSoldeExpediteur < $totalDebit) {
            throw new RuntimeException('Solde insuffisant pour couvrir le transfert et les frais.');
        }

        if ($destinataire !== null && $montantRecu > PHP_INT_MAX - $ancienSoldeDestinataire) {
            throw new InvalidArgumentException('Le crédit destinataire dépasse les limites du système.');
        }

        return [
            'expediteur' => $expediteur,
            'destinataire' => $destinataire,
            'numero_destinataire' => $numeroDestinataire,
            'type_transfert' => $typeTransfert,
            'operateur_source' => $operateurSource,
            'operateur_destination' => $operateurDestination,
            'transfert_externe' => $transfertExterne,
            'montant' => $montant,
            'montant_recu' => $montantRecu,
            'frais' => $fraisTransfert,
            'pourcentage_commission' => $pourcentageCommission,
            'frais_retrait_inclus' => $fraisRetraitInclus,
            'commission_interoperateur' => $commission,
            'montant_reverser' => $montantReverser,
            'total_debit' => $totalDebit,
            'ancien_solde_expediteur' => $ancienSoldeExpediteur,
            'ancien_solde_destinataire' => $ancienSoldeDestinataire,
            'nouveau_solde_expediteur' => $ancienSoldeExpediteur - $totalDebit,
            'nouveau_solde_destinataire' => $destinataire === null ? null : $ancienSoldeDestinataire + $montantRecu,
        ];
    }

    public function transferer(int|string $expediteurId, string $numeroDestinataire, int $montant, bool $inclureFraisRetrait = false): array
    {
        $calcul = $this->calculerTransfert($expediteurId, $numeroDestinataire, $montant, $inclureFraisRetrait);
        $expediteur = $calcul['expediteur'];
        $destinataire = $calcul['destinataire'];
        $typeTransfert = $calcul['type_transfert'];
        $nouveauSoldeExpediteur = $calcul['nouveau_solde_expediteur'];
        $nouveauSoldeDestinataire = $calcul['nouveau_solde_destinataire'];
        $dateOperation = date('Y-m-d H:i:s');

        $this->db->transBegin();

        try {
            $debitEffectue = $this->comptes->update($expediteur['id_compte'], ['solde' => $nouveauSoldeExpediteur]);

            if (! $debitEffectue) {
                throw new RuntimeException("Le solde de l'expéditeur n'a pas pu être mis à jour.");
            }

            if ($destinataire !== null) {
                $pourcentageEpargne = (int) ($destinataire['pourcentage_epargne'] ?? 0);
                $ancienSoldeEpargne = (int) ($destinataire['solde_epargne'] ?? 0);

                $montantEpargne = (int) round($calcul['montant_reçu']*($pourcentageEpargne /100));
                $montantDisponible = (int) $calcul['montant_reçu'] - $montantEpargne;

                $nouveauSoldeDestinataire = (int) $destinataire['solde'] + $montantDisponible;
                $nouveauSoldeEpargne = $ancienSoldeEpargne + $montantEpargne;

                $creditEffectue = $this->comptes->update($destinataire['id_compte'],[
                    'solde' => $nouveauSoldeDestinataire,
                    'solde_epargne' => $nouveauSoldeEpargne,
                ]);
                if(!$creditEffectue){
                    throw new RuntimeExeption("le solde du destinataire n'a pas pu etre mis a jour");
                }
            }

            $this->operations->insert([
                'reference' => $this->genererReference(),
                'id_type_operation' => (int) $typeTransfert['id_type_operation'],
                'id_compte_source' => (int) $expediteur['id_compte'],
                'id_compte_destination' => $destinataire === null ? null : (int) $destinataire['id_compte'],
                'id_operateur_source' => (int) $calcul['operateur_source']['id_operateur'],
                'id_operateur_destination' => (int) $calcul['operateur_destination']['id_operateur'],
                'numero_destinataire' => $numeroDestinataire,
                'montant' => $montant,
                'frais' => $calcul['frais'],
                'pourcentage_commission' => $calcul['pourcentage_commission'],
                'frais_retrait_inclus' => $calcul['frais_retrait_inclus'],
                'commission_interoperateur' => $calcul['commission_interoperateur'],
                'montant_reverser' => $calcul['montant_reverser'],
                'total_debite' => $calcul['total_debit'],
                'montant_recu' => $calcul['montant_recu'],
                'solde_source_apres' => $nouveauSoldeExpediteur,
                'solde_destination_apres' => $nouveauSoldeDestinataire,
                'statut' => 'validee',
                'description' => 'Transfert client',
                'created_at' => $dateOperation,
            ], true);

            $operationId = (int) $this->operations->getInsertID();

            if ($operationId <= 0) {
                throw new RuntimeException("L'opération de transfert n'a pas pu être enregistrée.");
            }

            $this->db->transCommit();
        } catch (\Throwable $exception) {
            $this->db->transRollback();

            throw $exception;
        }

        return [
            'id_operation' => $operationId,
            ...$calcul,
            'date_operation' => $dateOperation,
        ];
    }

    public function calculerEnvoiMultiple(int|string $expediteurId, array $numerosDestinataires, int $montantTotal, bool $inclureFraisRetrait = false): array
    {
        $numerosNettoyes = array_values(array_filter(array_map(
            static fn (string $numero): string => preg_replace('/\D+/', '', $numero) ?? '',
            $numerosDestinataires
        )));

        if ($numerosNettoyes === []) {
            throw new InvalidArgumentException('Veuillez saisir au moins un destinataire.');
        }

        if ($montantTotal <= 0) {
            throw new InvalidArgumentException('Le montant total doit être supérieur à zéro.');
        }

        if (count($numerosNettoyes) !== count(array_unique($numerosNettoyes))) {
            throw new InvalidArgumentException('Un numéro destinataire est présent plusieurs fois.');
        }

        $nombreDestinataires = count($numerosNettoyes);
        $montantBase = intdiv($montantTotal, $nombreDestinataires);
        $reste = $montantTotal % $nombreDestinataires;

        $transferts = [];
        $totalFrais = 0;
        $totalFraisRetraitInclus = 0;
        $totalDebit = 0;
        $totalCommissions = 0;
        $totalReverser = 0;

        foreach ($numerosNettoyes as $index => $numeroDestinataire) {
            $montantDestinataire = $montantBase + ($index === 0 ? $reste : 0);
            $calcul = $this->calculerTransfert($expediteurId, $numeroDestinataire, $montantDestinataire, $inclureFraisRetrait);
            $transferts[] = $calcul;
            $totalFrais += (int) $calcul['frais'];
            $totalFraisRetraitInclus += (int) $calcul['frais_retrait_inclus'];
            $totalDebit += (int) $calcul['total_debit'];
            $totalCommissions += (int) $calcul['commission_interoperateur'];
            $totalReverser += (int) $calcul['montant_reverser'];
        }

        $expediteur = $transferts[0]['expediteur'];

        if ((int) $expediteur['solde'] < $totalDebit) {
            throw new RuntimeException('Solde insuffisant pour couvrir tous les transferts et les frais.');
        }

        return [
            'id_envoi_multiple' => $this->genererReference(),
            'expediteur' => $expediteur,
            'nombre_destinataires' => $nombreDestinataires,
            'montant_total' => $montantTotal,
            'montant_par_destinataire' => $montantBase,
            'reste_distribue' => $reste,
            'total_frais' => $totalFrais,
            'total_frais_retrait_inclus' => $totalFraisRetraitInclus,
            'total_debit' => $totalDebit,
            'total_commissions' => $totalCommissions,
            'total_reverser' => $totalReverser,
            'nouveau_solde_expediteur' => (int) $expediteur['solde'] - $totalDebit,
            'transferts' => $transferts,
        ];
    }

    public function envoyerMultiple(int|string $expediteurId, array $numerosDestinataires, int $montantTotal, bool $inclureFraisRetrait = false): array
    {
        $calcul = $this->calculerEnvoiMultiple($expediteurId, $numerosDestinataires, $montantTotal, $inclureFraisRetrait);
        $expediteur = $calcul['expediteur'];
        $soldeExpediteurCourant = (int) $expediteur['solde'];
        $dateOperation = date('Y-m-d H:i:s');
        $operationIds = [];
        $idEnvoiMultiple = $calcul['id_envoi_multiple'];

        $this->db->transBegin();

        try {
            foreach ($calcul['transferts'] as $transfert) {
                $destinataire = $transfert['destinataire'];
                $soldeExpediteurCourant -= (int) $transfert['total_debit'];
                $nouveauSoldeDestinataire = null;

                if ($destinataire !== null) {
                    $pourcentageEpargne = (int) ($destinataire['pourcentage_epargne'] ?? 0);
                    $ancienSoldeEpargne = (int) ($destinataire['solde_epargne'] ?? 0);

                    $montantEpargne = (int) round($calcul['montant_reçu']*($pourcentageEpargne /100));
                    $montantDisponible = (int) $calcul['montant_reçu'] - $montantEpargne;

                    $nouveauSoldeDestinataire = (int) $destinataire['solde'] + $montantDisponible;
                    $nouveauSoldeEpargne = $ancienSoldeEpargne + $montantEpargne;

                    $creditEffectue = $this->comptes->update($destinataire['id_compte'],[
                        'solde' => $nouveauSoldeDestinataire,
                        'solde_epargne' => $nouveauSoldeEpargne,
                    ]);
                    if(!$creditEffectue){
                        throw new RuntimeExeption("le solde du destinataire n'a pas pu etre mis a jour");
                    }
                }

                $this->operations->insert([
                    'reference' => $this->genererReference(),
                    'id_type_operation' => (int) $transfert['type_transfert']['id_type_operation'],
                    'id_compte_source' => (int) $expediteur['id_compte'],
                    'id_compte_destination' => $destinataire === null ? null : (int) $destinataire['id_compte'],
                    'id_operateur_source' => (int) $transfert['operateur_source']['id_operateur'],
                    'id_operateur_destination' => (int) $transfert['operateur_destination']['id_operateur'],
                    'numero_destinataire' => $transfert['numero_destinataire'],
                    'montant' => (int) $transfert['montant'],
                    'frais' => (int) $transfert['frais'],
                    'pourcentage_commission' => (float) $transfert['pourcentage_commission'],
                    'frais_retrait_inclus' => (int) $transfert['frais_retrait_inclus'],
                    'commission_interoperateur' => (int) $transfert['commission_interoperateur'],
                    'montant_reverser' => (int) $transfert['montant_reverser'],
                    'total_debite' => (int) $transfert['total_debit'],
                    'montant_recu' => (int) $transfert['montant_recu'],
                    'id_envoi_multiple' => $idEnvoiMultiple,
                    'solde_source_apres' => $soldeExpediteurCourant,
                    'solde_destination_apres' => $nouveauSoldeDestinataire,
                    'statut' => 'validee',
                    'description' => 'Envoi multiple client',
                    'created_at' => $dateOperation,
                ], true);

                $operationId = (int) $this->operations->getInsertID();

                if ($operationId <= 0) {
                    throw new RuntimeException("Un transfert de l'envoi multiple n'a pas pu être enregistré.");
                }

                $operationIds[] = $operationId;
            }

            if (! $this->comptes->update($expediteur['id_compte'], ['solde' => $soldeExpediteurCourant])) {
                throw new RuntimeException("Le solde de l'expéditeur n'a pas pu être mis à jour.");
            }

            $this->db->transCommit();
        } catch (\Throwable $exception) {
            $this->db->transRollback();

            throw $exception;
        }

        return [
            ...$calcul,
            'id_envoi_multiple' => $idEnvoiMultiple,
            'operation_ids' => $operationIds,
            'date_operation' => $dateOperation,
        ];
    }

    public function recupererTransfertClient(int $operationId, int $compteId): ?array
    {
        return $this->db->table('operations')
            ->select('operations.*, types_operations.libelle AS type_operation, types_operations.code AS type_code, source.numero_telephone AS numero_source, COALESCE(destination.numero_telephone, operations.numero_destinataire) AS numero_destination, operateur_source.nom AS operateur_source, operateur_destination.nom AS operateur_destination')
            ->join('types_operations', 'types_operations.id_type_operation = operations.id_type_operation')
            ->join('comptes_mobile_money AS source', 'source.id_compte = operations.id_compte_source')
            ->join('comptes_mobile_money AS destination', 'destination.id_compte = operations.id_compte_destination', 'left')
            ->join('operateurs AS operateur_source', 'operateur_source.id_operateur = operations.id_operateur_source', 'left')
            ->join('operateurs AS operateur_destination', 'operateur_destination.id_operateur = operations.id_operateur_destination', 'left')
            ->where('operations.id_operation', $operationId)
            ->where('operations.id_compte_source', $compteId)
            ->where('types_operations.code', 'transfert')
            ->get()
            ->getRowArray();
    }

    public function recupererHistoriqueClient(int $compteId, int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $countBuilder = $this->db->table('operations')
            ->groupStart()
                ->where('operations.id_compte_source', $compteId)
                ->orWhere('operations.id_compte_destination', $compteId)
            ->groupEnd();

        $total = $countBuilder->countAllResults();

        $operations = $this->db->table('operations')
            ->select('operations.*, types_operations.libelle AS type_operation, types_operations.code AS type_code, source.numero_telephone AS numero_source, COALESCE(destination.numero_telephone, operations.numero_destinataire) AS numero_destination, operateur_destination.nom AS operateur_destination')
            ->join('types_operations', 'types_operations.id_type_operation = operations.id_type_operation')
            ->join('comptes_mobile_money AS source', 'source.id_compte = operations.id_compte_source', 'left')
            ->join('comptes_mobile_money AS destination', 'destination.id_compte = operations.id_compte_destination', 'left')
            ->join('operateurs AS operateur_destination', 'operateur_destination.id_operateur = operations.id_operateur_destination', 'left')
            ->groupStart()
                ->where('operations.id_compte_source', $compteId)
                ->orWhere('operations.id_compte_destination', $compteId)
            ->groupEnd()
            ->orderBy('operations.created_at', 'DESC')
            ->orderBy('operations.id_operation', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        return [
            'operations' => array_map(
                static fn (array $operation): array => self::presenterOperationHistorique($operation, $compteId),
                $operations
            ),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function recupererEvolutionSoldeClient(int $compteId): array
    {
        $operations = $this->db->table('operations')
            ->select('operations.*, types_operations.libelle AS type_operation, types_operations.code AS type_code, source.numero_telephone AS numero_source, COALESCE(destination.numero_telephone, operations.numero_destinataire) AS numero_destination, operateur_destination.nom AS operateur_destination')
            ->join('types_operations', 'types_operations.id_type_operation = operations.id_type_operation')
            ->join('comptes_mobile_money AS source', 'source.id_compte = operations.id_compte_source', 'left')
            ->join('comptes_mobile_money AS destination', 'destination.id_compte = operations.id_compte_destination', 'left')
            ->join('operateurs AS operateur_destination', 'operateur_destination.id_operateur = operations.id_operateur_destination', 'left')
            ->groupStart()
                ->where('operations.id_compte_source', $compteId)
                ->orWhere('operations.id_compte_destination', $compteId)
            ->groupEnd()
            ->orderBy('operations.created_at', 'ASC')
            ->orderBy('operations.id_operation', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(
            static function (array $operation) use ($compteId): array {
                $operation = self::presenterOperationHistorique($operation, $compteId);
                $montant = (int) $operation['montant'];
                $frais = (int) $operation['frais'];
                $typeCode = $operation['type_code'];
                $estSource = (int) ($operation['id_compte_source'] ?? 0) === $compteId;

                if ($typeCode === 'depot') {
                    $variation = $montant;
                } elseif ($typeCode === 'retrait') {
                    $variation = -($montant + $frais);
                } elseif ($typeCode === 'transfert' && $estSource) {
                    $variation = -($montant + $frais + (int) ($operation['frais_retrait_inclus'] ?? 0) + (int) ($operation['commission_interoperateur'] ?? 0));
                } elseif ($typeCode === 'transfert') {
                    $variation = (int) ($operation['montant_recu_effectif'] ?? $montant);
                } else {
                    $variation = $montant;
                }

                $soldeApres = (int) $operation['solde_apres'];
                $operation['variation'] = $variation;
                $operation['solde_avant'] = $soldeApres - $variation;
                $operation['solde_apres'] = $soldeApres;

                return $operation;
            },
            $operations
        );
    }

    private static function presenterOperationHistorique(array $operation, int $compteId): array
    {
        $estSource = (int) ($operation['id_compte_source'] ?? 0) === $compteId;
        $estDestination = (int) ($operation['id_compte_destination'] ?? 0) === $compteId;
        $typeCode = $operation['type_code'];
        $montant = (int) ($operation['montant'] ?? 0);
        $frais = (int) ($operation['frais'] ?? 0);
        $fraisRetraitInclus = (int) ($operation['frais_retrait_inclus'] ?? 0);
        $commission = (int) ($operation['commission_interoperateur'] ?? 0);
        $operation['montant_recu_effectif'] = (int) (($operation['montant_recu'] ?? 0) > 0 ? $operation['montant_recu'] : $montant + $fraisRetraitInclus);
        $operation['total_debite_effectif'] = (int) (($operation['total_debite'] ?? 0) > 0 ? $operation['total_debite'] : $montant + $frais + $fraisRetraitInclus + $commission);
        $operation['type_transfert'] = (
            ! empty($operation['id_operateur_source'])
            && ! empty($operation['id_operateur_destination'])
            && (int) $operation['id_operateur_source'] !== (int) $operation['id_operateur_destination']
        ) ? 'Externe' : 'Interne';

        if ($typeCode === 'transfert' && $estSource) {
            $operation['libelle_historique'] = ($operation['id_envoi_multiple'] ?? null) ? 'Envoi multiple envoyé' : 'Transfert envoyé';
            $operation['numero_affiche'] = $operation['numero_destination'] ?? '-';
            $operation['solde_apres'] = $operation['solde_source_apres'];

            return $operation;
        }

        if ($typeCode === 'transfert' && $estDestination) {
            $operation['libelle_historique'] = ($operation['id_envoi_multiple'] ?? null) ? 'Envoi multiple reçu' : 'Transfert reçu';
            $operation['numero_affiche'] = $operation['numero_source'] ?? '-';
            $operation['solde_apres'] = $operation['solde_destination_apres'];

            return $operation;
        }

        if ($typeCode === 'depot') {
            $operation['libelle_historique'] = $operation['type_operation'];
            $operation['numero_affiche'] = $operation['numero_destination'] ?? '-';
            $operation['solde_apres'] = $operation['solde_destination_apres'];

            return $operation;
        }

        $operation['libelle_historique'] = $operation['type_operation'];
        $operation['numero_affiche'] = $operation['numero_source'] ?? '-';
        $operation['solde_apres'] = $operation['solde_source_apres'];

        return $operation;
    }

    private function recupererOperateurParNumero(string $numeroTelephone): ?array
    {
        return $this->db->table('prefixes_telephoniques')
            ->select('operateurs.*, prefixes_telephoniques.id_prefixe, prefixes_telephoniques.prefixe')
            ->join('operateurs', 'operateurs.id_operateur = prefixes_telephoniques.id_operateur')
            ->where('prefixes_telephoniques.actif', 1)
            ->where('operateurs.actif', 1)
            ->where("'" . $this->db->escapeLikeString($numeroTelephone) . "' LIKE prefixes_telephoniques.prefixe || '%'", null, false)
            ->orderBy('length(prefixes_telephoniques.prefixe)', 'DESC', false)
            ->get()
            ->getRowArray();
    }

    private function recupererOperateurParPrefixe(int $prefixeId): ?array
    {
        return $this->db->table('prefixes_telephoniques')
            ->select('operateurs.*, prefixes_telephoniques.id_prefixe, prefixes_telephoniques.prefixe')
            ->join('operateurs', 'operateurs.id_operateur = prefixes_telephoniques.id_operateur')
            ->where('prefixes_telephoniques.id_prefixe', $prefixeId)
            ->where('prefixes_telephoniques.actif', 1)
            ->where('operateurs.actif', 1)
            ->get()
            ->getRowArray();
    }

    private function genererReference(): string
    {
        return 'MM' . date('YmdHis') . strtoupper(bin2hex(random_bytes(3)));
    }
}
