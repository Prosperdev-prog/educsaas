# Documentation de l'API 🔌

Toutes les requêtes vers l'API d'Antigravity retournent du **JSON**.

## 🛡️ Authentification & Sécurité
- Les rôles sont vérifiés à chaque appel : `admin`, `enseignant`, `eleve`, `parent`, `superadmin`.
- Toutes les données sont filtrées via `school_id` pour assurer l'isolation entre les écoles.

## 📝 Routes Principales

### 🔐 Authentification
| Route | Méthode | Description |
| :--- | :--- | :--- |
| `/api/auth/login.php` | `POST` | Connexion utilisateur (email, password) |
| `/api/auth/logout.php` | `GET` | Déconnexion |

### 🏫 Gestion des Écoles (Superadmin)
| Route | Méthode | Description |
| :--- | :--- | :--- |
| `/api/superadmin/read_schools.php` | `GET` | Liste toutes les écoles inscrites |
| `/api/superadmin/create_school.php` | `POST` | Enregistre une nouvelle école |

### 👨‍🎓 Élèves
| Route | Méthode | Description |
| :--- | :--- | :--- |
| `/api/eleves/read.php` | `GET` | Liste les élèves de l'école (via `school_id`) |
| `/api/eleves/create.php` | `POST` | Ajoute un nouvel élève |
| `/api/eleves/stats.php` | `GET` | Statistiques de réussite (Premium) |

### 📊 Notes & Bulletins
| Route | Méthode | Description |
| :--- | :--- | :--- |
| `/api/notes/create.php` | `POST` | Ajoute une note |
| `/api/bulletins/read.php` | `GET` | Récupère les données d'un bulletin |

### 💳 Abonnements
| Route | Méthode | Description |
| :--- | :--- | :--- |
| `/api/subscriptions/check.php` | `GET` | Vérifie si l'école a une licence valide |

## 📦 Format de réponse standard

**Succès :**
```json
{
    "status": "success",
    "message": "Action effectuée",
    "data": { ... }
}
```

**Erreur :**
```json
{
    "status": "error",
    "message": "Erreur d'authentification"
}
```
