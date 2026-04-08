<?php 
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<!-- Content body start -->
<div class="content-body">
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-lg-3 col-sm-6">
                <div class="card gradient-1">
                    <div class="card-body">
                        <h3 class="card-title text-white">Élèves</h3>
                        <div class="d-inline-block">
                            <h2 class="text-white" id="stat-eleves">...</h2>
                            <p class="text-white mb-0">Total Inscrits</p>
                        </div>
                        <span class="float-right display-5 opacity-5"><i class="fa fa-users"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card gradient-2">
                    <div class="card-body">
                        <h3 class="card-title text-white">Matières</h3>
                        <div class="d-inline-block">
                            <h2 class="text-white" id="stat-matieres">...</h2>
                            <p class="text-white mb-0">Au programme</p>
                        </div>
                        <span class="float-right display-5 opacity-5"><i class="fa fa-book"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card gradient-3">
                    <div class="card-body">
                        <h3 class="card-title text-white">Salles de classe</h3>
                        <div class="d-inline-block">
                            <h2 class="text-white" id="stat-classes">...</h2>
                            <p class="text-white mb-0">Disponibles</p>
                        </div>
                        <span class="float-right display-5 opacity-5"><i class="fa fa-building"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card gradient-4">
                    <div class="card-body">
                        <h3 class="card-title text-white">Personnel</h3>
                        <div class="d-inline-block">
                            <h2 class="text-white" id="stat-enseignants">...</h2>
                            <p class="text-white mb-0">Professeurs / Admins</p>
                        </div>
                        <span class="float-right display-5 opacity-5"><i class="fa fa-user-circle"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SaaS Plan Status Banner -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 15px; background-color: rgba(92, 103, 242, 0.05);">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-3 me-3 shadow-sm text-primary">
                                <i class="fas fa-crown fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">Abonnement École : <span class="text-primary" id="plan-name-dash">Chargement...</span></h5>
                                <p class="mb-0 text-muted small">Profitez de toutes les fonctionnalités pour booster votre établissement.</p>
                            </div>
                        </div>
                        <a href="mon_ecole.php" class="btn btn-primary rounded-pill px-4 shadow-sm">Gérer le plan</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Dernières inscriptions (Activité)</h4>
                        <div class="active-member">
                            <div class="table-responsive">
                                <table class="table table-xs mb-0">
                                    <thead>
                                        <tr>
                                            <th>Élève</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="table-derniers">
                                        <!-- JS Filled -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Répartition Élèves</h4>
                        <canvas id="sexeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- #/ container -->
</div>
<!-- Content body end -->

<?php include '../../includes/footer.php'; ?>

<!-- Initialisation du dashboard spécifique -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    async function loadDashboardStats() {
        const res = await apiCall('/dashboard/stats.php');
        if (res.status === 'success') {
            document.getElementById('stat-eleves').textContent = res.data.total_eleves;
            document.getElementById('stat-classes').textContent = res.data.total_classes;
            document.getElementById('stat-matieres').textContent = res.data.total_matieres;
            document.getElementById('stat-enseignants').textContent = res.data.total_enseignants;
            
            // Remplir le tableau des derniers
            const tbody = document.getElementById('table-derniers');
            tbody.innerHTML = '';
            if(res.data.derniers_inscrits.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3">Aucune inscription pour le moment.</td></tr>';
            } else {
                res.data.derniers_inscrits.forEach(el => {
                    tbody.innerHTML += `
                        <tr>
                            <td><img src="/saas/assets/images/avatar/1.jpg" class="rounded-circle mr-3" alt="">${el.nom} ${el.prenom}</td>
                            <td><span>${el.created_at}</span></td>
                            <td><i class="fa fa-circle-o text-success mr-2"></i> Admis</td>
                        </tr>
                    `;
                });
            }

            // Init Chart js pour le doughnut (Sexe)
            const ctx = document.getElementById('sexeChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Garçons', 'Filles'],
                    datasets: [{
                        data: [res.data.stats_sexe.garcons, res.data.stats_sexe.filles],
                        backgroundColor: ['#4d7cff', '#ff5c6c']
                    }]
                }
            });
        }
        
        // Charger le nom du plan
        const schoolRes = await apiCall('/schools/read.php');
        if (schoolRes.status === 'success') {
            document.getElementById('plan-name-dash').textContent = schoolRes.data.subscription ? schoolRes.data.subscription.plan_name : 'FREE';
        }
    }
    document.addEventListener('DOMContentLoaded', loadDashboardStats);
</script>
