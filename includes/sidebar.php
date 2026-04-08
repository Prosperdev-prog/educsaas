<?php
// Déterminer le dossier de base en fonction du rôle
$role = $_SESSION['role'] ?? 'admin';
$plan = $_SESSION['plan_name'] ?? 'FREE';
$has_parents = $_SESSION['has_parents'] ?? 0;
$has_bulletins = $_SESSION['has_bulletins'] ?? 0;

$base_url = '/saas/pages/' . ($role === 'eleve' ? 'eleves' : ($role === 'parent' ? 'parents' : ($role === 'enseignant' ? 'enseignants' : ($role === 'superadmin' ? 'superadmin' : 'admin'))));
?>
<!-- Sidebar start -->
<div class="nk-sidebar">
    <div class="nk-nav-scroll">
        <ul class="metismenu" id="menu">
            <li class="nav-label">Menu Principal</li>
            <li>
                <a href="<?= $base_url ?>/dashboard.php" aria-expanded="false">
                    <i class="icon-speedometer menu-icon"></i><span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <?php if ($role === 'admin'): ?>
            <li>
                <a href="<?= $base_url ?>/eleves.php" aria-expanded="false">
                    <i class="icon-people menu-icon"></i><span class="nav-text">Élèves</span>
                </a>
            </li>
            <li>
                <a href="<?= $base_url ?>/classes.php" aria-expanded="false">
                    <i class="icon-grid menu-icon"></i><span class="nav-text">Classes</span>
                </a>
            </li>
            <li>
                <a href="<?= $base_url ?>/matieres.php" aria-expanded="false">
                    <i class="icon-book-open menu-icon"></i><span class="nav-text">Matières</span>
                </a>
            </li>
            <li class="nav-label">Comptes Utilisateurs</li>
            <li>
                <a href="<?= $base_url ?>/enseignants.php" aria-expanded="false">
                    <i class="icon-user menu-icon"></i><span class="nav-text">Personnel</span>
                </a>
            </li>
            
            <?php if ($has_parents): ?>
            <li>
                <a href="<?= $base_url ?>/parents.php" aria-expanded="false">
                    <i class="icon-user-female menu-icon"></i><span class="nav-text">Parents</span>
                </a>
            </li>
            <?php endif; ?>

            <li>
                <a href="<?= $base_url ?>/mon_ecole.php" aria-expanded="false">
                    <i class="icon-settings menu-icon"></i><span class="nav-text">Mon Établissement</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (in_array($role, ['admin', 'enseignant'])): ?>
            <li>
                <a href="<?= $base_url ?>/notes.php" aria-expanded="false">
                    <i class="icon-badge menu-icon"></i><span class="nav-text">Saisie des Notes</span>
                </a>
            </li>
            
            <?php if ($has_bulletins): ?>
            <li>
                <a href="<?= $base_url ?>/bulletins.php" aria-expanded="false">
                    <i class="icon-doc menu-icon"></i><span class="nav-text">Bulletins</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-label">Gestion de Classe</li>
            <li>
                <a href="<?= $base_url ?>/presences.php" aria-expanded="false">
                    <i class="icon-check menu-icon"></i><span class="nav-text">Présences</span>
                </a>
            </li>
            <li>
                <a href="<?= $base_url ?>/devoirs.php" aria-expanded="false">
                    <i class="icon-notebook menu-icon"></i><span class="nav-text">Devoirs</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (in_array($role, ['eleve', 'parent'])): ?>
            <li class="nav-label">Espace Personnel</li>
            <li>
                <a href="<?= $base_url ?>/mes_notes.php" aria-expanded="false">
                    <i class="icon-badge menu-icon"></i><span class="nav-text">Mes Notes</span>
                </a>
            </li>
            <li>
                <a href="<?= $base_url ?>/mes_devoirs.php" aria-expanded="false">
                    <i class="icon-notebook menu-icon"></i><span class="nav-text">Cahier de Textes</span>
                </a>
            </li>
            <?php if ($has_bulletins): ?>
            <li>
                <a href="<?= $base_url ?>/mon_bulletin.php" aria-expanded="false">
                    <i class="icon-doc menu-icon"></i><span class="nav-text">Mon Bulletin</span>
                </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($role === 'superadmin'): ?>
            <li class="nav-label">Administration Système</li>
            <li>
                <a href="<?= $base_url ?>/ecoles.php" aria-expanded="false">
                    <i class="icon-notebook menu-icon"></i><span class="nav-text">Gérer Écoles</span>
                </a>
            </li>
            <li>
                <a href="<?= $base_url ?>/utilisateurs.php" aria-expanded="false">
                    <i class="icon-people menu-icon"></i><span class="nav-text">Tout les Users</span>
                </a>
            </li>
            <li>
                <a href="<?= $base_url ?>/licences.php" aria-expanded="false">
                    <i class="icon-badge menu-icon"></i><span class="nav-text">Licences</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <?php if ($role === 'admin' && $plan !== 'PREMIUM'): ?>
        <!-- PRO Version Banner : Visible uniquement pour l'admin non-premium -->
        <div class="sidebar-pro-banner m-3 p-4 text-center bg-dark rounded-4 shadow-lg position-relative overflow-hidden">
            <div class="position-absolute" style="width: 150px; height: 150px; background: rgba(92, 103, 242, 0.2); border-radius: 50%; top: -50px; right: -50px;"></div>
            <div class="position-relative">
                <div class="display-4 text-warning mb-2">
                    <i class="fas fa-crown"></i>
                </div>
                <h5 class="text-white fw-bold mb-1">Passer à la PRO</h5>
                <p class="text-white-50 small mb-3">Bulletins PDF, accès parents & stats avancées.</p>
                <a href="<?= $base_url ?>/pricing.php" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold shadow-sm w-100">
                    Débloquer
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.sidebar-pro-banner {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%) !important;
    border: 1px solid rgba(255,255,255,0.05);
}
.sidebar-pro-banner .btn-warning {
    background: #ffc107;
    border: none;
    color: #000;
}
.sidebar-pro-banner .btn-warning:hover {
    background: #e0a800;
    transform: scale(1.05);
}
.rounded-4 { border-radius: 20px !important; }
</style>
<!-- Sidebar end -->