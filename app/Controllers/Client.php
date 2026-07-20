<?php

namespace App\Controllers;

use App\Models\PrefixeTelephoniqueModel;
use App\Services\MobileMoneyService;
use InvalidArgumentException;
use RuntimeException;

class Client extends BaseController
{
    private MobileMoneyService $mobileMoney;
    private PrefixeTelephoniqueModel $prefixes;

    public function __construct()
    {
        $this->mobileMoney = new MobileMoneyService();
        $this->prefixes = new PrefixeTelephoniqueModel();
    }

    public function login()
    {
        if (session('client_connecte')) {
            return redirect()->to('/client');
        }

        return view('client/login', [
            'errors' => session('errors') ?? [],
        ]);
    }

    public function authenticate()
    {
        $numeroTelephone = $this->normaliserNumero((string) $this->request->getPost('numero_telephone'));
        $errors = $this->validerConnexion($numeroTelephone);

        if ($errors !== []) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $errors);
        }

        $compte = $this->mobileMoney->recupererCompteClient($numeroTelephone);

        if ($compte === null) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Aucun compte client ne correspond à ce numéro.');
        }

        if ($compte['statut'] !== 'actif') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ce compte est désactivé.');
        }

        session()->regenerate();
        session()->set([
            'client_connecte' => true,
            'client_id' => (int) $compte['id_client'],
            'compte_id' => (int) $compte['id_compte'],
            'numero_telephone' => $compte['numero_telephone'],
        ]);

        return redirect()->to('/client')->with('success', 'Connexion réussie.');
    }

    public function dashboard()
    {
        $redirect = $this->exigerConnexion();

        if ($redirect !== null) {
            return $redirect;
        }

        $compte = $this->mobileMoney->recupererCompteClient((int) session('compte_id'));

        if ($compte === null) {
            session()->destroy();

            return redirect()->to('/connexion')->with('error', 'Votre compte est introuvable. Veuillez vous reconnecter.');
        }

        return view('client/dashboard', [
            'compte' => $compte,
            'solde' => $this->mobileMoney->consulterSolde((int) $compte['id_compte']),
        ]);
    }

    public function depot()
    {
        $redirect = $this->exigerConnexion();

        if ($redirect !== null) {
            return $redirect;
        }

        $compte = $this->mobileMoney->recupererCompteClient((int) session('compte_id'));

        if ($compte === null) {
            session()->destroy();

            return redirect()->to('/connexion')->with('error', 'Votre compte est introuvable. Veuillez vous reconnecter.');
        }

        return view('client/depot', [
            'compte' => $compte,
            'errors' => session('errors') ?? [],
        ]);
    }

    public function retrait()
    {
        $redirect = $this->exigerConnexion();

        if ($redirect !== null) {
            return $redirect;
        }

        $compte = $this->mobileMoney->recupererCompteClient((int) session('compte_id'));

        if ($compte === null) {
            session()->destroy();

            return redirect()->to('/connexion')->with('error', 'Votre compte est introuvable. Veuillez vous reconnecter.');
        }

        return view('client/retrait', [
            'compte' => $compte,
            'errors' => session('errors') ?? [],
        ]);
    }

    public function transfert()
    {
        $redirect = $this->exigerConnexion();

        if ($redirect !== null) {
            return $redirect;
        }

        $compte = $this->mobileMoney->recupererCompteClient((int) session('compte_id'));

        if ($compte === null) {
            session()->destroy();

            return redirect()->to('/connexion')->with('error', 'Votre compte est introuvable. Veuillez vous reconnecter.');
        }

        return view('client/transfert', [
            'compte' => $compte,
            'errors' => session('errors') ?? [],
        ]);
    }

    public function historique()
    {
        $redirect = $this->exigerConnexion();

        if ($redirect !== null) {
            return $redirect;
        }

        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $historique = $this->mobileMoney->recupererHistoriqueClient((int) session('compte_id'), $page, 10);

        return view('client/historique', [
            'operations' => $historique['operations'],
            'page' => $historique['page'],
            'totalPages' => $historique['total_pages'],
            'total' => $historique['total'],
        ]);
    }

    public function enregistrerDepot()
    {
        $redirect = $this->exigerConnexion();

        if ($redirect !== null) {
            return $redirect;
        }

        $montant = $this->normaliserMontant((string) $this->request->getPost('montant'));
        $errors = $this->validerMontantDepot($montant);

        if ($errors !== []) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $errors);
        }

        try {
            $resultat = $this->mobileMoney->deposer((int) session('compte_id'), (int) $montant);
        } catch (InvalidArgumentException | RuntimeException $exception) {
            return redirect()->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        } catch (\Throwable) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Le dépôt n'a pas pu être enregistré.");
        }

        return redirect()->to('/client')
            ->with('success', 'Dépôt de ' . number_format((int) $montant, 0, ',', ' ') . ' Ar effectué. Nouveau solde : ' . number_format($resultat['nouveau_solde'], 0, ',', ' ') . ' Ar.');
    }

    public function enregistrerRetrait()
    {
        $redirect = $this->exigerConnexion();

        if ($redirect !== null) {
            return $redirect;
        }

        $montant = $this->normaliserMontant((string) $this->request->getPost('montant'));
        $errors = $this->validerMontantRetrait($montant);

        if ($errors !== []) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $errors);
        }

        try {
            $resultat = $this->mobileMoney->retirer((int) session('compte_id'), (int) $montant);
        } catch (InvalidArgumentException | RuntimeException $exception) {
            return redirect()->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        } catch (\Throwable) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Le retrait n'a pas pu être enregistré.");
        }

        return redirect()->to('/client/retrait/' . $resultat['id_operation'])
            ->with('success', 'Retrait effectué avec succès.');
    }

    public function enregistrerTransfert()
    {
        $redirect = $this->exigerConnexion();

        if ($redirect !== null) {
            return $redirect;
        }

        $numeroDestinataire = $this->normaliserNumero((string) $this->request->getPost('numero_destinataire'));
        $montant = $this->normaliserMontant((string) $this->request->getPost('montant'));
        $errors = $this->validerTransfert($numeroDestinataire, $montant);

        if ($errors !== []) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $errors);
        }

        try {
            $resultat = $this->mobileMoney->transferer((int) session('compte_id'), $numeroDestinataire, (int) $montant);
        } catch (InvalidArgumentException | RuntimeException $exception) {
            return redirect()->back()
                ->withInput()
                ->with('error', $exception->getMessage());
        } catch (\Throwable) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Le transfert n'a pas pu être enregistré.");
        }

        return redirect()->to('/client/transfert/' . $resultat['id_operation'])
            ->with('success', 'Transfert effectué avec succès.');
    }

    public function detailRetrait(int $id)
    {
        $redirect = $this->exigerConnexion();

        if ($redirect !== null) {
            return $redirect;
        }

        $operation = $this->mobileMoney->recupererRetraitClient($id, (int) session('compte_id'));

        if ($operation === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Retrait introuvable.');
        }

        return view('client/retrait_detail', [
            'operation' => $operation,
        ]);
    }

    public function detailTransfert(int $id)
    {
        $redirect = $this->exigerConnexion();

        if ($redirect !== null) {
            return $redirect;
        }

        $operation = $this->mobileMoney->recupererTransfertClient($id, (int) session('compte_id'));

        if ($operation === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Transfert introuvable.');
        }

        return view('client/transfert_detail', [
            'operation' => $operation,
        ]);
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/connexion')->with('success', 'Vous êtes déconnecté.');
    }

    private function validerConnexion(string $numeroTelephone): array
    {
        $errors = [];

        if ($numeroTelephone === '') {
            $errors['numero_telephone'] = 'Le numéro de téléphone est obligatoire.';

            return $errors;
        }

        if (! preg_match('/^03[0-9]{8}$/', $numeroTelephone)) {
            $errors['numero_telephone'] = 'Le numéro doit contenir 10 chiffres et commencer par 03.';

            return $errors;
        }

        if ($this->prefixes->findActiveForNumero($numeroTelephone) === null) {
            $errors['numero_telephone'] = 'Le préfixe de ce numéro n’est pas actif.';
        }

        return $errors;
    }

    private function normaliserNumero(string $numeroTelephone): string
    {
        return preg_replace('/\D+/', '', $numeroTelephone) ?? '';
    }

    private function normaliserMontant(string $montant): string
    {
        return preg_replace('/[\s]+/', '', trim($montant)) ?? '';
    }

    private function validerMontantDepot(string $montant): array
    {
        $errors = [];

        if ($montant === '') {
            $errors['montant'] = 'Le montant est obligatoire.';

            return $errors;
        }

        if (! ctype_digit($montant)) {
            $errors['montant'] = 'Le montant doit être numérique.';

            return $errors;
        }

        if ((int) $montant <= 0) {
            $errors['montant'] = 'Le montant doit être supérieur à zéro.';

            return $errors;
        }

        if (strlen($montant) > 18 || (int) $montant > PHP_INT_MAX) {
            $errors['montant'] = 'Le montant dépasse les limites du système.';
        }

        return $errors;
    }

    private function validerMontantRetrait(string $montant): array
    {
        $errors = [];

        if ($montant === '') {
            $errors['montant'] = 'Le montant est obligatoire.';

            return $errors;
        }

        if (! ctype_digit($montant)) {
            $errors['montant'] = 'Le montant doit être numérique.';

            return $errors;
        }

        if ((int) $montant <= 0) {
            $errors['montant'] = 'Le montant doit être supérieur à zéro.';

            return $errors;
        }

        if (strlen($montant) > 18 || (int) $montant > PHP_INT_MAX) {
            $errors['montant'] = 'Le montant dépasse les limites du système.';
        }

        return $errors;
    }

    private function validerTransfert(string $numeroDestinataire, string $montant): array
    {
        $errors = $this->validerMontantRetrait($montant);

        if ($numeroDestinataire === '') {
            $errors['numero_destinataire'] = 'Le numéro du destinataire est obligatoire.';

            return $errors;
        }

        if (! preg_match('/^03[0-9]{8}$/', $numeroDestinataire)) {
            $errors['numero_destinataire'] = 'Le numéro du destinataire doit contenir 10 chiffres et commencer par 03.';

            return $errors;
        }

        if ($this->prefixes->findActiveForNumero($numeroDestinataire) === null) {
            $errors['numero_destinataire'] = 'Le préfixe du destinataire n’est pas actif.';
        }

        if ($numeroDestinataire === session('numero_telephone')) {
            $errors['numero_destinataire'] = 'Vous ne pouvez pas transférer vers votre propre numéro.';
        }

        return $errors;
    }

    private function exigerConnexion()
    {
        if (! session('client_connecte') || ! session('compte_id')) {
            return redirect()->to('/connexion')->with('error', 'Veuillez vous connecter pour accéder à votre espace client.');
        }

        return null;
    }
}
