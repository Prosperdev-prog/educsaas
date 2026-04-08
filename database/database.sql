-- ==========================================================
-- BASE DE DONNÉES : ANTIGRAVITY SAAS (SCOLARITÉ)
-- Version : 2.0 (Prête pour Production)
-- Devise : FCFA (XOF)
-- ==========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- 1. STRUCTURE DES ÉCOLES & PLANS
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(50),
    email VARCHAR(150),
    logo VARCHAR(255),
    license_status ENUM('trial', 'active', 'expired', 'suspended') DEFAULT 'trial',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL, -- 'FREE', 'PREMIUM'
    price_monthly INT DEFAULT 0, -- en FCFA
    price_yearly INT DEFAULT 0, -- en FCFA
    max_students INT DEFAULT 50,
    has_bulletins BOOLEAN DEFAULT FALSE,
    has_parents BOOLEAN DEFAULT FALSE,
    has_stats BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    amount INT NOT NULL, -- en FCFA
    currency VARCHAR(3) DEFAULT 'XOF',
    payment_method VARCHAR(50), -- 'Mobile Money', 'Cash', 'Card'
    transaction_ref VARCHAR(100) UNIQUE,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. UTILISATEURS & RÔLES
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL, -- 0 pour Superadmin
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'admin', 'enseignant', 'eleve', 'parent') NOT NULL,
    phone VARCHAR(50),
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. STRUCTURE SCOLAIRE
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    nom VARCHAR(50) NOT NULL,
    niveau VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS eleves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    user_id INT NULL, -- Pour l'accès élève
    classe_id INT NULL,
    matricule VARCHAR(50) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE,
    sexe ENUM('M', 'F') NOT NULL,
    adresse TEXT,
    nom_parent VARCHAR(150),
    telephone_parent VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(school_id, matricule),
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS matieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    niveau VARCHAR(50) DEFAULT 'Général',
    coefficient INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. RÉSULTATS & SUIVI
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    eleve_id INT NOT NULL,
    matiere_id INT NOT NULL,
    note DECIMAL(5,2) NOT NULL,
    type ENUM('devoir', 'examen', 'participation') DEFAULT 'examen',
    date_evaluation DATE,
    commentaire TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (eleve_id) REFERENCES eleves(id) ON DELETE CASCADE,
    FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS devoirs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    classe_id INT NOT NULL,
    matiere_id INT NOT NULL,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    date_limite DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS presences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    eleve_id INT NOT NULL,
    date_presence DATE NOT NULL,
    statut ENUM('présent', 'absent', 'retard') NOT NULL,
    motif TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (eleve_id) REFERENCES eleves(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. MODULE PARENTS (SaaS Premium)
-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS parent_eleve (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    parent_id INT NOT NULL, -- user_id avec role 'parent'
    eleve_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (eleve_id) REFERENCES eleves(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. DONNÉES INITIALES (SETUP)
-- ---------------------------------------------------------

-- Plans
INSERT INTO plans (name, price_monthly, price_yearly, max_students, has_bulletins, has_parents, has_stats) VALUES 
('FREE', 0, 0, 50, 0, 0, 0),
('PREMIUM', 5000, 50000, 999999, 1, 1, 1);

-- École Superadmin (Système)
INSERT INTO schools (id, name, license_status) VALUES (1, 'SYSTEM_ADMIN', 'active');

-- Superadmin par défaut (Pass: admin123)
INSERT INTO users (school_id, nom, prenom, email, password, role) 
VALUES (1, 'Prosper', 'Admin', 'superadmin@antigravity.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');
