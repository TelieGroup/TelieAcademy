<?php
require_once 'config/session.php';
require_once 'includes/User.php';

$user = new User();
$isLoggedIn = $user->isLoggedIn();

$pageTitle = "Contact Us - TelieAcademy";
$pageDescription = "Get in touch with TelieAcademy. We're here to help with your questions, feedback, and support needs.";

include 'includes/head.php';
?>

<style>
.contact-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 2rem 0;
}

.contact-header {
    text-align: center;
    margin-bottom: 3rem;
}

.contact-header h1 {
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 1rem;
}

.contact-header p {
    color: #6c757d;
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
}

.contact-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 3rem;
    margin-bottom: 2rem;
}

.contact-form-section {
    margin-bottom: 3rem;
}

.contact-form-section h2 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.contact-form-section h3 {
    color: #34495e;
    font-weight: 600;
    margin: 1.5rem 0 1rem 0;
}

.contact-form-section p {
    color: #555;
    line-height: 1.7;
    margin-bottom: 1rem;
}

.contact-form-section ul {
    color: #555;
    line-height: 1.7;
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.contact-form-section li {
    margin-bottom: 0.5rem;
}

.contact-form .form-label {
    font-weight: 600;
    color: #2c3e50;
}

.contact-form .form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem;
    transition: all 0.3s ease;
}

.contact-form .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.contact-form .form-select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem;
    transition: all 0.3s ease;
}

.contact-form .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.contact-form textarea.form-control {
    min-height: 120px;
    resize: vertical;
}

