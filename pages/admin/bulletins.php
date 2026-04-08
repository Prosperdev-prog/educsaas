<?php 
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-3 text-dark pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 pt-3 flex-wrap gap-3">
            <h1 class="h3 mb-0 fw-bold"><i class="fas fa-edit text-primary me-2"></i> Éditeur de Bulletin</h1>
        </div>
        
        <!-- Toolbar & Config -->
        <div class="card shadow-sm border-0 mb-4 d-print-none" style="border-radius: 15px;">
            <div class="card-body p-3 p-md-4">
                <div class="row align-items-end mb-4 g-3">
                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label fw-bold small text-uppercase">1. Choisir l'Élève</label>
                        <select class="form-select form-control rounded-pill border-primary" id="eleve_select" onchange="generateBulletin()">
                            <option value="">Sélectionner...</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-8 col-lg-9 text-md-end d-flex flex-wrap justify-content-md-end gap-2">
                         <div class="btn-group shadow-sm flex-grow-1 flex-md-grow-0">
                             <button class="btn btn-sm btn-outline-dark bg-white" onclick="insertTemplate('header_classic')">Classique</button>
                             <button class="btn btn-sm btn-outline-dark bg-white d-none d-sm-inline-block" onclick="insertTemplate('logo_center')">Centré</button>
                             <button class="btn btn-sm btn-outline-dark bg-white" onclick="insertTemplate('side_by_side')">Double</button>
                         </div>
                         <button class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm" onclick="generateBulletin()">
                             <i class="fas fa-sync me-2"></i>Charger
                         </button>
                    </div>
                </div>

                <!-- Rich text simple toolbar -->
                <div class="bg-light p-2 rounded-top border d-flex flex-wrap gap-2 align-items-center">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-white border shadow-xs" onclick="execCmd('bold')"><i class="fas fa-bold"></i></button>
                        <button class="btn btn-sm btn-white border shadow-xs" onclick="execCmd('italic')"><i class="fas fa-italic"></i></button>
                    </div>
                    <div class="vr mx-1 d-none d-sm-block"></div>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-white border shadow-xs" onclick="execCmd('justifyLeft')"><i class="fas fa-align-left"></i></button>
                        <button class="btn btn-sm btn-white border shadow-xs" onclick="execCmd('justifyCenter')"><i class="fas fa-align-center"></i></button>
                    </div>
                    <div class="vr mx-1 d-none d-sm-block"></div>
                    <button class="btn btn-sm btn-white border shadow-xs" onclick="document.getElementById('logoUpload').click()"><i class="fas fa-upload text-success"></i></button>
                    <input type="file" id="logoUpload" style="display:none" accept="image/*" onchange="handleLogoUpload(this)">
                    <span class="ms-auto me-2 text-muted small d-none d-lg-inline"><i class="fas fa-info-circle me-1"></i> Édition libre activée</span>
                </div>
                
                <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 mt-4">
                     <button class="btn btn-success px-4 rounded-pill text-white shadow-sm w-100 w-sm-auto" onclick="saveBulletinConfig()">
                         <i class="fas fa-save me-2"></i>Sauvegarder
                     </button>
                     <div class="d-flex gap-2 w-100 w-sm-auto">
                        <button class="btn btn-outline-primary flex-grow-1 px-4 rounded-pill shadow-xs" onclick="window.print()" id="btn-print" disabled>
                            <i class="fas fa-print me-2"></i>Imprimer
                        </button>
                        <button class="btn btn-danger flex-grow-1 px-4 rounded-pill text-white shadow-sm" onclick="downloadPDF()" id="btn-pdf" disabled>
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </button>
                     </div>
                </div>
            </div>
        </div>

        <!-- Bulletin Zone -->
        <div class="bulletin-responsive-container">
            <div class="card shadow-lg d-none mt-4 border-0" id="bulletin-zone" style="width: 210mm; min-width: 210mm; margin: 0 auto; transform-origin: top center;">
                <div id="bulletin-content" class="bg-white p-4 p-md-5 text-dark" style="min-height: 297mm; position: relative;">
                     <!-- HEADER EDITABLE AREA -->
                     <div id="bulletin-header-editable" class="mb-4 mb-md-5 p-3 rounded" contenteditable="true" style="outline: 1px dashed #ddd; min-height: 150px;">
                          <div class="text-center text-muted py-5">Sélectionnez un élève pour commencer...</div>
                     </div>

                     <!-- DATA AREA (READ ONLY) -->
                     <div id="bulletin-table-area" class="mt-4 table-responsive"></div>

                     <!-- FOOTER AREA (EDITABLE) -->
                     <div id="bulletin-footer-editable" class="mt-5 pt-4 border-top" contenteditable="true" style="outline: 1px dashed #eee;">
                          <div class="row mt-4">
                              <div class="col-6 text-center">
                                  <p class="fw-bold mb-5">Signature du Parent</p>
                              </div>
                              <div class="col-6 text-center">
                                  <p class="fw-bold mb-5">Le Directeur</p>
                              </div>
                          </div>
                     </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
/* Responsive Scaling for Bulletin Preview */
.bulletin-responsive-container {
    width: 100%;
    overflow-x: auto;
    padding-bottom: 20px;
}

@media (max-width: 992px) {
    #bulletin-zone {
        transform: scale(0.8);
        margin-top: -50px !important;
    }
}
@media (max-width: 768px) {
    #bulletin-zone {
        transform: scale(0.6);
        margin-top: -120px !important;
    }
}
@media (max-width: 576px) {
    #bulletin-zone {
        transform: scale(0.45);
        margin-top: -180px !important;
    }
}

