<?php

namespace App\Controllers;

use App\Models\BaremeFraisModel;
use App\Models\CompteMobileMoneyModel;
use App\Models\PrefixeTelephoniqueModel;
use App\Models\TypeOperationModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Operateur extends BaseController
{
    private PrefixeTelephoniqueModel $prefixes;
    private TypeOperationModel $types;
    private BaremeFraisModel $baremes;
    private CompteMobileMoneyModel $comptes;

    public function __construct()
    {
        $this->prefixes = new PrefixeTelephoniqueModel();
        $this->types = new TypeOperationModel();
        $this->baremes = new BaremeFraisModel();
        $this->comptes = new CompteMobileMoneyModel();
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
            'prefixes' => $this->prefixes->orderBy('prefixe', 'ASC')->findAll(),
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
            ->select('operations.*, source.numero_telephone AS numero_source, destination.numero_telephone AS numero_destination, source_client.nom AS nom_source, source_client.prenom AS prenom_source, destination_client.nom AS nom_destination, destination_client.prenom AS prenom_destination')
            ->join('types_operations', 'types_operations.id_type_operation = operations.id_type_operation')
            ->join('comptes_mobile_money AS source', 'source.id_compte = operations.id_compte_source')
            ->join('clients AS source_client', 'source_client.id_client = source.id_client')
            ->join('comptes_mobile_money AS destination', 'destination.id_compte = operations.id_compte_destination')
            ->join('clients AS destination_client', 'destination_client.id_client = destination.id_client')
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

        $operations = db_connect()->table('operations')
            ->select('operations.*, types_operations.libelle AS type_operation, types_operations.code AS type_code, source.numero_telephone AS numero_source, destination.numero_telephone AS numero_destination')
            ->join('types_operations', 'types_operations.id_type_operation = operations.id_type_operation')
            ->join('comptes_mobile_money AS source', 'source.id_compte = operations.id_compte_source', 'left')
            ->join('comptes_mobile_money AS destination', 'destination.id_compte = operations.id_compte_destination', 'left')
            ->whereIn('types_operations.code', ['retrait', 'transfert'])
            ->where('operations.statut', 'validee')
            ->orderBy('operations.created_at', 'DESC')
            ->orderBy('operations.id_operation', 'DESC')
            ->get()
            ->getResultArray();

        return view('operateur/gains/index', [
            'operations' => $operations,
            'gainTotal' => array_sum(array_map(static fn (array $operation): int => (int) $operation['frais'], $operations)),
            'gainRetrait' => $this->totalFraisParType('retrait'),
            'gainTransfert' => $this->totalFraisParType('transfert'),
        ]);
    }


    private function prefixeData(): array
    {
        return [
            'prefixe' => trim((string) $this->request->getPost('prefixe')),
            'operateur' => trim((string) $this->request->getPost('operateur')),
            'actif' => (int) ($this->request->getPost('actif') === '1'),
        ];
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
            'operateur' => [
                'label' => 'Opérateur',
                'rules' => 'required|min_length[2]',
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
}
