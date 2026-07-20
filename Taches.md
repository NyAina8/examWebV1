# Tâches du projet Mobile Money

## Membres du binôme

- Ny Aina
- Fitahiana

## Technologies

- PHP avec CodeIgniter 4
- SQLite
- HTML, CSS et JavaScript
- Bootstrap

# Version 1 — Tag v1

## Tâches de Ny Aina

### Base de données

- Concevoir la base de données SQLite.
- Créer le fichier `base.sql` 
- Créer les tables nécessaires :
  - opérateurs ;
  - préfixes téléphoniques ;
  - types d’opérations ;
  - barèmes de frais ;
  - clients ;
  - comptes Mobile Money ;
  - opérations ;
  - historiques.
- Ajouter les relations entre les différentes tables.
- Ajouter les données initiales nécessaires.
- Ajouter les types d’opérations :
  - dépôt ;
  - retrait ;
  - transfert.
- Ajouter dans la base les différents barèmes de frais fournis dans le sujet.

### Login client

- Créer la page de connexion par numéro de téléphone.
- Vérifier que le numéro possède un préfixe valide.
- Vérifier que le compte client existe.
- Connecter automatiquement le client s’il existe.
- Ne pas prévoir d’inscription ni de mot de passe.
- Créer et gérer la session du client connecté.
- Ajouter la déconnexion.

### Gestion du solde

- Créer la page principale du compte client.
- Afficher le numéro de téléphone du client.
- Afficher le nom du propriétaire du compte.
- Afficher le solde actuel du compte.
- Protéger les pages pour empêcher l’accès sans connexion.

### Dépôt

- Créer le formulaire de dépôt.
- Vérifier que le montant est valide et supérieur à zéro.
- Ajouter automatiquement le montant au solde.
- Enregistrer le dépôt dans l’historique.
- Afficher un message de succès ou d’erreur.

---

## Tâches de Fitahiana

### Interface opérateur

- Créer le tableau de bord de l’opérateur.
- Créer la gestion des préfixes téléphoniques valides.
- Permettre l’ajout, la modification et la suppression d’un préfixe.
- Afficher la liste des préfixes configurés.

### Types d’opérations

- Créer la gestion des types d’opérations :
  - dépôt ;
  - retrait ;
  - transfert.
- Permettre d’activer ou de désactiver un type d’opération.
- Afficher les types d’opérations disponibles.

### Barèmes de frais

- Créer l’interface de gestion des frais de retrait et de transfert.
- Afficher les tranches de montants.
- Permettre la modification du montant minimum.
- Permettre la modification du montant maximum.
- Permettre la modification du montant des frais.
- Vérifier que les tranches ne se chevauchent pas.

### Retrait client

- Créer le formulaire de retrait.
- Calculer automatiquement les frais selon le montant demandé.
- Vérifier que le client possède suffisamment d’argent pour payer le montant et les frais.
- Déduire le montant et les frais du solde.
- Enregistrer le retrait dans l’historique.
- Afficher le détail du retrait et des frais.

### Transfert client

- Créer le formulaire de transfert.
- Vérifier que le numéro du destinataire existe.
- Empêcher le transfert vers son propre numéro.
- Calculer automatiquement les frais.
- Vérifier que le solde est suffisant.
- Débiter le compte de l’expéditeur.
- Créditer automatiquement le compte du destinataire.
- Enregistrer le transfert dans l’historique des deux comptes.
- Afficher un message de confirmation.

### Historique

- Créer la page d’historique des opérations.
- Afficher :
  - la date ;
  - le type d’opération ;
  - le montant ;
  - les frais ;
  - le numéro concerné ;
  - le solde après l’opération.
- Classer les opérations de la plus récente à la plus ancienne.

