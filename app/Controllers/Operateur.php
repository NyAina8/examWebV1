<?php

namespace App\Controllers;

use App\Models\BaremeFraisModel;
use App\Models\CompteMobileMoneyModel;
use App\Models\OperateurMobileMoneyModel;
use App\Models\PrefixeTelephoniqueModel;
use App\Models\TypeOperationModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Operateur extends BaseController
{
    private PrefixeTelephoniqueModel $prefixes;
    private TypeOperationModel $types;
    private BaremeFraisModel $baremes;
    private CompteMobileMoneyModel $comptes;
    private OperateurMobileMoneyModel $operateurs;

    public function __construct()
    {
        $this->prefixes = new PrefixeTelephoniqueModel();
        $this->types = new TypeOperationModel();
        $this->baremes = new BaremeFraisModel();
        $this->comptes = new CompteMobileMoneyModel();
        $this->operateurs = new OperateurMobileMoneyModel();
    }

    public function login()
    {
        return redirect()->to('/connexion');
    }

    public function logout()
    {
        session()->remove(['admin_connecte', 'admin_numero']);

        return redirect()->to('/connexion')->with('success', 'Vous êtes déconnecté de l’espace admin.');
    }

    public function index()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        return view('operateur/dashboard', [
            'clientsCount' => $this->comptes->countAllResults(),
            'depotsCount' => $this->nombreOperationsParType('depot'),
            'retraitsCount' => $this->nombreOperationsParType('retrait'),
            'transfertsCount' => $this->nombreOperationsParType('transfert'),
        ]);
    }

    public function prefixes()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        return view('operateur/prefixes/index', [
            'prefixes' => $this->prefixes
                ->select('prefixes_telephoniques.*, operateurs.nom AS nom_operateur')
                ->join('operateurs', 'operateurs.id_operateur = prefixes_telephoniques.id_operateur')
                ->orderBy('operateurs.nom', 'ASC')
                ->orderBy('prefixes_telephoniques.prefixe', 'ASC')
                ->findAll(),
        ]);
    }

    public function newPrefixe()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        return view('operateur/prefixes/form', [
            'prefixe' => null,
            'operateurs' => $this->operateurs->orderBy('nom', 'ASC')->findAll(),
            'errors' => session('errors') ?? [],
        ]);
    }

    public function createPrefixe()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        $data = $this->prefixeData();

        if (! $this->validatePrefixe($data)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->prefixes->insert($data);
        } catch (DatabaseException $exception) {
            return redirect()->back()->withInput()->with('error', 'Ce préfixe existe déjà ou ne respecte pas le format attendu.');
        }

        return redirect()->to('/operateur/prefixes')->with('success', 'Préfixe ajouté avec succès.');
    }

    public function editPrefixe(int $id)
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        $prefixe = $this->prefixes->find($id);

        if ($prefixe === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Préfixe introuvable.');
        }

        return view('operateur/prefixes/form', [
            'prefixe' => $prefixe,
            'operateurs' => $this->operateurs->orderBy('nom', 'ASC')->findAll(),
            'errors' => session('errors') ?? [],
        ]);
    }

    public function updatePrefixe(int $id)
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        if ($this->prefixes->find($id) === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Préfixe introuvable.');
        }

        $data = $this->prefixeData();

        if (! $this->validatePrefixe($data, $id)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->prefixes->update($id, $data);
        } catch (DatabaseException $exception) {
            return redirect()->back()->withInput()->with('error', 'Modification impossible : préfixe dupliqué ou données invalides.');
        }

        return redirect()->to('/operateur/prefixes')->with('success', 'Préfixe modifié avec succès.');
    }

    public function deletePrefixe(int $id)
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        try {
            $this->prefixes->delete($id);
        } catch (DatabaseException $exception) {
            return redirect()->to('/operateur/prefixes')->with('error', 'Suppression impossible : ce préfixe est utilisé par un compte.');
        }

        return redirect()->to('/operateur/prefixes')->with('success', 'Préfixe supprimé.');
    }

    public function types()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        return view('operateur/types/index', [
            'types' => $this->types->orderBy('id_type_operation', 'ASC')->findAll(),
        ]);
    }

    public function toggleType(int $id)
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        $type = $this->types->find($id);

        if ($type === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Type d’opération introuvable.');
        }

        $this->types->update($id, ['actif' => (int) ! (bool) $type['actif']]);

        return redirect()->to('/operateur/types')->with('success', 'Statut du type d’opération mis à jour.');
    }

    public function baremes()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        return view('operateur/baremes/index', [
            'baremes' => $this->baremes
                ->select('baremes_frais.*, types_operations.code, types_operations.libelle')
                ->join('types_operations', 'types_operations.id_type_operation = baremes_frais.id_type_operation')
                ->orderBy('types_operations.id_type_operation', 'ASC')
                ->orderBy('baremes_frais.montant_min', 'ASC')
                ->findAll(),
        ]);
    }

    public function newBareme()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        return view('operateur/baremes/form', [
            'bareme' => null,
            'types' => $this->types->orderBy('id_type_operation', 'ASC')->findAll(),
            'errors' => session('errors') ?? [],
        ]);
    }

    public function createBareme()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        $data = $this->baremeData();
        $errors = $this->validateBaremeData($data);

        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        try {
            $this->baremes->insert($data);
        } catch (DatabaseException $exception) {
            return redirect()->back()->withInput()->with('error', 'Ajout impossible : ce barème existe déjà ou ne respecte pas les contraintes.');
        }

        return redirect()->to('/operateur/baremes')->with('success', 'Barème ajouté avec succès.');
    }

    public function editBareme(int $id)
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        $bareme = $this->baremes->find($id);

        if ($bareme === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Barème introuvable.');
        }

        return view('operateur/baremes/form', [
            'bareme' => $bareme,
            'types' => $this->types->orderBy('id_type_operation', 'ASC')->findAll(),
            'errors' => session('errors') ?? [],
        ]);
    }

    public function updateBareme(int $id)
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        if ($this->baremes->find($id) === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Barème introuvable.');
        }

        $data = $this->baremeData();
        $errors = $this->validateBaremeData($data, $id);

        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        try {
            $this->baremes->update($id, $data);
        } catch (DatabaseException $exception) {
            return redirect()->back()->withInput()->with('error', 'Modification impossible : barème dupliqué ou données invalides.');
        }

        return redirect()->to('/operateur/baremes')->with('success', 'Barème modifié avec succès.');
    }

    public function deleteBareme(int $id)
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        $this->baremes->delete($id);

        return redirect()->to('/operateur/baremes')->with('success', 'Barème supprimé.');
    }

    public function comptes()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        return view('operateur/comptes/index', [
            'comptes' => $this->comptes
                ->select('comptes_mobile_money.*, clients.nom, clients.prenom, prefixes_telephoniques.operateur')
                ->join('clients', 'clients.id_client = comptes_mobile_money.id_client')
                ->join('prefixes_telephoniques', 'prefixes_telephoniques.id_prefixe = comptes_mobile_money.id_prefixe')
                ->orderBy('comptes_mobile_money.numero_telephone', 'ASC')
                ->findAll(),
        ]);
    }

    public function transferts()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        $transferts = db_connect()->table('operations')
            ->select('operations.*, source.numero_telephone AS numero_source, COALESCE(destination.numero_telephone, operations.numero_destinataire) AS numero_destination, source_client.nom AS nom_source, source_client.prenom AS prenom_source, destination_client.nom AS nom_destination, destination_client.prenom AS prenom_destination, operateur_destination.nom AS nom_operateur_destination')
            ->join('types_operations', 'types_operations.id_type_operation = operations.id_type_operation')
            ->join('comptes_mobile_money AS source', 'source.id_compte = operations.id_compte_source')
            ->join('clients AS source_client', 'source_client.id_client = source.id_client')
            ->join('comptes_mobile_money AS destination', 'destination.id_compte = operations.id_compte_destination', 'left')
            ->join('clients AS destination_client', 'destination_client.id_client = destination.id_client', 'left')
            ->join('operateurs AS operateur_destination', 'operateur_destination.id_operateur = operations.id_operateur_destination', 'left')
            ->where('types_operations.code', 'transfert')
            ->orderBy('operations.created_at', 'DESC')
            ->orderBy('operations.id_operation', 'DESC')
            ->get()
            ->getResultArray();

        return view('operateur/transferts/index', [
            'transferts' => $transferts,
        ]);
    }

    public function retraits()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        $retraits = db_connect()->table('operations')
            ->select('operations.*, comptes_mobile_money.numero_telephone, clients.nom, clients.prenom')
            ->join('types_operations', 'types_operations.id_type_operation = operations.id_type_operation')
            ->join('comptes_mobile_money', 'comptes_mobile_money.id_compte = operations.id_compte_source')
            ->join('clients', 'clients.id_client = comptes_mobile_money.id_client')
            ->where('types_operations.code', 'retrait')
            ->orderBy('operations.created_at', 'DESC')
            ->orderBy('operations.id_operation', 'DESC')
            ->get()
            ->getResultArray();

        return view('operateur/retraits/index', [
            'retraits' => $retraits,
        ]);
    }

    public function depots()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        $depots = db_connect()->table('operations')
            ->select('operations.*, comptes_mobile_money.numero_telephone, clients.nom, clients.prenom')
            ->join('types_operations', 'types_operations.id_type_operation = operations.id_type_operation')
            ->join('comptes_mobile_money', 'comptes_mobile_money.id_compte = operations.id_compte_destination')
            ->join('clients', 'clients.id_client = comptes_mobile_money.id_client')
            ->where('types_operations.code', 'depot')
            ->orderBy('operations.created_at', 'DESC')
            ->orderBy('operations.id_operation', 'DESC')
            ->get()
            ->getResultArray();

        return view('operateur/depots/index', [
            'depots' => $depots,
        ]);
    }

    public function gains()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        $situation = $this->calculerSituationGains();
        $operateurs = $situation['operateurs'];
        $operateurId = (int) ($this->request->getGet('operateur_id') ?? 0);
        $operateurSelectionne = null;

        if ($operateurId > 0) {
            foreach ($operateurs as $operateur) {
                if ((int) $operateur['id_operateur'] === $operateurId) {
                    $operateurSelectionne = $operateur;

                    break;
                }
            }
        }

        return view('operateur/gains/index', [
            ...$situation,
            'operateursListe' => $operateurs,
            'operateurs' => $operateurSelectionne === null ? $operateurs : [$operateurSelectionne],
            'operateurSelectionne' => $operateurSelectionne,
            'operateurSelectionneId' => $operateurSelectionne === null ? 0 : $operateurId,
        ]);
    }

    public function operateursExternes()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        $operateurs = db_connect()->table('operateurs')
            ->select('operateurs.*, GROUP_CONCAT(prefixes_telephoniques.prefixe, ", ") AS prefixes')
            ->join('prefixes_telephoniques', 'prefixes_telephoniques.id_operateur = operateurs.id_operateur', 'left')
            ->groupBy('operateurs.id_operateur')
            ->orderBy('operateurs.principal', 'DESC')
            ->orderBy('operateurs.nom', 'ASC')
            ->get()
            ->getResultArray();

        return view('operateur/operateurs_externes/index', [
            'operateurs' => $operateurs,
        ]);
    }

    public function newOperateurExterne()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        return view('operateur/operateurs_externes/form', [
            'operateur' => null,
            'prefixes' => '',
            'errors' => session('errors') ?? [],
        ]);
    }

    public function createOperateurExterne()
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        $data = $this->operateurData();
        $prefixes = $this->normaliserPrefixes((string) $this->request->getPost('prefixes'));
        $errors = $this->validateOperateurData($data, $prefixes);

        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $this->operateurs->insert($data);
            $operateurId = (int) $this->operateurs->getInsertID();
            $this->enregistrerPrefixesOperateur($operateurId, $data['nom'], $prefixes);
            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('error', "L'opérateur n'a pas pu être ajouté.");
        }

        return redirect()->to('/operateur/operateurs-externes')->with('success', 'Opérateur ajouté avec succès.');
    }

    public function editOperateurExterne(int $id)
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        $operateur = $this->operateurs->find($id);

        if ($operateur === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Opérateur introuvable.');
        }

        $prefixes = implode("\n", array_column($this->prefixes->where('id_operateur', $id)->orderBy('prefixe', 'ASC')->findAll(), 'prefixe'));

        return view('operateur/operateurs_externes/form', [
            'operateur' => $operateur,
            'prefixes' => $prefixes,
            'errors' => session('errors') ?? [],
        ]);
    }

    public function updateOperateurExterne(int $id)
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        if ($this->operateurs->find($id) === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Opérateur introuvable.');
        }

        $data = $this->operateurData();
        $prefixes = $this->normaliserPrefixes((string) $this->request->getPost('prefixes'));
        $errors = $this->validateOperateurData($data, $prefixes, $id);

        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $this->operateurs->update($id, $data);
            $this->enregistrerPrefixesOperateur($id, $data['nom'], $prefixes);
            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('error', "L'opérateur n'a pas pu être modifié.");
        }

        return redirect()->to('/operateur/operateurs-externes')->with('success', 'Opérateur modifié avec succès.');
    }

    public function toggleOperateurExterne(int $id)
    {
        $redirect = $this->exigerAdmin();

        if ($redirect !== null) {
            return $redirect;
        }

        $operateur = $this->operateurs->find($id);

        if ($operateur === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Opérateur introuvable.');
        }

        $this->operateurs->update($id, ['actif' => (int) ! (bool) $operateur['actif']]);

        return redirect()->to('/operateur/operateurs-externes')->with('success', 'Statut de l’opérateur mis à jour.');
    }


    private function prefixeData(): array
    {
        $operateurId = (int) $this->request->getPost('id_operateur');
        $operateur = $this->operateurs->find($operateurId);

        return [
            'id_operateur' => $operateurId,
            'prefixe' => trim((string) $this->request->getPost('prefixe')),
            'operateur' => $operateur['nom'] ?? '',
            'actif' => (int) ($this->request->getPost('actif') === '1'),
        ];
    }

    private function operateurData(): array
    {
        return [
            'code' => strtoupper(trim((string) $this->request->getPost('code'))),
            'nom' => trim((string) $this->request->getPost('nom')),
            'principal' => (int) ($this->request->getPost('principal') === '1'),
            'commission_transfert_externe' => (float) str_replace(',', '.', (string) $this->request->getPost('commission_transfert_externe')),
            'actif' => (int) ($this->request->getPost('actif') === '1'),
        ];
    }

    private function validateOperateurData(array $data, array $prefixes, ?int $ignoredId = null): array
    {
        $errors = [];

        if ($data['nom'] === '') {
            $errors['nom'] = 'Le nom est obligatoire.';
        }

        if ($data['code'] === '') {
            $errors['code'] = 'Le code est obligatoire.';
        } elseif (! preg_match('/^[A-Z0-9_-]+$/', $data['code'])) {
            $errors['code'] = 'Le code doit contenir uniquement lettres, chiffres, tirets ou soulignés.';
        } else {
            $query = $this->operateurs->where('code', $data['code']);

            if ($ignoredId !== null) {
                $query->where('id_operateur !=', $ignoredId);
            }

            if ($query->first() !== null) {
                $errors['code'] = 'Ce code est déjà utilisé.';
            }
        }

        if ($data['commission_transfert_externe'] < 0 || $data['commission_transfert_externe'] > 100) {
            $errors['commission_transfert_externe'] = 'Le pourcentage de commission doit être compris entre 0 et 100.';
        }

        if (count($prefixes) !== count(array_unique($prefixes))) {
            $errors['prefixes'] = 'Les préfixes doivent être uniques.';
        }

        foreach ($prefixes as $prefixe) {
            if (! preg_match('/^03[0-9]$/', $prefixe)) {
                $errors['prefixes'] = 'Chaque préfixe doit avoir le format 03x.';

                break;
            }

            $prefixeExistant = $this->prefixes->where('prefixe', $prefixe)->first();

            if ($prefixeExistant !== null && ! empty($prefixeExistant['id_operateur']) && (int) $prefixeExistant['id_operateur'] !== (int) ($ignoredId ?? 0)) {
                $errors['prefixes'] = 'Le préfixe ' . $prefixe . ' appartient déjà à un autre opérateur.';

                break;
            }
        }

        return $errors;
    }

    private function normaliserPrefixes(string $prefixes): array
    {
        return array_values(array_filter(array_map(
            static fn (string $prefixe): string => trim($prefixe),
            preg_split('/[\s,;]+/', $prefixes) ?: []
        )));
    }

    private function enregistrerPrefixesOperateur(int $operateurId, string $nomOperateur, array $prefixes): void
    {
        foreach ($prefixes as $prefixe) {
            $prefixeExistant = $this->prefixes->where('prefixe', $prefixe)->first();
            $data = [
                'id_operateur' => $operateurId,
                'prefixe' => $prefixe,
                'operateur' => $nomOperateur,
                'actif' => 1,
            ];

            if ($prefixeExistant === null) {
                $this->prefixes->insert($data);
            } else {
                $this->prefixes->update($prefixeExistant['id_prefixe'], $data);
            }
        }
    }

    private function validatePrefixe(array $data, ?int $id = null): bool
    {
        $uniqueRule = 'is_unique[prefixes_telephoniques.prefixe]';

        if ($id !== null) {
            $uniqueRule = "is_unique[prefixes_telephoniques.prefixe,id_prefixe,{$id}]";
        }

        return $this->validate([
            'prefixe' => [
                'label' => 'Préfixe',
                'rules' => "required|regex_match[/^03[0-9]$/]|{$uniqueRule}",
            ],
            'id_operateur' => [
                'label' => 'Opérateur',
                'rules' => 'required|is_natural_no_zero|is_not_unique[operateurs.id_operateur]',
            ],
        ]);
    }

    private function baremeData(): array
    {
        $montantMax = trim((string) $this->request->getPost('montant_max'));

        return [
            'id_type_operation' => (int) $this->request->getPost('id_type_operation'),
            'montant_min' => (int) $this->request->getPost('montant_min'),
            'montant_max' => $montantMax === '' ? null : (int) $montantMax,
            'frais' => (int) $this->request->getPost('frais'),
            'actif' => (int) ($this->request->getPost('actif') === '1'),
        ];
    }

    private function validateBaremeData(array $data, ?int $ignoredId = null): array
    {
        $errors = [];

        if ($this->types->find($data['id_type_operation']) === null) {
            $errors['id_type_operation'] = 'Le type d’opération sélectionné est invalide.';
        }

        if ($data['montant_min'] < 0) {
            $errors['montant_min'] = 'Le montant minimum doit être positif.';
        }

        if ($data['montant_max'] !== null && $data['montant_min'] >= $data['montant_max']) {
            $errors['montant_max'] = 'Le montant minimum doit être inférieur au montant maximum.';
        }

        if ($data['frais'] < 0) {
            $errors['frais'] = 'Les frais doivent être positifs.';
        }

        if ($errors === [] && $this->hasOverlappingBareme($data, $ignoredId)) {
            $errors['montant_min'] = 'Cette tranche chevauche déjà une tranche du même type d’opération.';
        }

        return $errors;
    }

    private function hasOverlappingBareme(array $data, ?int $ignoredId = null): bool
    {
        $newMin = $data['montant_min'];
        $newMax = $data['montant_max'];
        $query = $this->baremes->where('id_type_operation', $data['id_type_operation']);

        if ($ignoredId !== null) {
            $query->where('id_bareme !=', $ignoredId);
        }

        foreach ($query->findAll() as $bareme) {
            $existingMin = (int) $bareme['montant_min'];
            $existingMax = $bareme['montant_max'] === null ? null : (int) $bareme['montant_max'];

            if (($newMax === null || $existingMin <= $newMax) && ($existingMax === null || $newMin <= $existingMax)) {
                return true;
            }
        }

        return false;
    }

    private function totalFraisParType(string $code): int
    {
        $result = db_connect()->table('operations')
            ->selectSum('operations.frais', 'total_frais')
            ->join('types_operations', 'types_operations.id_type_operation = operations.id_type_operation')
            ->where('types_operations.code', $code)
            ->where('operations.statut', 'validee')
            ->get()
            ->getRowArray();

        return (int) ($result['total_frais'] ?? 0);
    }

    private function calculerSituationGains(): array
    {
        $fraisRetrait = $this->totalFraisParType('retrait');
        $fraisTransfert = $this->totalFraisParType('transfert');
        $fraisTransfertInterne = $this->totalFraisTransfertInterne();
        $commissionsExternes = $this->totalColonneTransfertExterne('commission_interoperateur');
        $nombreTransfertsInternes = $this->nombreTransfertsParPortee(false);
        $nombreTransfertsExternes = $this->nombreTransfertsParPortee(true);
        $montantsTransferes = $this->totalMontantsTransferts();

        return [
            'gainsInternes' => $fraisRetrait + $fraisTransfertInterne,
            'gainRetrait' => $fraisRetrait,
            'gainTransfert' => $fraisTransfert,
            'fraisTransfertInterne' => $fraisTransfertInterne,
            'nombreTransfertsInternes' => $nombreTransfertsInternes,
            'nombreTransfertsExternes' => $nombreTransfertsExternes,
            'montantsTransferes' => $montantsTransferes,
            'commissionsExternes' => $commissionsExternes,
            'gainTotal' => $fraisRetrait + $fraisTransfert + $commissionsExternes,
            'operateurs' => $this->gainsParOperateur(),
        ];
    }

    private function totalFraisTransfertInterne(): int
    {
        $result = db_connect()->table('operations')
            ->selectSum('operations.frais', 'total')
            ->join('types_operations', 'types_operations.id_type_operation = operations.id_type_operation')
            ->where('types_operations.code', 'transfert')
            ->where('operations.statut', 'validee')
            ->where('operations.id_operateur_source = operations.id_operateur_destination', null, false)
            ->get()
            ->getRowArray();

        return (int) ($result['total'] ?? 0);
    }

    private function nombreTransfertsParPortee(bool $externe): int
    {
        $builder = db_connect()->table('operations')
            ->join('types_operations', 'types_operations.id_type_operation = operations.id_type_operation')
            ->where('types_operations.code', 'transfert')
            ->where('operations.statut', 'validee');

        if ($externe) {
            $builder->where('operations.id_operateur_source != operations.id_operateur_destination', null, false);
        } else {
            $builder->where('operations.id_operateur_source = operations.id_operateur_destination', null, false);
        }

        return $builder->countAllResults();
    }

    private function totalMontantsTransferts(): int
    {
        $result = db_connect()->table('operations')
            ->selectSum('operations.montant', 'total')
            ->join('types_operations', 'types_operations.id_type_operation = operations.id_type_operation')
            ->where('types_operations.code', 'transfert')
            ->where('operations.statut', 'validee')
            ->get()
            ->getRowArray();

        return (int) ($result['total'] ?? 0);
    }

    private function totalColonneTransfertExterne(string $colonne): int
    {
        $result = db_connect()->table('operations')
            ->selectSum('operations.' . $colonne, 'total')
            ->join('types_operations', 'types_operations.id_type_operation = operations.id_type_operation')
            ->where('types_operations.code', 'transfert')
            ->where('operations.statut', 'validee')
            ->where('operations.id_operateur_source != operations.id_operateur_destination', null, false)
            ->get()
            ->getRowArray();

        return (int) ($result['total'] ?? 0);
    }

    private function gainsParOperateur(): array
    {
        $operateurs = db_connect()->table('operateurs')
            ->select('operateurs.id_operateur, operateurs.nom, operateurs.commission_transfert_externe, (SELECT GROUP_CONCAT(prefixe, ", ") FROM prefixes_telephoniques WHERE prefixes_telephoniques.id_operateur = operateurs.id_operateur) AS prefixes')
            ->select('(SELECT COUNT(*) FROM operations JOIN types_operations ON types_operations.id_type_operation = operations.id_type_operation WHERE types_operations.code = "transfert" AND operations.statut = "validee" AND operations.id_operateur_source = operateurs.id_operateur) AS transferts_envoyes', false)
            ->select('(SELECT COUNT(*) FROM operations JOIN types_operations ON types_operations.id_type_operation = operations.id_type_operation WHERE types_operations.code = "transfert" AND operations.statut = "validee" AND operations.id_operateur_destination = operateurs.id_operateur AND operations.id_operateur_source != operations.id_operateur_destination) AS transferts_recus_externes', false)
            ->select('(SELECT COALESCE(SUM(operations.montant), 0) FROM operations JOIN types_operations ON types_operations.id_type_operation = operations.id_type_operation WHERE types_operations.code = "transfert" AND operations.statut = "validee" AND operations.id_operateur_source = operateurs.id_operateur) AS montant_envoye', false)
            ->select('(SELECT COALESCE(SUM(CASE WHEN operations.montant_recu > 0 THEN operations.montant_recu ELSE operations.montant END), 0) FROM operations JOIN types_operations ON types_operations.id_type_operation = operations.id_type_operation WHERE types_operations.code = "transfert" AND operations.statut = "validee" AND operations.id_operateur_destination = operateurs.id_operateur AND operations.id_operateur_source != operations.id_operateur_destination) AS montant_recu_externe', false)
            ->select('(SELECT COALESCE(SUM(operations.frais), 0) FROM operations JOIN types_operations ON types_operations.id_type_operation = operations.id_type_operation WHERE types_operations.code = "transfert" AND operations.statut = "validee" AND operations.id_operateur_source = operateurs.id_operateur) AS frais_transfert_gagnes', false)
            ->select('(SELECT COALESCE(SUM(operations.frais), 0) FROM operations JOIN types_operations ON types_operations.id_type_operation = operations.id_type_operation JOIN comptes_mobile_money ON comptes_mobile_money.id_compte = operations.id_compte_source JOIN prefixes_telephoniques ON prefixes_telephoniques.id_prefixe = comptes_mobile_money.id_prefixe WHERE types_operations.code = "retrait" AND operations.statut = "validee" AND prefixes_telephoniques.id_operateur = operateurs.id_operateur) AS frais_retrait_gagnes', false)
            ->select('(SELECT COALESCE(SUM(operations.commission_interoperateur), 0) FROM operations JOIN types_operations ON types_operations.id_type_operation = operations.id_type_operation WHERE types_operations.code = "transfert" AND operations.statut = "validee" AND operations.id_operateur_destination = operateurs.id_operateur AND operations.id_operateur_source != operations.id_operateur_destination) AS commissions_gagnees', false)
            ->where('operateurs.actif', 1)
            ->orderBy('operateurs.principal', 'DESC')
            ->orderBy('operateurs.nom', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static function (array $operateur): array {
            $operateur['gain_total'] = (int) $operateur['frais_transfert_gagnes']
                + (int) $operateur['frais_retrait_gagnes']
                + (int) $operateur['commissions_gagnees'];

            return $operateur;
        }, $operateurs);
    }

    private function nombreOperationsParType(string $code): int
    {
        return db_connect()->table('operations')
            ->join('types_operations', 'types_operations.id_type_operation = operations.id_type_operation')
            ->where('types_operations.code', $code)
            ->where('operations.statut', 'validee')
            ->countAllResults();
    }

    private function exigerAdmin()
    {
        if (! session('admin_connecte')) {
            return redirect()->to('/connexion')->with('error', 'Veuillez vous connecter comme administrateur.');
        }

        return null;
    }

    private function resolveOperateurId(string $nom): int
    {
        $db = db_connect();
        $operateur = $db->table('operateurs')->where('nom', $nom)->get()->getRowArray();

        if ($operateur !== null) {
            return (int) $operateur['id_operateur'];
        }

        $db->table('operateurs')->insert([
            'code' => $this->genererCodeOperateur($nom),
            'nom' => $nom,
            'principal' => 0,
            'commission_transfert_externe' => 0,
            'actif' => 1,
        ]);

        return (int) $db->insertID();
    }

    private function genererCodeOperateur(string $nom): string
    {
        $base = strtoupper(preg_replace('/[^A-Z0-9]+/i', '_', trim($nom)) ?: 'OPERATEUR');
        $base = trim($base, '_') ?: 'OPERATEUR';
        $code = $base;
        $suffixe = 1;

        while ($this->operateurs->where('code', $code)->first() !== null) {
            $suffixe++;
            $code = $base . '_' . $suffixe;
        }

        return $code;
    }
}
