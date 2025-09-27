<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-sign-in-alt me-2"></i>Login to Your Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- OAuth Login Options -->
                <div class="mb-4">
                    <p class="text-center text-muted mb-3">Quick login with</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="loginWithLinkedIn()">
                            <i class="fab fa-linkedin me-2"></i>Continue with LinkedIn
                        </button>
                        <button type="button" class="btn btn-outline-dark" onclick="loginWithGoogle()">
                            <i class="fab fa-google me-2"></i>Continue with Google
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="loginWithGitHub()">
                            <i class="fab fa-github me-2"></i>Continue with GitHub
                        </button>
                    </div>
                </div>
                
                <div class="text-center mb-3">
                    <span class="text-muted">or</span>
                </div>
                
                <!-- Email Login Form -->
                <form id="loginForm">
                    <div class="mb-3">
                        <label for="loginUsername" class="form-label">Username or Email</label>
                        <input type="text" class="form-control" id="loginUsername" required>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="loginPassword" required>
                    </div>
                    <div class="mb-3">
                        <a href="#" onclick="showForgotPasswordModal()" class="text-decoration-none">
                            <small>Forgot your password?</small>
                        </a>
                    </div>
                    <div id="loginMessage" class="alert" style="display: none;"></div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
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
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Create Your Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- OAuth Registration Options -->
                <div class="mb-4">
                    <p class="text-center text-muted mb-3">Quick registration with</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="registerWithLinkedIn()">
                            <i class="fab fa-linkedin me-2"></i>Sign up with LinkedIn
                        </button>
                        <button type="button" class="btn btn-outline-dark" onclick="registerWithGoogle()">
                            <i class="fab fa-google me-2"></i>Sign up with Google
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="registerWithGitHub()">
                            <i class="fab fa-github me-2"></i>Sign up with GitHub
                        </button>
                    </div>
                </div>
                
                <div class="text-center mb-3">
                    <span class="text-muted">or</span>
                </div>
                
                <!-- Email Registration Form -->
                <form id="registerForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="registerFirstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="registerFirstName">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="registerLastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="registerLastName">
                            </div>
                        </div>
                    </div>
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
                        <div class="form-text">
                            <small>Password must be at least 8 characters long</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="registerTerms" required>
                            <label class="form-check-label" for="registerTerms">
                                I agree to the <a href="terms-of-service.php" target="_blank">Terms of Service</a> and <a href="privacy-policy.php" target="_blank">Privacy Policy</a>
                            </label>
                        </div>
                    </div>
                    <div id="registerMessage" class="alert" style="display: none;"></div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="fas fa-envelope me-2 text-primary"></i>Choose Your Subscription
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Login Required Message -->
                <div id="loginRequiredMessage" class="alert alert-info text-center mb-4">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    <strong>Login Required</strong> - Please <a href="#" onclick="showLoginModal()" class="alert-link">login</a> or <a href="#" onclick="showRegisterModal()" class="alert-link">register</a> to subscribe to our newsletter and premium content.
                </div>

                <!-- Subscription Plans -->
                <div class="row">
                    <!-- Newsletter Subscription -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-primary newsletter-plan">
                            <div class="card-header bg-primary text-white text-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-envelope me-2"></i>Newsletter
                                </h5>
                                <p class="mb-0 small">Stay updated with our content</p>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="text-center mb-3">
                                    <span class="h2 text-primary">FREE</span>
                                </div>
                                <ul class="list-unstyled mb-4 flex-grow-1">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Weekly newsletter
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Latest tutorials
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Community updates
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Free tutorial access
                                    </li>
                                </ul>
                                <button type="button" class="btn btn-outline-primary btn-lg w-100 subscription-btn" 
                                        data-subscription-type="newsletter" data-price="0">
                                    <i class="fas fa-envelope me-2"></i>Subscribe to Newsletter
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Premium Subscription -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-warning premium-plan position-relative">
                            <div class="card-header bg-warning text-dark text-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-crown me-2"></i>Premium
                                </h5>
                                <p class="mb-0 small">Full access to premium content</p>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="text-center mb-3">
                                    <span class="h2 text-warning">$0</span>
                                    <span class="text-muted">/month</span>
                                </div>
                                <ul class="list-unstyled mb-4 flex-grow-1">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        All newsletter benefits
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Premium tutorials
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Exclusive content
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Priority support
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Ad-free experience
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Early access to features
                                    </li>
                                </ul>
                                <button type="button" class="btn btn-warning btn-lg w-100 subscription-btn" 
                                        data-subscription-type="premium" data-price="0">
                                    <i class="fas fa-crown me-2"></i>Subscribe to Premium
                                </button>
                            </div>
                            <div class="position-absolute top-0 end-0 bg-success text-white px-2 py-1 rounded-start" style="margin-top: 15px;">
                                <small><i class="fas fa-star me-1"></i>Popular</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscription Form (Hidden initially) -->
                <div id="subscriptionFormContainer" style="display: none;">
                    <hr class="my-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 id="selectedPlanTitle" class="mb-3"></h6>
                            <form id="modalNewsletterForm">
                                <input type="hidden" id="selectedSubscriptionType" name="subscription_type" value="">
                                <input type="hidden" id="selectedPrice" name="price" value="">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="modalNewsletterName" class="form-label">Name (Optional)</label>
                                        <input type="text" class="form-control" id="modalNewsletterName" placeholder="Your name">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="modalNewsletterEmail" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="modalNewsletterEmail" placeholder="your@email.com" required readonly>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Newsletter Frequency</label>
                                <div class="btn-group w-100" role="group" aria-label="Frequency selection">
                                    <input type="radio" class="btn-check" name="newsletterFrequency" id="frequencyWeekly" value="weekly" checked>
                                    <label class="btn btn-outline-primary" for="frequencyWeekly">Weekly</label>
                                    
                                    <input type="radio" class="btn-check" name="newsletterFrequency" id="frequencyMonthly" value="monthly">
                                    <label class="btn btn-outline-primary" for="frequencyMonthly">Monthly</label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Content Preferences</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input newsletter-pref" type="checkbox" id="prefTutorials" value="new_tutorials" checked>
                                            <label class="form-check-label" for="prefTutorials">
                                                New Tutorials
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input newsletter-pref" type="checkbox" id="prefDigest" value="weekly_digest" checked>
                                            <label class="form-check-label" for="prefDigest">
                                                Weekly Digest
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input newsletter-pref" type="checkbox" id="prefNotifications" value="post_notifications">
                                            <label class="form-check-label" for="prefNotifications">
                                                Post Updates
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input newsletter-pref" type="checkbox" id="prefTrending" value="trending_content">
                                            <label class="form-check-label" for="prefTrending">
                                                Trending Content
                                            </label>
                                        </div>
                                    </div>
                                </div>
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

