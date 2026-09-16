    </div> <!-- End container -->
    <footer class="mt-5 py-6 border-top" style="background: var(--card-bg);">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-4 mb-md-0">
                    <span class="fw-bold text-primary fs-5"><i class="bi bi-rocket-takeoff-fill me-2"></i>KoKCS Online Assessment Portal</span>
                    <p class="text-muted small mb-0 mt-3">&copy; <?= date('Y') ?> King of Kings College School. Empowering minds through technology.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="d-flex justify-content-center justify-content-md-end gap-4">
                        <a href="#" class="text-muted text-decoration-none small fw-semibold hover-primary transition-all">Privacy Policy</a>
                        <a href="#" class="text-muted text-decoration-none small fw-semibold hover-primary transition-all">Terms of Service</a>
                        <a href="#" class="text-muted text-decoration-none small fw-semibold hover-primary transition-all">Contact Support</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars(appBasePath() . '/assets/js/script.js') ?>"></script>
</body>
</html>