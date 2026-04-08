        <!-- Footer start -->
        <div class="footer">
            <div class="copyright">
                <p>Copyright &copy; SaaS École </p>
            </div>
        </div>
        <!-- Footer end -->
    </div>
    <!-- Main wrapper end -->

    <!-- Scripts Quixlab -->
    <script src="/saas/assets/plugins/common/common.min.js"></script>
    <script src="/saas/assets/js/custom.min.js"></script>
    <script src="/saas/assets/js/settings.js"></script>
    <script src="/saas/assets/js/gleek.js"></script>
    <script src="/saas/assets/js/styleSwitcher.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- API JS -->
    <script src="/saas/assets/js/api.js"></script>

    <script>
        // SÉCURITÉ : Forcer la fermeture du preloader si les scripts internes bloquent
        window.addEventListener('load', function() {
            setTimeout(function() {
                const preloader = document.getElementById('preloader');
                if (preloader) {
                    preloader.style.display = 'none';
                    preloader.style.opacity = '0';
                }
            }, 1000); // 1 seconde de sécurité
        });

        // Surbrillance du lien actif dans la sidebar metismenu
        const currentPath = window.location.pathname;
        const menuLinks = document.querySelectorAll('.nk-sidebar .metismenu a');
        
        menuLinks.forEach(link => {
            if(link.getAttribute('href') === currentPath) {
                link.closest('li').classList.add('active');
            }
        });
    </script>
</body>
</html>