<!-- Unsubscribe Confirmation Modal -->
<div class="modal fade" id="unsubscribeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Unsubscribe
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to unsubscribe from the newsletter?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    This action will stop all newsletter emails. If you have a premium subscription, it will also revoke your premium access.
                </div>
                <div id="unsubscribeMessage" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="handleUnsubscribeConfirm()">
                    <i class="fas fa-unlink me-2"></i>Yes, Unsubscribe
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-key me-2"></i>Reset Your Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Enter your email address and we'll send you a link to reset your password.</p>
                <form id="forgotPasswordForm">
                    <div class="mb-3">
                        <label for="forgotPasswordEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="forgotPasswordEmail" required>
                    </div>
                    <div id="forgotPasswordMessage" class="alert" style="display: none;"></div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Email Verification Modal -->
<div class="modal fade" id="emailVerificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-envelope me-2 text-primary"></i>Verify Your Email
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-envelope-open-text fa-3x text-primary mb-3"></i>
                    <h5>Check Your Email</h5>
                    <p class="text-muted">We've sent a verification link to your email address.</p>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Didn't receive the email?</strong> Check your spam folder or 
                    <a href="#" onclick="resendVerificationEmail()" class="alert-link">click here to resend</a>.
                </div>
                <div id="verificationMessage" class="alert" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div> 