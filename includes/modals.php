<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm">
                    <div class="mb-3">
                        <label for="loginUsername" class="form-label">Username or Email</label>
                        <input type="text" class="form-control" id="loginUsername" required>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="loginPassword" required>
                    </div>
                    <div id="loginMessage" class="alert" style="display: none;"></div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <hr>
                <p class="text-center mb-0">
                    Don't have an account? 
                    <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Register</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="registerForm">
                    <div class="mb-3">
                        <label for="registerUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="registerUsername" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="registerEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="registerPassword" required>
                    </div>
                    <div id="registerMessage" class="alert" style="display: none;"></div>
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>
                <hr>
                <p class="text-center mb-0">
                    Already have an account? 
                    <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Newsletter Modal -->
<div class="modal fade" id="newsletterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-envelope me-2"></i>Subscribe to Our Newsletter
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="text-primary mb-3">Stay Updated with Latest Tutorials</h6>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Get notified about new tutorials and courses
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Receive exclusive tips and tricks
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Access to premium content previews
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Weekly roundup of best tutorials
                            </li>
                        </ul>
                        <form id="modalNewsletterForm">
                            <div class="mb-3">
                                <label for="modalNewsletterEmail" class="form-label">Email Address</label>
                                <input type="email" class="form-control form-control-lg" id="modalNewsletterEmail" placeholder="your@email.com" required>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newsletterConsent" required>
                                    <label class="form-check-label" for="newsletterConsent">
                                        I agree to receive email updates and newsletters
                                    </label>
                                </div>
                            </div>
                            <div id="modalNewsletterMessage" class="alert" style="display: none;"></div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-paper-plane me-2"></i>Subscribe Now
                            </button>
                        </form>
                        <div id="modalSubscribedContent" style="display: none;"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <i class="fas fa-envelope-open-text fa-4x text-primary mb-3"></i>
                            <h6 class="text-muted">Join Our Community</h6>
                            <p class="text-muted small">
                                Be part of our growing community of developers and learners.
                            </p>
                            <div class="bg-light p-3 rounded">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    We respect your privacy. Unsubscribe anytime.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 