PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS operations;
DROP TABLE IF EXISTS baremes_frais;
DROP TABLE IF EXISTS comptes_mobile_money;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS types_operations;
DROP TABLE IF EXISTS prefixes_telephoniques;

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
    prefixe TEXT NOT NULL UNIQUE,
    operateur TEXT NOT NULL,
    actif INTEGER NOT NULL DEFAULT 1 CHECK (actif IN (0, 1)),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT,
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
    montant INTEGER NOT NULL CHECK (montant > 0),
    frais INTEGER NOT NULL DEFAULT 0 CHECK (frais >= 0),
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
    CHECK (id_compte_source IS NOT NULL OR id_compte_destination IS NOT NULL),
    CHECK (id_compte_source IS NULL OR id_compte_destination IS NULL OR id_compte_source <> id_compte_destination)
);

CREATE INDEX idx_comptes_mobile_money_client ON comptes_mobile_money(id_client);
CREATE INDEX idx_comptes_mobile_money_prefixe ON comptes_mobile_money(id_prefixe);
CREATE INDEX idx_baremes_frais_type_montants ON baremes_frais(id_type_operation, montant_min, montant_max);
CREATE INDEX idx_operations_source_date ON operations(id_compte_source, created_at);
CREATE INDEX idx_operations_destination_date ON operations(id_compte_destination, created_at);
CREATE INDEX idx_operations_type ON operations(id_type_operation);

INSERT INTO prefixes_telephoniques (id_prefixe, prefixe, operateur) VALUES
    (1, '032', 'Orange Money'),
    (2, '033', 'Airtel Money'),
    (3, '034', 'MVola'),
    (4, '038', 'MVola');

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
    (2, 1, 5000, 100),
    (2, 5001, 10000, 200),
    (2, 10001, 25000, 500),
    (2, 25001, 50000, 1000),
    (2, 50001, 100000, 1500),
    (2, 100001, 250000, 3000),
    (2, 250001, 500000, 5000),
    (2, 500001, NULL, 10000),
    (3, 1, 5000, 100),
    (3, 5001, 10000, 200),
    (3, 10001, 25000, 500),
    (3, 25001, 50000, 1000),
    (3, 50001, 100000, 1500),
    (3, 100001, 250000, 3000),
    (3, 250001, 500000, 5000),
    (3, 500001, NULL, 10000);
