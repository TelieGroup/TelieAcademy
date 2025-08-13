<!-- Footer -->
<footer class="bg-dark text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>TelieAcademy</h5>
                <p class="footer-text-muted">Empowering developers and students with quality tech tutorials.</p>
                <div class="social-links">
                    <a href="#" class="footer-text-muted me-3" title="Follow us on Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="footer-text-muted me-3" title="Follow us on GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="#" class="footer-text-muted me-3" title="Follow us on LinkedIn">
                        <i class="fab fa-linkedin"></i>
                    </a>
                    <a href="#" class="footer-text-muted" title="Subscribe to our YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <h6>Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../index.php' : 'index.php'; ?>" class="footer-text-muted text-decoration-none">Home</a></li>
                    <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../categories.php' : 'categories.php'; ?>" class="footer-text-muted text-decoration-none">Categories</a></li>
                    <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../tags.php' : 'tags.php'; ?>" class="footer-text-muted text-decoration-none">Tags</a></li>
                    <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../posts.php' : 'posts.php'; ?>" class="footer-text-muted text-decoration-none">All Posts</a></li>
                    <li><a href="#" class="footer-text-muted text-decoration-none" data-bs-toggle="modal" data-bs-target="#newsletterModal">Newsletter</a></li>
                </ul>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0 footer-text-muted small">
                    &copy; <?php echo date('Y'); ?> TelieAcademy. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0 footer-text-muted small">
                    <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../privacy-policy.php' : 'privacy-policy.php'; ?>" class="footer-text-muted text-decoration-none">Privacy Policy</a> | 
                    <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../terms-of-service.php' : 'terms-of-service.php'; ?>" class="footer-text-muted text-decoration-none">Terms of Service</a> | 
                    <a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../contact-us.php' : 'contact-us.php'; ?>" class="footer-text-muted text-decoration-none">Contact Us</a>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JavaScript Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom Scripts -->
<?php include 'scripts.php'; ?> 