PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS operations;
DROP TABLE IF EXISTS baremes_frais;
DROP TABLE IF EXISTS comptes_mobile_money;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS types_operations;
DROP TABLE IF EXISTS prefixes_telephoniques;
DROP TABLE IF EXISTS operateurs;

CREATE TABLE operateurs (
    id_operateur INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT NOT NULL UNIQUE,
    nom TEXT NOT NULL UNIQUE,
    principal INTEGER NOT NULL DEFAULT 0 CHECK (principal IN (0, 1)),
    commission_transfert_externe REAL NOT NULL DEFAULT 0 CHECK (commission_transfert_externe >= 0 AND commission_transfert_externe <= 100),
    actif INTEGER NOT NULL DEFAULT 1 CHECK (actif IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT,
    CHECK (length(trim(nom)) > 0)
);

CREATE TABLE clients (
    id_client INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL,
    prenom TEXT NOT NULL,
    email TEXT UNIQUE,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT,
    CHECK (length(trim(nom)) > 0),
    CHECK (length(trim(prenom)) > 0)
);

CREATE TABLE prefixes_telephoniques (
    id_prefixe INTEGER PRIMARY KEY AUTOINCREMENT,
    id_operateur INTEGER NOT NULL,
    prefixe TEXT NOT NULL UNIQUE,
    operateur TEXT NOT NULL,
    actif INTEGER NOT NULL DEFAULT 1 CHECK (actif IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT,
    FOREIGN KEY (id_operateur) REFERENCES operateurs(id_operateur)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CHECK (prefixe GLOB '03[0-9]'),
    CHECK (length(trim(operateur)) > 0)
);

CREATE TABLE types_operations (
    id_type_operation INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT NOT NULL UNIQUE,
    libelle TEXT NOT NULL,
    actif INTEGER NOT NULL DEFAULT 1 CHECK (actif IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT,
    CHECK (code IN ('depot', 'retrait', 'transfert')),
    CHECK (length(trim(libelle)) > 0)
);

CREATE TABLE comptes_mobile_money (
    id_compte INTEGER PRIMARY KEY AUTOINCREMENT,
    id_client INTEGER NOT NULL,
    id_prefixe INTEGER NOT NULL,
    numero_telephone TEXT NOT NULL UNIQUE,
    solde INTEGER NOT NULL DEFAULT 0 CHECK (solde >= 0),
    pourcentage_epargne INTEGER NOT NULL DEFAULT 0 CHECK (pourcentage_epargne >= 0 AND pourcentage_epargne <= 100),
    solde_epargne INTEGER NOT NULL DEFAULT 0 CHECK (solde_epargne >= 0),
    statut TEXT NOT NULL DEFAULT 'actif' CHECK (statut IN ('actif', 'bloque')),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT,
    FOREIGN KEY (id_client) REFERENCES clients(id_client)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (id_prefixe) REFERENCES prefixes_telephoniques(id_prefixe)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CHECK (numero_telephone GLOB '03[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]')
);

CREATE TABLE baremes_frais (
    id_bareme INTEGER PRIMARY KEY AUTOINCREMENT,
    id_type_operation INTEGER NOT NULL,
    montant_min INTEGER NOT NULL CHECK (montant_min >= 0),
    montant_max INTEGER CHECK (montant_max IS NULL OR montant_max >= montant_min),
    frais INTEGER NOT NULL CHECK (frais >= 0),
    actif INTEGER NOT NULL DEFAULT 1 CHECK (actif IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT,
    FOREIGN KEY (id_type_operation) REFERENCES types_operations(id_type_operation)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    UNIQUE (id_type_operation, montant_min, montant_max)
);

CREATE TABLE operations (
    id_operation INTEGER PRIMARY KEY AUTOINCREMENT,
    reference TEXT NOT NULL UNIQUE,
    id_type_operation INTEGER NOT NULL,
    id_compte_source INTEGER,
    id_compte_destination INTEGER,
    id_operateur_source INTEGER,
    id_operateur_destination INTEGER,
    numero_destinataire TEXT,
    montant INTEGER NOT NULL CHECK (montant > 0),
    frais INTEGER NOT NULL DEFAULT 0 CHECK (frais >= 0),
    pourcentage_commission REAL NOT NULL DEFAULT 0 CHECK (pourcentage_commission >= 0 AND pourcentage_commission <= 100),
    frais_retrait_inclus INTEGER NOT NULL DEFAULT 0 CHECK (frais_retrait_inclus >= 0),
    commission_interoperateur INTEGER NOT NULL DEFAULT 0 CHECK (commission_interoperateur >= 0),
    montant_reverser INTEGER NOT NULL DEFAULT 0 CHECK (montant_reverser >= 0),
    total_debite INTEGER NOT NULL DEFAULT 0 CHECK (total_debite >= 0),
    montant_recu INTEGER NOT NULL DEFAULT 0 CHECK (montant_recu >= 0),
    id_envoi_multiple TEXT,
    solde_source_apres INTEGER CHECK (solde_source_apres IS NULL OR solde_source_apres >= 0),
    solde_destination_apres INTEGER CHECK (solde_destination_apres IS NULL OR solde_destination_apres >= 0),
    statut TEXT NOT NULL DEFAULT 'validee' CHECK (statut IN ('validee', 'annulee')),
    description TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_type_operation) REFERENCES types_operations(id_type_operation)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (id_compte_source) REFERENCES comptes_mobile_money(id_compte)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (id_compte_destination) REFERENCES comptes_mobile_money(id_compte)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (id_operateur_source) REFERENCES operateurs(id_operateur)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (id_operateur_destination) REFERENCES operateurs(id_operateur)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CHECK (id_compte_source IS NOT NULL OR id_compte_destination IS NOT NULL),
    CHECK (id_compte_source IS NULL OR id_compte_destination IS NULL OR id_compte_source <> id_compte_destination)
);

CREATE INDEX idx_comptes_mobile_money_client ON comptes_mobile_money(id_client);
CREATE INDEX idx_comptes_mobile_money_prefixe ON comptes_mobile_money(id_prefixe);
CREATE INDEX idx_baremes_frais_type_montants ON baremes_frais(id_type_operation, montant_min, montant_max);
CREATE INDEX idx_operations_source_date ON operations(id_compte_source, created_at);
CREATE INDEX idx_operations_destination_date ON operations(id_compte_destination, created_at);
CREATE INDEX idx_operations_type ON operations(id_type_operation);
CREATE INDEX idx_operations_operateurs ON operations(id_operateur_source, id_operateur_destination);

INSERT INTO operateurs (id_operateur, code, nom, principal, commission_transfert_externe, actif) VALUES
    (1, 'YAS', 'Yas', 1, 0, 1),
    (2, 'OM', 'Orange Money', 0, 10, 1),
    (3, 'AIRTEL', 'Airtel Money', 0, 2, 1),
    (4, 'TELMA', 'Telma Money', 0, 1.5, 1);

INSERT INTO prefixes_telephoniques (id_prefixe, id_operateur, prefixe, operateur) VALUES
    (1, 2, '032', 'Orange Money'),
    (2, 3, '033', 'Airtel Money'),
    (3, 1, '034', 'Yas'),
    (4, 4, '037', 'Telma Money'),
    (5, 1, '038', 'Yas');

INSERT INTO types_operations (id_type_operation, code, libelle) VALUES
    (1, 'depot', 'Dépôt'),
    (2, 'retrait', 'Retrait'),
    (3, 'transfert', 'Transfert');

INSERT INTO clients (id_client, nom, prenom, email) VALUES
    (1, 'Rabe', 'Ny Aina', 'nyaina@example.test'),
    (2, 'Rakoto', 'Fitahiana', 'fitahiana@example.test'),
    (3, 'Rasoa', 'Miora', 'miora@example.test');

INSERT INTO comptes_mobile_money (id_compte, id_client, id_prefixe, numero_telephone, solde) VALUES
    (1, 1, 3, '0341234567', 150000),
    (2, 2, 1, '0327654321', 80000),
    (3, 3, 2, '0331122334', 25000);

INSERT INTO baremes_frais (id_type_operation, montant_min, montant_max, frais) VALUES
    (1, 1, NULL, 0),
    (2, 100, 1000, 50),
    (2, 1001, 5000, 50),
    (2, 5001, 10000, 100),
    (2, 10001, 25000, 200),
    (2, 25001, 50000, 400),
    (2, 50001, 100000, 800),
    (2, 100001, 250000, 1500),
    (2, 250001, 500000, 1500),
    (2, 500001, 1000000, 2500),
    (2, 1000001, 2000000, 3000),
    (3, 100, 1000, 50),
    (3, 1001, 5000, 50),
    (3, 5001, 10000, 100),
    (3, 10001, 25000, 200),
    (3, 25001, 50000, 1000),
    (3, 50001, 100000, 800),
    (3, 100001, 250000, 1500),
    (3, 250001, 500000, 1500),
    (3, 500001, 1000000, 2500),
    (3, 1000001, 2000000, 3000);
