<?php
require_once 'config/session.php';
require_once 'includes/User.php';

$user = new User();
$isLoggedIn = $user->isLoggedIn();

// Set page variables for head component
$pageTitle = "Terms of Service - TelieAcademy";
$pageDescription = "Terms and conditions for using TelieAcademy's educational platform and services.";

include 'includes/head.php';
?>

<style>
/* Terms of Service Styling */
.terms-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 2rem 0;
}

.terms-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 3rem;
    margin-bottom: 2rem;
}

.terms-header {
    text-align: center;
    margin-bottom: 3rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid #e9ecef;
}

.terms-header h1 {
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 1rem;
}

.terms-header .last-updated {
    color: #6c757d;
    font-size: 1.1rem;
}

.terms-section {
    margin-bottom: 2.5rem;
}

.terms-section h2 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}

.terms-section h3 {
    color: #34495e;
    font-weight: 600;
    margin: 1.5rem 0 1rem 0;
}

.terms-section p {
    color: #555;
    line-height: 1.7;
    margin-bottom: 1rem;
    text-align: justify;
}

.terms-section ul, .terms-section ol {
    color: #555;
    line-height: 1.7;
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.terms-section li {
    margin-bottom: 0.5rem;
}

.terms-section .highlight {
    background: #fff3cd;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #ffc107;
    margin: 1.5rem 0;
}

.terms-section .important {
    background: #f8d7da;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #dc3545;
    margin: 1.5rem 0;
}

.contact-info {
    background: #e3f2fd;
    padding: 2rem;
    border-radius: 12px;
    text-align: center;
    margin-top: 3rem;
}

.contact-info h3 {
    color: #1976d2;
    margin-bottom: 1rem;
}

.contact-info p {
    color: #1565c0;
    margin-bottom: 0.5rem;
}

.contact-info a {
    color: #1976d2;
    text-decoration: none;
    font-weight: 500;
}

.contact-info a:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .terms-content {
        padding: 2rem 1.5rem;
        margin: 1rem;
    }
    
    .terms-header {
        margin-bottom: 2rem;
    }
    
    .terms-section h2 {
        font-size: 1.5rem;
    }
    
    .terms-section h3 {
        font-size: 1.25rem;
    }
}

@media (max-width: 576px) {
    .terms-content {
        padding: 1.5rem 1rem;
        margin: 0.5rem;
    }
    
    .terms-header h1 {
        font-size: 2rem;
    }
    
    .contact-info {
        padding: 1.5rem 1rem;
    }
}
</style>

<?php include 'includes/header.php'; ?>

<!-- Reading Progress Bar -->
<div class="reading-progress-bar" id="readingProgressBar"></div>

