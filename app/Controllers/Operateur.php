<?php

namespace App\Controllers;

use App\Models\BaremeFraisModel;
use App\Models\PrefixeTelephoniqueModel;
use App\Models\TypeOperationModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Operateur extends BaseController
{
    private PrefixeTelephoniqueModel $prefixes;
    private TypeOperationModel $types;
    private BaremeFraisModel $baremes;

    public function __construct()
    {
        $this->prefixes = new PrefixeTelephoniqueModel();
        $this->types = new TypeOperationModel();
        $this->baremes = new BaremeFraisModel();
    }

    public function index(): string
    {
        return view('operateur/dashboard', [
            'prefixesCount' => $this->prefixes->countAllResults(),
            'typesCount' => $this->types->countAllResults(),
            'activeTypesCount' => $this->types->where('actif', 1)->countAllResults(),
            'baremesCount' => $this->baremes->countAllResults(),
        ]);
    }

    public function prefixes(): string
    {
        return view('operateur/prefixes/index', [
            'prefixes' => $this->prefixes->orderBy('prefixe', 'ASC')->findAll(),
        ]);
    }

    public function newPrefixe(): string
    {
        return view('operateur/prefixes/form', [
            'prefixe' => null,
            'errors' => session('errors') ?? [],
        ]);
    }

    public function createPrefixe()
    {
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

    public function editPrefixe(int $id): string
    {
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
        try {
            $this->prefixes->delete($id);
        } catch (DatabaseException $exception) {
            return redirect()->to('/operateur/prefixes')->with('error', 'Suppression impossible : ce préfixe est utilisé par un compte.');
        }

        return redirect()->to('/operateur/prefixes')->with('success', 'Préfixe supprimé.');
    }

    public function types(): string
    {
        return view('operateur/types/index', [
            'types' => $this->types->orderBy('id_type_operation', 'ASC')->findAll(),
        ]);
    }

    public function toggleType(int $id)
    {
        $type = $this->types->find($id);

        if ($type === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Type d’opération introuvable.');
        }

        $this->types->update($id, ['actif' => (int) ! (bool) $type['actif']]);

        return redirect()->to('/operateur/types')->with('success', 'Statut du type d’opération mis à jour.');
    }

    public function baremes(): string
    {
        return view('operateur/baremes/index', [
            'baremes' => $this->baremes
                ->select('baremes_frais.*, types_operations.code, types_operations.libelle')
                ->join('types_operations', 'types_operations.id_type_operation = baremes_frais.id_type_operation')
                ->orderBy('types_operations.id_type_operation', 'ASC')
                ->orderBy('baremes_frais.montant_min', 'ASC')
                ->findAll(),
        ]);
    }

    public function newBareme(): string
    {
        return view('operateur/baremes/form', [
            'bareme' => null,
            'types' => $this->types->orderBy('id_type_operation', 'ASC')->findAll(),
            'errors' => session('errors') ?? [],
        ]);
    }

    public function createBareme()
    {
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

    public function editBareme(int $id): string
    {
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
        $this->baremes->delete($id);

        return redirect()->to('/operateur/baremes')->with('success', 'Barème supprimé.');
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
}
