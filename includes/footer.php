            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Network Monitor &copy; 2026</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="sb_admin_theme/vendor/jquery/jquery.min.js"></script>
    <script src="sb_admin_theme/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="sb_admin_theme/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="sb_admin_theme/js/sb-admin-2.min.js"></script>

    <!-- Theme Toggle Script -->
    <script>
        // Theme Toggle Functionality
        (function() {
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            const themeIcon = themeToggle.querySelector('i');
            
            // Check for saved theme preference or default to light mode
            const currentTheme = localStorage.getItem('theme') || 'light';
            
            // Apply the saved theme on page load
            if (currentTheme === 'dark') {
                body.classList.add('dark-mode');
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
                themeToggle.setAttribute('title', 'Toggle Light Mode');
            }
            
            // Toggle theme on button click
            themeToggle.addEventListener('click', function() {
                body.classList.toggle('dark-mode');
                
                // Update icon and save preference
                if (body.classList.contains('dark-mode')) {
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');
                    themeToggle.setAttribute('title', 'Toggle Light Mode');
                    localStorage.setItem('theme', 'dark');
                } else {
                    themeIcon.classList.remove('fa-sun');
                    themeIcon.classList.add('fa-moon');
                    themeToggle.setAttribute('title', 'Toggle Dark Mode');
                    localStorage.setItem('theme', 'light');
                }
            });
        })();
    </script>

    <?php if (isset($page_scripts)): ?>
        <!-- Page level plugins/scripts -->
        <?php echo $page_scripts; ?>
    <?php endif; ?>

</body>

</html>