#bulletin-header-editable:focus, #bulletin-footer-editable:focus { 
    outline: 2px solid #5c67f2 !important; 
    background-color: #fff; 
}
#bulletin-header-editable img { max-height: 100px; }

@media print {
    body * { visibility: hidden; }
    #bulletin-zone, #bulletin-zone * { visibility: visible; }
    #bulletin-zone { 
        position: absolute; left: 0; top: 0; width: 210mm !important; 
        transform: none !important; margin: 0 !important; padding: 0 !important; 
        box-shadow: none !important; 
    }
    .d-print-none { display: none !important; }
}
</style>

<script>
    let currentData = null;
    let schoolLogo = '<?= $_SESSION['school_logo'] ?? '/saas/assets/images/logo1.jpg' ?>';

    document.addEventListener('DOMContentLoaded', () => {
        loadEleves();
        loadSavedConfig();
    });

    async function loadSavedConfig() {
        const res = await apiCall('/schools/read_bulletin.php');
        if (res.status === 'success' && res.data && res.data.bulletin_header) {
            document.getElementById('bulletin-header-editable').innerHTML = res.data.bulletin_header;
        } else {
            insertTemplate('header_classic');
        }
    }

    async function saveBulletinConfig() {
        const headerHtml = document.getElementById('bulletin-header-editable').innerHTML;
        const img = document.getElementById('bulletin-header-editable').querySelector('img');
        const res = await apiCall('/schools/update_bulletin.php', 'POST', {
            bulletin_header: headerHtml,
            logo_url: img ? img.src : schoolLogo
        });
        if (res.status === 'success') showAlert('success', 'Modèle enregistré');
    }

    async function loadEleves() {
        const res = await apiCall('/eleves/read.php');
        if (res.status === 'success') {
            const select = document.getElementById('eleve_select');
            res.data.forEach(e => {
                select.innerHTML += `<option value="${e.id}">${e.nom} ${e.prenom}</option>`;
            });
        }
    }

    async function handleLogoUpload(input) {
        if (!input.files || !input.files[0]) return;
        const formData = new FormData();
        formData.append('logo', input.files[0]);
        const response = await fetch('../api/schools/upload_logo.php', { method: 'POST', body: formData });
        const res = await response.json();
        if (res.status === 'success') {
            document.getElementById('bulletin-header-editable').focus();
            document.execCommand('insertImage', false, res.logo_url);
            schoolLogo = res.logo_url;
        }
    }

    function execCmd(command) { document.execCommand(command, false, null); }

    function insertTemplate(type) {
        const header = document.getElementById('bulletin-header-editable');
        const e = currentData ? currentData.eleve : {nom: "[NOM]", prenom: "[PRENOM]", classe_nom: "[CLASSE]"};
        let html = '';
        if(type === 'header_classic') {
            html = `<div style="display:flex; justify-content:space-between; border-bottom:2px solid #000; padding-bottom:10px;">
                        <img src="${schoolLogo}" style="max-height:80px;">
                        <div style="text-align:right;">
                            <h4 style="margin:0; font-weight:bold;">${'<?= $_SESSION['school_name'] ?>'.toUpperCase()}</h4>
                            <p style="margin:0; font-size:12px;">Année Scolaire : 2025-2026</p>
                        </div>
                    </div>
                    <div style="text-align:center; margin-top:20px;">
                        <h3 style="text-decoration:underline; font-weight:bold;">BULLETIN DE NOTES</h3>
                        <p>Élève : <b>${e.nom} ${e.prenom}</b> | Classe : <b>${e.classe_nom}</b></p>
                    </div>`;
        } else if(type === 'side_by_side') {
            html = `<div style="display:flex; justify-content:space-between; align-items:center;">
                        <div style="width:45%;"><img src="${schoolLogo}" style="max-height:60px;"><br><b>RÉPUBLIQUE DU BÉNIN</b></div>
                        <div style="width:45%; text-align:right; border-left:1px solid #ddd; padding-left:10px;">
                            <h4 style="color:#5c67f2;">FICHE DE RÉSULTATS</h4>
                            <p style="margin:0; font-size:12px;">${e.nom} ${e.prenom}<br>${e.classe_nom}</p>
                        </div>
                    </div>`;
        }
        header.innerHTML = html;
    }

    async function generateBulletin() {
        const eleveId = document.getElementById('eleve_select').value;
        if (!eleveId) return;
        document.getElementById('bulletin-zone').classList.remove('d-none');
        const res = await apiCall(`/bulletins/read.php?eleve_id=${eleveId}`);
        if (res.status === 'success') {
            currentData = res.data;
            const lines = currentData.lignes;
            let html = `<table class="table table-bordered border-dark text-center align-middle">
                            <thead class="table-light">
                                <tr><th>Matières</th><th>Moy.</th><th>Coef.</th><th>Total</th></tr>
                            </thead><tbody>`;
            lines.forEach(l => {
                html += `<tr><td class="text-start"><b>${l.matiere}</b></td><td>${l.moyenne}</td><td>${l.coefficient}</td><td>${(l.moyenne*l.coefficient).toFixed(2)}</td></tr>`;
            });
            html += `</tbody><tfoot class="table-light fw-bold">
                        <tr><td colspan="3" class="text-end">MOYENNE GÉNÉRALE :</td><td class="text-primary">${currentData.moyenne_generale} / 20</td></tr>
                    </tfoot></table>`;
            document.getElementById('bulletin-table-area').innerHTML = html;
            document.getElementById('btn-print').disabled = false;
            document.getElementById('btn-pdf').disabled = false;
        }
    }

    function downloadPDF() {
        const element = document.getElementById('bulletin-content');
        const opt = { margin: 10, filename: 'bulletin.pdf', image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2 }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' } };
        html2pdf().set(opt).from(element).save();
    }
</script>
