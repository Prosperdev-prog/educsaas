# Guide d'Installation 🛠️

Suivez ces étapes pour déployer **Antigravity SaaS** sur votre serveur local ou en production.

## 📋 Pré-requis
- **PHP** : 8.0 ou supérieur.
- **Base de données** : MySQL 5.7+ ou MariaDB.
- **Extensions PHP requises** : `pdo_mysql`, `mbstring`, `json`.

## 🚀 Étapes d'Installation

### 1. Cloner le projet
Placez les fichiers dans votre répertoire web (ex: `C:/xampp/htdocs/saas`).

### 2. Configuration de la base de données
1. Créez une base de données nommée `saas_ecole_db`.
2. Importez le fichier `database.sql` situé à la racine du projet.
   ```bash
   mysql -u root -p saas_ecole_db < database.sql
   ```

### 3. Configuration du fichier de connexion
Éditez le fichier `config/db.php` pour renseigner vos identifiants :
```php
<?php
$host = "localhost";
$dbname = "saas_ecole_db";
$username = "votre_user";
$password = "votre_pass";
// ...
```

### 4. Accès par défaut (Superadmin)
Une fois installé, vous pouvez vous connecter avec les identifiants suivants :
- **URL** : `http://localhost/saas/`
- **Email** : `superadmin@antigravity.com`
- **Mot de passe** : `admin123`

## 🔐 Sécurité
- Assurez-vous que le dossier `config/` n'est pas accessible publiquement via le navigateur.
- Changez immédiatement le mot de passe superadmin après la première connexion.
