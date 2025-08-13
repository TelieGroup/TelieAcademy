<?php
require_once 'config/session.php';
require_once 'includes/User.php';

$user = new User();
$isLoggedIn = $user->isLoggedIn();

$pageTitle = "Privacy Policy - TelieAcademy";
$pageDescription = "Privacy policy explaining how TelieAcademy collects, uses, and protects your personal information.";

include 'includes/head.php';
?>

<style>
.privacy-container { background: #f8f9fa; min-height: 100vh; padding: 2rem 0; }
.privacy-content { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 3rem; margin-bottom: 2rem; }
.privacy-header { text-align: center; margin-bottom: 3rem; padding-bottom: 2rem; border-bottom: 2px solid #e9ecef; }
.privacy-header h1 { color: #2c3e50; font-weight: 700; margin-bottom: 1rem; }
.privacy-header .last-updated { color: #6c757d; font-size: 1.1rem; }
.privacy-section { margin-bottom: 2.5rem; }
.privacy-section h2 { color: #2c3e50; font-weight: 600; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e9ecef; }
.privacy-section h3 { color: #34495e; font-weight: 600; margin: 1.5rem 0 1rem 0; }
.privacy-section p { color: #555; line-height: 1.7; margin-bottom: 1rem; text-align: justify; }
.privacy-section ul, .privacy-section ol { color: #555; line-height: 1.7; margin-bottom: 1rem; padding-left: 2rem; }
.privacy-section li { margin-bottom: 0.5rem; }
.privacy-section .highlight { background: #fff3cd; padding: 1rem; border-radius: 8px; border-left: 4px solid #ffc107; margin: 1.5rem 0; }
.privacy-section .important { background: #f8d7da; padding: 1rem; border-radius: 8px; border-left: 4px solid #dc3545; margin: 1.5rem 0; }
.privacy-section .info-box { background: #e3f2fd; padding: 1rem; border-radius: 8px; border-left: 4px solid #2196f3; margin: 1.5rem 0; }
.contact-info { background: #e3f2fd; padding: 2rem; border-radius: 12px; text-align: center; margin-top: 3rem; }
.contact-info h3 { color: #1976d2; margin-bottom: 1rem; }
.contact-info p { color: #1565c0; margin-bottom: 0.5rem; }
.contact-info a { color: #1976d2; text-decoration: none; font-weight: 500; }
.contact-info a:hover { text-decoration: underline; }

@media (max-width: 768px) {
    .privacy-content { padding: 2rem 1.5rem; margin: 1rem; }
    .privacy-header { margin-bottom: 2rem; }
    .privacy-section h2 { font-size: 1.5rem; }
    .privacy-section h3 { font-size: 1.25rem; }
}
@media (max-width: 576px) {
    .privacy-content { padding: 1.5rem 1rem; margin: 0.5rem; }
    .privacy-header h1 { font-size: 2rem; }
    .contact-info { padding: 1.5rem 1rem; }
}
</style>

<?php include 'includes/header.php'; ?>

<div class="reading-progress-bar" id="readingProgressBar"></div>

<div class="privacy-container">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="privacy-content">
                    <div class="privacy-header">
                        <h1>Privacy Policy</h1>
                        <p class="last-updated">Last Updated: <?php echo date('F j, Y'); ?></p>
                    </div>

                    <div class="privacy-section">
                        <p>At TelieAcademy, we are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our educational platform and services.</p>
                        
                        <div class="highlight">
                            <strong>Important:</strong> This Privacy Policy applies to all users of TelieAcademy, including visitors, registered users, and premium subscribers.
                        </div>
                    </div>

                    <div class="privacy-section">
                        <h2>1. Information We Collect</h2>
                        
                        <h3>1.1 Personal Information</h3>
                        <p>We may collect the following personal information when you use our Service:</p>
                        <ul>
                            <li><strong>Account Information:</strong> Name, email address, username, password, and profile picture</li>
                            <li><strong>Profile Information:</strong> Educational background, interests, and preferences</li>
                            <li><strong>Payment Information:</strong> Billing address, payment method details (processed securely by third-party payment processors)</li>
                            <li><strong>Communication Data:</strong> Messages, comments, and feedback you provide</li>
                        </ul>
                        
                        <h3>1.2 Usage Information</h3>
                        <p>We automatically collect certain information about your use of our Service:</p>
                        <ul>
                            <li><strong>Device Information:</strong> IP address, browser type, operating system, and device identifiers</li>
                            <li><strong>Usage Data:</strong> Pages visited, time spent on pages, links clicked, and features used</li>
                            <li><strong>Course Progress:</strong> Materials accessed, download history, and learning preferences</li>
                            <li><strong>Technical Data:</strong> Error logs, performance data, and system information</li>
                        </ul>
                    </div>

                    <div class="privacy-section">
                        <h2>2. How We Use Your Information</h2>
                        <p>We use the collected information for the following purposes:</p>
                        
                        <h3>2.1 Service Provision</h3>
                        <ul>
                            <li>Provide and maintain our educational platform</li>
                            <li>Process registrations and manage user accounts</li>
                            <li>Deliver course materials and educational content</li>
                            <li>Process payments and manage subscriptions</li>
                            <li>Provide customer support and respond to inquiries</li>
                        </ul>
                        
                        <h3>2.2 Personalization and Improvement</h3>
                        <ul>
                            <li>Personalize your learning experience</li>
                            <li>Recommend relevant courses and materials</li>
                            <li>Improve our platform and services</li>
                            <li>Analyze usage patterns and trends</li>
                        </ul>
                    </div>

                    <div class="privacy-section">
                        <h2>3. Information Sharing and Disclosure</h2>
                        <p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except in the following circumstances:</p>
                        
                        <h3>3.1 Service Providers</h3>
                        <p>We may share information with trusted third-party service providers who assist us in operating our platform, including payment processors, cloud hosting providers, and analytics services.</p>
                        
                        <h3>3.2 Legal Requirements</h3>
                        <p>We may disclose your information if required by law or in response to valid legal requests from government authorities, court orders, or to protect our rights and property.</p>
                        
                        <div class="important">
                            <strong>Note:</strong> We will never share your personal information with third parties for marketing purposes without your explicit consent.
                        </div>
                    </div>

                    <div class="privacy-section">
                        <h2>4. Data Security</h2>
                        <p>We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>
                        
                        <h3>4.1 Security Measures</h3>
                        <ul>
                            <li>Encryption of data in transit and at rest</li>
                            <li>Regular security assessments and updates</li>
                            <li>Access controls and authentication systems</li>
                            <li>Secure data centers and infrastructure</li>
                        </ul>
                        
                        <div class="info-box">
                            <strong>Security Tip:</strong> You can help protect your account by using a strong password and keeping your login credentials secure.
                        </div>
                    </div>

                    <div class="privacy-section">
                        <h2>5. Your Rights and Choices</h2>
                        <p>You have certain rights regarding your personal information:</p>
                        
                        <h3>5.1 Access and Control</h3>
                        <ul>
                            <li><strong>Access:</strong> Request a copy of your personal information</li>
                            <li><strong>Update:</strong> Correct or update your information</li>
                            <li><strong>Delete:</strong> Request deletion of your account and data</li>
                            <li><strong>Portability:</strong> Request a copy of your data in a portable format</li>
                        </ul>
                        
                        <h3>5.2 Communication Preferences</h3>
                        <ul>
                            <li>Opt out of marketing communications</li>
                            <li>Manage newsletter subscription preferences</li>
                            <li>Control notification settings</li>
                        </ul>
                    </div>

                    <div class="privacy-section">
                        <h2>6. Children's Privacy</h2>
                        <p>Our Service is not intended for children under the age of 13. We do not knowingly collect personal information from children under 13.</p>
                        
                        <div class="highlight">
                            <strong>Age Requirement:</strong> Users must be at least 13 years old to create an account and use our Service.
                        </div>
                    </div>

                    <div class="privacy-section">
                        <h2>7. Third-Party Services</h2>
                        <p>Our Service may contain links to third-party websites or integrate with third-party services. This Privacy Policy does not apply to those third-party services.</p>
                        
                        <h3>7.1 Third-Party Integrations</h3>
                        <p>We may use third-party services for payment processing, analytics, marketing tools, and social media integration. These services have their own privacy policies and data practices.</p>
                    </div>

                    <div class="privacy-section">
                        <h2>8. Changes to This Policy</h2>
                        <p>We may update this Privacy Policy from time to time to reflect changes in our practices or applicable laws. We will notify you of any material changes by posting the updated policy on our website and updating the "Last Updated" date.</p>
                        
                        <p>Your continued use of our Service after such changes constitutes acceptance of the updated Privacy Policy.</p>
                    </div>

                    <div class="contact-info">
                        <h3>Questions About Your Privacy?</h3>
                        <p>If you have any questions about this Privacy Policy or our data practices, please contact us:</p>
                        <p><strong>Email:</strong> <a href="mailto:privacy@telieacademy.com">privacy@telieacademy.com</a></p>
                        <p><strong>Data Protection Officer:</strong> <a href="mailto:dpo@telieacademy.com">dpo@telieacademy.com</a></p>
                        <p><strong>Address:</strong> [Your Business Address]</p>
                        <p><strong>Phone:</strong> [Your Phone Number]</p>
                        
                        <div class="mt-3">
                            <p><strong>Response Time:</strong> We aim to respond to all privacy-related inquiries within 30 days.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
