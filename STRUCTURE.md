# Architecture du Projet 🧱

Antigravity est structuré de manière modulaire pour faciliter la maintenance et l'évolutivité.

## 📁 Structure des dossiers

### `/api`
Contient toute la logique backend. Chaque module (élèves, notes, abonnements) possède son propre sous-dossier.
- **Principe** : Une route = un fichier PHP qui retourne du JSON.

### `/config`
Configuration centrale de l'application (Connexion PDO, Constantes globales).

### `/includes`
Composants réutilisables de l'interface :
- `header.php` : Barre de navigation et meta.
- `sidebar.php` : Menu latéral dynamique selon le rôle.
- `footer.php` : Scripts JS et copyright.

### `/admin`, `/enseignants`, `/eleves`, `/parents`, `/superadmin`
Contient les pages d'interface (Vues) spécifiques à chaque rôle utilisateur.

### `/assets`
Ressources statiques :
- `/css` : Styles personnalisés (Bootstrap 5).
- `/js` : Scripts front-end (Appels Fetch API).
- `/plugins` : Librairies tierces (Datatables, SweetAlert2, etc.).

## 🔐 Isolation des Données (Multi-Tenancy)
Chaque table de la base de données (sauf `plans` et `schools`) possède une colonne `school_id`.
Lorsqu'un utilisateur se connecte, son `school_id` est stocké en session. Toutes les requêtes SQL incluent systématiquement une clause `WHERE school_id = ?` pour empêcher toute fuite de données entre établissements.
