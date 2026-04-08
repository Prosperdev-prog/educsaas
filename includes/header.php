<?php
// Déterminer le dossier de base en fonction du rôle
$role = $_SESSION['role'] ?? 'admin';
$base_url = '/saas/pages/' . ($role === 'eleve' ? 'eleves' : ($role === 'parent' ? 'parents' : ($role === 'enseignant' ? 'enseignants' : ($role === 'superadmin' ? 'superadmin' : 'admin'))));
?>
<!DOCTYPE html>
<html lang="fr text-dark">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>SaaS École - Gestion Scolaire Nouvelle Génération</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="/saas/assets/images/favicon.png">
    
    <!-- Google Fonts: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Pignose Calender -->
    <link href="/saas/assets/plugins/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <!-- Chartist -->
    <link rel="stylesheet" href="/saas/assets/plugins/chartist/css/chartist.min.css">
    <link rel="stylesheet" href="/saas/assets/plugins/chartist-plugin-tooltips/css/chartist-plugin-tooltip.css">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Stylesheet -->
    <link href="/saas/assets/css/style.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #5c67f2;
            --secondary: #f3f4f9;
            --dark: #2c3e50;
            --success: #00b894;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --glass: rgba(255, 255, 255, 0.7);
        }

        body {
            font-family: 'Outfit', sans-serif !important;
            background-color: #f8f9fa;
        }

        /* Premium Glassmorphism & Shadows */
        .card {
            border-radius: 16px !important;
            border: none !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
        }

        .content-body {
            padding-top: 80px;
        }

        .nk-sidebar {
            background: #ffffff !important;
            box-shadow: 4px 0 20px rgba(0,0,0,0.02);
        }

        .metismenu a {
            color: #6c757d !important;
            font-weight: 500;
            border-radius: 12px;
            margin: 4px 15px;
            padding: 12px 20px !important;
            transition: all 0.3s;
        }

        .metismenu li.active > a, .metismenu a:hover {
            background: rgba(92, 103, 242, 0.1) !important;
            color: var(--primary) !important;
        }

        .metismenu i {
            font-size: 1.2rem;
            margin-right: 15px;
        }

        .header {
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }

        .btn-primary {
            background: linear-gradient(45deg, #5c67f2, #7d85f5);
            border: none;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(92, 103, 242, 0.3);
        }

        /* Micro-animations */
        .fade-in { animation: fadeIn 0.5s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #aaa; }
    </style>
</head>

<body class="text-dark">
    <!-- Preloader start -->
    <div id="preloader">
        <div class="loader">
            <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10" />
            </svg>
        </div>
    </div>
    <!-- Preloader end -->

    <!-- Main wrapper start -->
    <div id="main-wrapper">

        <div class="nav-header bg-white shadow-sm border-bottom">
            <div class="brand-logo ">
                <a href="<?= $base_url ?>/dashboard.php" class="d-flex align-items-center">
                    <?php 
                        $display_logo = (!empty($_SESSION['school_logo'])) ? $_SESSION['school_logo'] : '/saas/assets/images/logo1.jpg';
                        $display_name = (!empty($_SESSION['school_name'])) ? $_SESSION['school_name'] : 'Ecole<span class="text-primary">SaaS</span>';
                    ?>
                    <!-- Logo visible quand le menu est réduit -->
                    <b class="logo-abbr p-3">
                        <img src="<?= $display_logo ?>" alt="" style="max-height: 30px; width: auto;">
                    </b>
                    <!-- Logo + Nom visibles quand le menu est étendu -->
                    <span class="brand-title d-flex align-items-center">
                        <img src="<?= $display_logo ?>" alt="Logo" class="me-3" style="max-height: 40px; width: auto; object-fit: contain;">
                        <span class="fw-bold text-dark h4 m-0 letter-spacing-1"><?= $display_name ?></span>
                        <?php if (isset($_SESSION['plan_name']) && $_SESSION['plan_name'] === 'PREMIUM'): ?>
                            <span class="badge bg-warning text-dark ms-2 shadow-sm" style="font-size: 0.6rem; vertical-align: middle;">
                                <i class="fas fa-crown me-1"></i>PRO
                            </span>
                        <?php endif; ?>
                    </span>
                </a>
            </div>
        </div>

        <!-- Header start -->
        <div class="header">    
            <div class="header-content clearfix">
                
                <div class="nav-control">
                    <div class="hamburger">
                        <span class="toggle-icon"><i class="icon-menu"></i></span>
                    </div>
                </div>
                <div class="header-left">
                    <div class="input-group icons">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-transparent border-0 pr-2 pr-sm-3" id="basic-addon1"><i class="mdi mdi-magnify"></i></span>
                        </div>
                        <input type="search" class="form-control" placeholder="Rechercher un élève..." aria-label="Search Dashboard">
                        <div class="drop-down animated flipInX d-md-none">
                            <form action="#">
                                <input type="text" class="form-control" placeholder="Search">
                            </form>
                        </div>
                    </div>
                </div>
                <div class="header-right">
                    <ul class="clearfix">
                        <li class="icons dropdown">
                            <div class="user-img c-pointer position-relative" data-toggle="dropdown">
                                <span class="activity active"></span>
                                <img src="<?= $_SESSION['user_photo'] ?? '/saas/assets/images/user/1.png' ?>" height="40" width="40" alt="" class="rounded-circle shadow-sm">
                            </div>
                            <div class="drop-down dropdown-profile animated fadeIn dropdown-menu">
                                <div class="dropdown-content-body">
                                    <ul>
                                        <li><a href="<?= $base_url ?>/profil.php"><i class="icon-user"></i> <span>Profil</span></a></li>
                                        <li><a href="javascript:void(0)" onclick="logout()"><i class="icon-key"></i> <span>Déconnexion</span></a></li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Header end -->