.contact-form .btn-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border: none;
    border-radius: 8px;
    padding: 0.75rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.contact-form .btn-primary:hover {
    background: linear-gradient(45deg, #0056b3, #004085);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,123,255,0.3);
}

.contact-info-section {
    background: #e3f2fd;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.contact-info-section h3 {
    color: #1976d2;
    margin-bottom: 1.5rem;
    text-align: center;
}

.contact-method {
    text-align: center;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.contact-method:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.contact-method .icon {
    font-size: 2.5rem;
    color: #1976d2;
    margin-bottom: 1rem;
}

.contact-method h4 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.contact-method p {
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.contact-method a {
    color: #1976d2;
    text-decoration: none;
    font-weight: 500;
}

.contact-method a:hover {
    text-decoration: underline;
}

.faq-section {
    margin-top: 3rem;
}

.faq-section h2 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}

.faq-item {
    margin-bottom: 1.5rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.faq-item h4 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.faq-item p {
    color: #555;
    margin-bottom: 0;
}

.alert {
    border-radius: 8px;
    border: none;
}

.alert-success {
    background: #d4edda;
    color: #155724;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border-left: 4px solid #17a2b8;
}

.form-control-modified {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
}

.form-control-modified:focus {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .contact-content {
        padding: 2rem 1.5rem;
        margin: 1rem;
    }
    
    .contact-header {
        margin-bottom: 2rem;
    }
    
    .contact-form-section h2 {
        font-size: 1.5rem;
    }
    
    .contact-form-section h3 {
        font-size: 1.25rem;
    }
    
    .contact-method {
        margin-bottom: 1.5rem;
        padding: 1rem;
    }
}

@media (max-width: 576px) {
    .contact-content {
        padding: 1.5rem 1rem;
        margin: 0.5rem;
    }
    
    .contact-header h1 {
        font-size: 2rem;
    }
    
    .contact-method .icon {
        font-size: 2rem;
    }
}
</style>

<?php include 'includes/header.php'; ?>

<div class="contact-container">
    <div class="container">
        <!-- Header -->
        <div class="contact-header">
            <h1>Contact Us</h1>
            <p>Have questions, feedback, or need support? We're here to help! Reach out to us through any of the methods below.</p>
            <?php if ($isLoggedIn): ?>
                <div class="mt-3">
                    <a href="my-messages" class="btn btn-outline-primary">
                        <i class="fas fa-envelope me-2"></i>View My Messages
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Contact Form -->
                <div class="contact-content">
                    <div class="contact-form-section">
                        <h2>Send Us a Message</h2>
                        <p>Fill out the form below and we'll get back to you as soon as possible. We typically respond within 24-48 hours.</p>
                        
                        <?php if ($isLoggedIn): ?>
                            <div class="alert alert-info d-flex align-items-center">
                                <div class="me-3">
                                    <?php if (!empty($currentUser['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($currentUser['profile_picture']); ?>" 
                                             alt="Profile Picture" 
                                             class="rounded-circle" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle fa-2x text-info"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <strong>Welcome back, <?php echo htmlspecialchars($currentUser['first_name'] ?? $currentUser['username'] ?? 'User'); ?>!</strong><br>
                                    <small>Your contact information has been pre-filled for your convenience. You can modify any fields if needed.</small>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form id="contactForm" class="contact-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contactFirstName" class="form-label">First Name *</label>
                                                        <input type="text" class="form-control" id="contactFirstName" name="first_name" 
                       value="<?php echo $isLoggedIn && !empty($currentUser['first_name']) ? htmlspecialchars($currentUser['first_name']) : ''; ?>" 
                       required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contactLastName" class="form-label">Last Name *</label>
                                                        <input type="text" class="form-control" id="contactLastName" name="last_name" 
                       value="<?php echo $isLoggedIn && !empty($currentUser['last_name']) ? htmlspecialchars($currentUser['last_name']) : ''; ?>" 
                       required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contactEmail" class="form-label">Email Address *</label>
                                                        <input type="email" class="form-control" id="contactEmail" name="email" 
                       value="<?php echo $isLoggedIn && !empty($currentUser['email']) ? htmlspecialchars($currentUser['email']) : ''; ?>" 
                       required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contactPhone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="contactPhone" name="phone" 
                                               value="<?php echo $isLoggedIn && !empty($currentUser['phone']) ? htmlspecialchars($currentUser['phone']) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="contactSubject" class="form-label">Subject *</label>
                                <select class="form-select" id="contactSubject" name="subject" required>
                                    <option value="">Select a subject</option>
                                    <option value="general">General Inquiry</option>
                                    <option value="technical">Technical Support</option>
                                    <option value="billing">Billing & Subscription</option>
                                    <option value="course">Course Content</option>
                                    <option value="partnership">Partnership & Collaboration</option>
                                    <option value="feedback">Feedback & Suggestions</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="contactMessage" class="form-label">Message *</label>
                                <textarea class="form-control" id="contactMessage" name="message" placeholder="Please describe your inquiry in detail..." required></textarea>
                                <div class="form-text">
                                    <small>Please provide as much detail as possible to help us assist you better.</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="contactNewsletter" name="newsletter_subscribe">
                                    <label class="form-check-label" for="contactNewsletter">
                                        Subscribe to our newsletter for updates and educational content
                                    </label>
                                </div>
                            </div>
                            
                            <div id="contactAlert" class="alert" style="display: none;"></div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Contact Information -->
                <div class="contact-info-section">
                    <h3>Get in Touch</h3>
                    
                    <div class="contact-method">
                        <div class="icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Email Us</h4>
                        <p>For general inquiries and support</p>
                        <a href="mailto:info@telieacademy.com">info@telieacademy.com</a>
                    </div>
                    
                    <div class="contact-method">
                        <div class="icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>Customer Support</h4>
                        <p>For technical issues and help</p>
                        <a href="mailto:support@telieacademy.com">support@telieacademy.com</a>
                    </div>
                    
                    <div class="contact-method">
                        <div class="icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h4>Educational Content</h4>
                        <p>For course-related questions</p>
                        <a href="mailto:education@telieacademy.com">education@telieacademy.com</a>
                    </div>
                    
                    <div class="contact-method">
                        <div class="icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4>Partnerships</h4>
                        <p>For business collaborations</p>
                        <a href="mailto:partnerships@telieacademy.com">partnerships@telieacademy.com</a>
                    </div>
                </div>
                
                <!-- Business Hours -->
                <div class="contact-content">
                    <div class="contact-form-section">
                        <h3>Business Hours</h3>
                        <ul class="list-unstyled">
                            <li><strong>Monday - Friday:</strong> 9:00 AM - 6:00 PM EST</li>
                            <li><strong>Saturday:</strong> 10:00 AM - 4:00 PM EST</li>
                            <li><strong>Sunday:</strong> Closed</li>
                        </ul>
                        
                        <div class="mt-3">
                            <p class="text-muted small">
                                <i class="fas fa-clock me-1"></i>
                                We aim to respond to all inquiries within 24-48 hours during business days.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="contact-content">
            <div class="faq-section">
                <h2>Frequently Asked Questions</h2>
                
                <div class="faq-item">
                    <h4>How do I access premium course materials?</h4>
                    <p>Premium course materials are available to users with active premium subscriptions. You can upgrade your account through your profile settings or contact our support team for assistance.</p>
                </div>
                
                <div class="faq-item">
                    <h4>What payment methods do you accept?</h4>
                    <p>We accept all major credit cards, PayPal, and other secure payment methods. All payments are processed securely through our trusted payment partners.</p>
                </div>
                
                <div class="faq-item">
                    <h4>Can I cancel my subscription anytime?</h4>
                    <p>Yes, you can cancel your subscription at any time through your account settings. Your premium access will continue until the end of your current billing period.</p>
                </div>
                
                <div class="faq-item">
                    <h4>How do I report a technical issue?</h4>
                    <p>For technical issues, please email us at support@telieacademy.com with a detailed description of the problem, including your browser, device, and steps to reproduce the issue.</p>
                </div>
                
                <div class="faq-item">
                    <h4>Do you offer refunds?</h4>
                    <p>We offer refunds within 30 days of purchase for first-time subscribers. Please contact our support team if you believe you're eligible for a refund.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    const contactAlert = document.getElementById('contactAlert');
    
    // Check if user is logged in and form is pre-filled
    const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
    const hasPreFilledData = isLoggedIn && (
        document.getElementById('contactFirstName').value ||
        document.getElementById('contactEmail').value
    );
    
    // Show different message based on login status
    if (isLoggedIn && hasPreFilledData) {
        console.log('Form pre-filled with user data');
    }
    
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(contactForm);
        const data = Object.fromEntries(formData);
        
        // Show loading state
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        submitBtn.disabled = true;
        
        // Send form data via AJAX
        fetch('submit_contact', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Show success message
                contactAlert.className = 'alert alert-success';
                let successMessage = '<i class="fas fa-check-circle me-2"></i>Thank you! Your message has been sent successfully. We\'ll get back to you within 24-48 hours.';
                
                if (isLoggedIn) {
                    successMessage += '<br><small class="text-muted">Your message was sent from your registered account.</small>';
                    successMessage += '<br><br><a href="my-messages" class="btn btn-sm btn-outline-success">View My Messages</a>';
                }
                
                contactAlert.innerHTML = successMessage;
                contactAlert.style.display = 'block';
                
                // Reset form for all users
                contactForm.reset();
                
                // Scroll to message
                contactMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                // Show error message
                contactAlert.className = 'alert alert-danger';
                contactAlert.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Error: ' + (result.message || 'Failed to send message. Please try again.');
                contactAlert.style.display = 'block';
                
                // Scroll to message
                contactAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error message
            contactAlert.className = 'alert alert-danger';
            contactAlert.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Error: Failed to send message. Please try again.';
            contactAlert.style.display = 'block';
            
            // Scroll to message
            contactAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        })
        .finally(() => {
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            
            // Hide message after 5 seconds
            setTimeout(() => {
                contactAlert.style.display = 'none';
            }, 5000);
        });
    });
    
    // Form is now cleared for all users after successful submission
});
</script>

<?php include 'includes/footer.php'; ?>