<div class="terms-container">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="terms-content">
                    <!-- Header -->
                    <div class="terms-header">
                        <h1>Terms of Service</h1>
                        <p class="last-updated">Last Updated: <?php echo date('F j, Y'); ?></p>
                    </div>

                    <!-- Introduction -->
                    <div class="terms-section">
                        <p>Welcome to TelieAcademy! These Terms of Service ("Terms") govern your use of our website, mobile applications, and services (collectively, the "Service") operated by TelieAcademy ("we," "us," or "our").</p>
                        
                        <p>By accessing or using our Service, you agree to be bound by these Terms. If you disagree with any part of these terms, then you may not access the Service.</p>
                        
                        <div class="highlight">
                            <strong>Important:</strong> Please read these Terms carefully before using our Service. Your continued use of the Service constitutes acceptance of these Terms.
                        </div>
                    </div>

                    <!-- Acceptance of Terms -->
                    <div class="terms-section">
                        <h2>1. Acceptance of Terms</h2>
                        <p>By accessing or using TelieAcademy, you confirm that you have read, understood, and agree to be bound by these Terms of Service. If you are using our Service on behalf of an organization, you represent that you have the authority to bind that organization to these Terms.</p>
                        
                        <p>We reserve the right to modify these Terms at any time. We will notify users of any material changes by posting the new Terms on this page and updating the "Last Updated" date. Your continued use of the Service after such modifications constitutes acceptance of the updated Terms.</p>
                    </div>

                    <!-- Description of Service -->
                    <div class="terms-section">
                        <h2>2. Description of Service</h2>
                        <p>TelieAcademy is an educational platform that provides:</p>
                        <ul>
                            <li>Educational content, tutorials, and learning materials</li>
                            <li>Course materials including PDFs, presentations, and study guides</li>
                            <li>Community features for students and developers</li>
                            <li>Premium subscription services with enhanced access</li>
                            <li>Newsletter and communication services</li>
                        </ul>
                        
                        <p>We reserve the right to modify, suspend, or discontinue any part of our Service at any time with or without notice.</p>
                    </div>

                    <!-- User Accounts -->
                    <div class="terms-section">
                        <h2>3. User Accounts</h2>
                        <h3>3.1 Account Creation</h3>
                        <p>To access certain features of our Service, you may be required to create an account. You agree to provide accurate, current, and complete information during registration and to update such information to keep it accurate, current, and complete.</p>
                        
                        <h3>3.2 Account Security</h3>
                        <p>You are responsible for safeguarding the password and for all activities that occur under your account. You agree to notify us immediately of any unauthorized use of your account or any other breach of security.</p>
                        
                        <h3>3.3 Account Termination</h3>
                        <p>We reserve the right to terminate or suspend your account at any time for violations of these Terms or for any other reason at our sole discretion.</p>
                    </div>

                    <!-- Premium Services -->
                    <div class="terms-section">
                        <h2>4. Premium Services</h2>
                        <h3>4.1 Subscription Plans</h3>
                        <p>We offer premium subscription plans that provide enhanced access to our educational content and materials. Subscription fees are billed in advance on a recurring basis.</p>
                        
                        <h3>4.2 Payment Terms</h3>
                        <p>All subscription fees are non-refundable except as required by law. We reserve the right to change subscription fees upon 30 days' notice to subscribers.</p>
                        
                        <h3>4.3 Cancellation</h3>
                        <p>You may cancel your subscription at any time through your account settings. Cancellation will take effect at the end of your current billing period.</p>
                        
                        <div class="important">
                            <strong>Note:</strong> Premium content access will be revoked immediately upon subscription cancellation.
                        </div>
                    </div>

                    <!-- Acceptable Use -->
                    <div class="terms-section">
                        <h2>5. Acceptable Use</h2>
                        <p>You agree to use our Service only for lawful purposes and in accordance with these Terms. You agree not to:</p>
                        <ul>
                            <li>Use the Service for any illegal or unauthorized purpose</li>
                            <li>Violate any applicable laws or regulations</li>
                            <li>Infringe upon the rights of others</li>
                            <li>Attempt to gain unauthorized access to our systems</li>
                            <li>Interfere with or disrupt the Service</li>
                            <li>Share your account credentials with others</li>
                            <li>Use automated systems to access the Service</li>
                            <li>Distribute or reproduce our content without permission</li>
                        </ul>
                    </div>

                    <!-- Intellectual Property -->
                    <div class="terms-section">
                        <h2>6. Intellectual Property</h2>
                        <h3>6.1 Our Content</h3>
                        <p>The Service and its original content, features, and functionality are owned by TelieAcademy and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.</p>
                        
                        <h3>6.2 User Content</h3>
                        <p>You retain ownership of any content you submit to our Service. By submitting content, you grant us a worldwide, non-exclusive, royalty-free license to use, reproduce, modify, and distribute your content in connection with our Service.</p>
                        
                        <h3>6.3 Course Materials</h3>
                        <p>Course materials provided through our Service are for educational purposes only. You may not redistribute, sell, or commercialize these materials without explicit permission.</p>
                    </div>

                    <!-- Privacy and Data -->
                    <div class="terms-section">
                        <h2>7. Privacy and Data Protection</h2>
                        <p>Your privacy is important to us. Our collection and use of personal information is governed by our Privacy Policy, which is incorporated into these Terms by reference.</p>
                        
                        <p>By using our Service, you consent to the collection and use of information as outlined in our Privacy Policy.</p>
                    </div>

                    <!-- Disclaimers -->
                    <div class="terms-section">
                        <h2>8. Disclaimers</h2>
                        <h3>8.1 Service Availability</h3>
                        <p>We strive to provide a reliable and uninterrupted Service, but we do not guarantee that the Service will be available at all times or that it will be error-free.</p>
                        
                        <h3>8.2 Educational Content</h3>
                        <p>While we strive for accuracy, our educational content is provided "as is" without warranties of any kind. We are not responsible for any errors or omissions in our content.</p>
                        
                        <h3>8.3 Third-Party Services</h3>
                        <p>Our Service may contain links to third-party websites or services. We are not responsible for the content or practices of any third-party services.</p>
                    </div>

                    <!-- Limitation of Liability -->
                    <div class="terms-section">
                        <h2>9. Limitation of Liability</h2>
                        <p>To the maximum extent permitted by law, TelieAcademy shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses.</p>
                        
                        <p>Our total liability to you for any claims arising from the use of our Service shall not exceed the amount you paid us in the twelve (12) months preceding the claim.</p>
                    </div>

                    <!-- Indemnification -->
                    <div class="terms-section">
                        <h2>10. Indemnification</h2>
                        <p>You agree to defend, indemnify, and hold harmless TelieAcademy and its officers, directors, employees, and agents from and against any claims, damages, obligations, losses, liabilities, costs, or debt arising from your use of the Service or violation of these Terms.</p>
                    </div>

                    <!-- Governing Law -->
                    <div class="terms-section">
                        <h2>11. Governing Law</h2>
                        <p>These Terms shall be governed by and construed in accordance with the laws of [Your Jurisdiction], without regard to its conflict of law provisions.</p>
                        
                        <p>Any disputes arising from these Terms or your use of the Service shall be resolved through binding arbitration in accordance with the rules of [Arbitration Organization].</p>
                    </div>

                    <!-- Changes to Terms -->
                    <div class="terms-section">
                        <h2>12. Changes to Terms</h2>
                        <p>We reserve the right to modify or replace these Terms at any time. If a revision is material, we will provide at least 30 days' notice prior to any new terms taking effect.</p>
                        
                        <p>What constitutes a material change will be determined at our sole discretion. By continuing to access or use our Service after those revisions become effective, you agree to be bound by the revised terms.</p>
                    </div>

                    <!-- Contact Information -->
                    <div class="contact-info">
                        <h3>Questions About These Terms?</h3>
                        <p>If you have any questions about these Terms of Service, please contact us:</p>
                        <p><strong>Email:</strong> <a href="mailto:legal@telieacademy.com">legal@telieacademy.com</a></p>
                        <p><strong>Address:</strong> [Your Business Address]</p>
                        <p><strong>Phone:</strong> [Your Phone Number]</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

