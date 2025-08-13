// TechTutorial Blog JavaScript

// Global variables
let isLoggedIn = false;
let currentUser = null;

// Dark Mode Toggle
document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;
    
    // Check for saved dark mode preference
    const savedMode = localStorage.getItem('darkMode');
    if (savedMode === 'dark') {
        body.classList.add('dark-mode');
        if (darkModeToggle) {
            updateDarkModeIcon(true);
        }
    }
    
    // Dark mode toggle functionality
    if (darkModeToggle) {
        console.log('Dark mode toggle button found and initialized');
        darkModeToggle.addEventListener('click', function() {
            console.log('Dark mode toggle clicked');
            body.classList.toggle('dark-mode');
            const isDarkMode = body.classList.contains('dark-mode');
            
            // Save preference to localStorage
            localStorage.setItem('darkMode', isDarkMode ? 'dark' : 'light');
            
            // Update icon
            updateDarkModeIcon(isDarkMode);
            
            // Add loading animation to cards
            animateCards();
        });
        
        function updateDarkModeIcon(isDark) {
            const icon = darkModeToggle.querySelector('i');
            if (icon) {
                if (isDark) {
                    icon.className = 'fas fa-sun';
                } else {
                    icon.className = 'fas fa-moon';
                }
            }
        }
    } else {
        console.log('Dark mode toggle button not found');
    }
    
    function animateCards() {
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('loading');
            }, index * 100);
        });
    }
    
    // Initialize banner ads
    initializeBannerAds();
    simulateAdLoading();
    
    // Initialize authentication
    initializeAuth();
    
    // Initialize newsletter forms
    initializeNewsletterForms();
    
    // Initialize comment forms
    initializeCommentForms();
    
    // Initialize voting functionality
    initializeVoting();
    
    // Initialize bookmark functionality
    initializeBookmarks();
    
    // Initialize newsletter modal
    initializeNewsletterModal();
    
    // Initialize search functionality
    initializeSearch();
    
    // Initialize back to top button
    initializeBackToTop();
    
    // Debug voting system - can be removed later
    // console.log('Page loaded - Auth Status:', {
    //     isLoggedIn: isLoggedIn,
    //     currentUser: currentUser,
    //     voteButtons: document.querySelectorAll('.vote-btn').length
    // });
    
    // Initial animation
    animateCards();
});

// Authentication Functions
function initializeAuth() {
    // Check auth status on page load
    checkAuthStatus();
    
    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Register form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
    
    // Logout button
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }
}

function checkAuthStatus() {
    console.log('Checking authentication status...');
    // Check authentication status from server
    fetch('api/auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ action: 'check_status' })
    })
    .then(response => {
        console.log('Auth API response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Auth API response data:', data);
        if (data.success && data.is_logged_in) {
            console.log('User is logged in, showing user info');
            isLoggedIn = true;
            currentUser = data.user;
            showUserInfo(data.user);
            
            // Update bookmark button states after authentication is confirmed
            setTimeout(() => {
                console.log('Authentication confirmed, updating bookmark states...');
                updateBookmarkButtonStates();
            }, 200);
        } else {
            console.log('User is not logged in, showing login button');
            isLoggedIn = false;
            currentUser = null;
            showLoginButton();
        }
    })
    .catch(error => {
        console.error('Auth status check error:', error);
        showLoginButton();
    });
}

function handleLogin(event) {
    event.preventDefault();
    
    const username = document.getElementById('loginUsername').value;
    const password = document.getElementById('loginPassword').value;
    const messageDiv = document.getElementById('loginMessage');
    
    fetch('api/auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'login',
            username: username,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            showUserInfo(data.user);
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
            modal.hide();
            
            // Reload page to show premium content
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message, 'danger', messageDiv);
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        showAlert('Login failed. Please try again.', 'danger', messageDiv);
    });
}

function handleRegister(event) {
    event.preventDefault();
    
    const username = document.getElementById('registerUsername').value;
    const email = document.getElementById('registerEmail').value;
    const password = document.getElementById('registerPassword').value;
    const firstName = document.getElementById('registerFirstName')?.value || null;
    const lastName = document.getElementById('registerLastName')?.value || null;
    const messageDiv = document.getElementById('registerMessage');
    
    // Validate password length
    if (password.length < 8) {
        showAlert('Password must be at least 8 characters long', 'danger', messageDiv);
        return;
    }
    
    fetch('api/auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'register',
            username: username,
            email: email,
            password: password,
            first_name: firstName,
            last_name: lastName
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Store email for verification modal
            localStorage.setItem('pending_verification_email', email);
            
            // Close register modal
            const registerModal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
            registerModal.hide();
            
            // Show email verification modal
            showEmailVerificationModal();
            
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message, 'danger', messageDiv);
        }
    })
    .catch(error => {
        console.error('Register error:', error);
        showAlert('Registration failed. Please try again.', 'danger', messageDiv);
    });
}

function handleLogout() {
    // Show confirmation dialog
    if (!confirm('Are you sure you want to logout?')) {
        return;
    }
    
    // Show loading state
    const logoutBtn = document.getElementById('logoutBtn');
    const originalText = logoutBtn ? logoutBtn.innerHTML : 'Logout';
    if (logoutBtn) {
        logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging out...';
        logoutBtn.disabled = true;
    }
    
    fetch('api/auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ action: 'logout' })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            showLoginButton();
            
            // Reset global variables
            isLoggedIn = false;
            currentUser = null;
            
            // Clear any stored user data
            localStorage.removeItem('userData');
            localStorage.removeItem('authToken');
            localStorage.removeItem('oauth_state');
            localStorage.removeItem('pending_verification_email');
            localStorage.removeItem('subscribedEmail');
            localStorage.removeItem('subscriberPreferences');
            localStorage.removeItem('subscriberFrequency');
            localStorage.removeItem('subscriptionType');
            
            // Clear any OAuth-related cookies if they exist
            document.cookie.split(";").forEach(function(c) { 
                document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
            });
            
            // Redirect to home page after a short delay
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1500);
        } else {
            throw new Error(data.message || 'Logout failed');
        }
    })
    .catch(error => {
        console.error('Logout error:', error);
        showAlert('Logout failed. Please try again.', 'danger');
        
        // Restore button state
        if (logoutBtn) {
            logoutBtn.innerHTML = originalText;
            logoutBtn.disabled = false;
        }
    });
}

function showUserInfo(user) {
    console.log('Showing user info for:', user);
    
    // Set global variables
    isLoggedIn = true;
    currentUser = user;
    
    const loginBtn = document.getElementById('loginBtnSpan');
    const userInfo = document.getElementById('userInfo');
    const username = document.getElementById('username');
    const adminBtn = document.getElementById('adminBtn');
    
    if (loginBtn) {
        loginBtn.style.display = 'none';
        console.log('Hidden login button');
    }
    if (userInfo) {
        userInfo.style.display = 'inline-flex';
        userInfo.style.alignItems = 'center';
        if (username) username.textContent = user.username;
        console.log('Showed user info section');
        
        // Admin button visibility is now controlled by server-side PHP
        // Only show if the button exists (meaning user is admin)
        if (adminBtn) {
            adminBtn.style.display = 'inline-block';
            console.log('Admin button is visible (user is admin)');
        }
    }
    
    // Update bookmark button states after user info is shown
    setTimeout(() => {
        console.log('User info shown, updating bookmark states...');
        updateBookmarkButtonStates();
    }, 100);
}

function showLoginButton() {
    console.log('Showing login button');
    
    // Reset global variables
    isLoggedIn = false;
    currentUser = null;
    
    const loginBtn = document.getElementById('loginBtnSpan');
    const userInfo = document.getElementById('userInfo');
    const adminBtn = document.getElementById('adminBtn');
    
    if (loginBtn) {
        loginBtn.style.display = 'inline-block';
        console.log('Showed login button');
    }
    if (userInfo) {
        userInfo.style.display = 'none';
        console.log('Hidden user info section');
    }
    // Admin button will be hidden automatically when userInfo is hidden
    // since it's inside the userInfo span
}

// Newsletter Functions
function initializeNewsletterForms() {
    // Check if user is already subscribed (from localStorage or session)
    const subscribedEmail = localStorage.getItem('subscribedEmail');
    if (subscribedEmail) {
        hideNewsletterFormsForSubscriber(subscribedEmail);
        return;
    }

    // Main newsletter form
    const newsletterSubmit = document.getElementById('newsletterSubmit');
    if (newsletterSubmit) {
        newsletterSubmit.addEventListener('click', subscribeNewsletter);
    }
    
    // Sidebar newsletter form
    const sidebarNewsletterSubmit = document.getElementById('sidebarNewsletterSubmit');
    if (sidebarNewsletterSubmit) {
        sidebarNewsletterSubmit.addEventListener('click', subscribeNewsletter);
    }
    
    // Modal newsletter form
    const modalNewsletterForm = document.getElementById('modalNewsletterForm');
    if (modalNewsletterForm) {
        modalNewsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            subscribeNewsletterAdvanced();
        });
    }

    // Subscription type buttons
    const subscriptionButtons = document.querySelectorAll('.subscription-btn');
    subscriptionButtons.forEach(button => {
        button.addEventListener('click', function() {
            selectSubscriptionType(this);
        });
    });

    // Newsletter modal event listener
    const newsletterModal = document.getElementById('newsletterModal');
    if (newsletterModal) {
        newsletterModal.addEventListener('shown.bs.modal', function() {
            // Refresh user data when modal opens
            if (isLoggedIn && currentUser) {
                const emailInput = document.getElementById('modalNewsletterEmail');
                const nameInput = document.getElementById('modalNewsletterName');
                
                if (emailInput && currentUser.email) {
                    emailInput.value = currentUser.email;
                    emailInput.readOnly = true;
                }
                if (nameInput && currentUser.username) {
                    nameInput.value = currentUser.username;
                }
                
                console.log('Newsletter modal opened - populated user data:', {
                    email: currentUser.email,
                    username: currentUser.username
                });
            }
        });
    }

    // Check subscription status for email inputs
    const emailInputs = document.querySelectorAll('#newsletterEmail, #modalNewsletterEmail, #sidebarNewsletterEmail');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && isValidEmail(this.value)) {
                checkSubscriptionStatus(this.value, this);
            }
        });
    });
}

// Hide newsletter forms for subscribed users
// function hideNewsletterFormsForSubscriber(email) {
//     // Hide all newsletter forms and show subscribed content
//     const newsletterForms = document.querySelectorAll('.newsletter-form');
//     newsletterForms.forEach(form => {
//         form.innerHTML = `
//             <div class="text-center py-4">
//                 <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
//                 <h5 class="text-success mb-3">You're Subscribed!</h5>
//                 <p class="text-muted mb-3">You're receiving our newsletter at <strong>${email}</strong></p>
//                 <div class="bg-light p-3 rounded mb-3">
//                     <h6 class="mb-2">Latest Updates:</h6>
//                     <ul class="list-unstyled small text-muted">
//                         <li><i class="fas fa-arrow-right me-2"></i>New JavaScript tutorials added</li>
//                         <li><i class="fas fa-arrow-right me-2"></i>React hooks guide published</li>
//                         <li><i class="fas fa-arrow-right me-2"></i>Python for beginners series</li>
//                         <li><i class="fas fa-arrow-right me-2"></i>Web development tips weekly</li>
//                     </ul>
//                 </div>
//                 <button class="btn btn-outline-primary btn-sm" onclick="changeSubscriptionEmail()">
//                     <i class="fas fa-edit me-1"></i>Change Email
//                 </button>
//             </div>
//         `;
//     });

//     // Update newsletter links in navigation and footer
//     const newsletterLinks = document.querySelectorAll('a[data-bs-target="#newsletterModal"], a[href*="newsletter"]');
//     newsletterLinks.forEach(link => {
//         link.innerHTML = '<i class="fas fa-envelope me-1"></i>Newsletter (Subscribed)';
//         link.classList.add('text-success');
//         link.onclick = function(e) {
//             e.preventDefault();
//             showSubscribedModal(email);
//         };
//     });
// }

// Show subscribed modal instead of newsletter modal
// function showSubscribedModal(email) {
//     const modal = document.getElementById('newsletterModal');
//     if (modal) {
//         const modalBody = modal.querySelector('.modal-body');
//         modalBody.innerHTML = `
//             <div class="text-center py-4">
//                 <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
//                 <h5 class="text-success mb-3">You're Already Subscribed!</h5>
//                 <p class="text-muted mb-3">You're receiving our newsletter at <strong>${email}</strong></p>
//                 <div class="bg-light p-3 rounded mb-3">
//                     <h6 class="mb-2">Latest Updates:</h6>
//                     <ul class="list-unstyled small text-muted">
//                         <li><i class="fas fa-arrow-right me-2"></i>New JavaScript tutorials added</li>
//                         <li><i class="fas fa-arrow-right me-2"></i>React hooks guide published</li>
//                         <li><i class="fas fa-arrow-right me-2"></i>Python for beginners series</li>
//                         <li><i class="fas fa-arrow-right me-2"></i>Web development tips weekly</li>
//                     </ul>
//                 </div>
//                 <button class="btn btn-outline-primary btn-sm" onclick="changeSubscriptionEmail()">
//                     <i class="fas fa-edit me-1"></i>Change Email
//                 </button>
//             </div>
//         `;
        
//         const bsModal = new bootstrap.Modal(modal);
//         bsModal.show();
//     }
// }

// Change subscription email
function changeSubscriptionEmail() {
    // Clear stored email and reload page to show forms again
    localStorage.removeItem('subscribedEmail');
    location.reload();
}

// Check if user is already subscribed
function checkSubscriptionStatus(email, inputElement) {
    fetch('api/check_subscription.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.is_subscribed) {
            showSubscribedContent(inputElement);
            // Store the subscribed email in localStorage
            localStorage.setItem('subscribedEmail', email);
        }
    })
    .catch(error => {
        console.error('Error checking subscription:', error);
    });
}

// Show content for already subscribed users
function showSubscribedContent(inputElement) {
    // Find the newsletter container
    const newsletterContainer = inputElement.closest('.newsletter-form') || 
                               inputElement.closest('.modal-body') ||
                               inputElement.closest('.card-body');
    
    if (newsletterContainer) {
        const email = inputElement.value;
        const subscriber = getSubscriberByEmail(email);
        
        // Check if it's the modal
        const isModal = inputElement.id === 'modalNewsletterEmail';
        
        if (isModal) {
            // Hide the form and show subscribed content in modal
            const form = document.getElementById('modalNewsletterForm');
            const subscribedContent = document.getElementById('modalSubscribedContent');
            
            if (form && subscribedContent) {
                form.style.display = 'none';
                subscribedContent.style.display = 'block';
                subscribedContent.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="text-success mb-3">Already Subscribed!</h5>
                        <p class="text-muted mb-3">You're already subscribed to our newsletter with <strong>${email}</strong></p>
                        <div class="bg-light p-3 rounded mb-3">
                            <h6 class="mb-2">Latest Updates:</h6>
                            <ul class="list-unstyled small text-muted">
                                <li><i class="fas fa-arrow-right me-2"></i>New JavaScript tutorials added</li>
                                <li><i class="fas fa-arrow-right me-2"></i>React hooks guide published</li>
                                <li><i class="fas fa-arrow-right me-2"></i>Python for beginners series</li>
                                <li><i class="fas fa-arrow-right me-2"></i>Web development tips weekly</li>
                            </ul>
                        </div>
                        <button class="btn btn-outline-primary btn-sm" onclick="showNewsletterForm('${email}')">
                            <i class="fas fa-edit me-1"></i>Change Email
                        </button>
                    </div>
                `;
            }
        } else {
            // Regular newsletter forms
            newsletterContainer.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5 class="text-success mb-3">Already Subscribed!</h5>
                    <p class="text-muted mb-3">You're already subscribed to our newsletter with <strong>${email}</strong></p>
                    <div class="bg-light p-3 rounded mb-3">
                        <h6 class="mb-2">Latest Updates:</h6>
                        <ul class="list-unstyled small text-muted">
                            <li><i class="fas fa-arrow-right me-2"></i>New JavaScript tutorials added</li>
                            <li><i class="fas fa-arrow-right me-2"></i>React hooks guide published</li>
                            <li><i class="fas fa-arrow-right me-2"></i>Python for beginners series</li>
                            <li><i class="fas fa-arrow-right me-2"></i>Web development tips weekly</li>
                        </ul>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" onclick="showNewsletterForm('${email}')">
                        <i class="fas fa-edit me-1"></i>Change Email
                    </button>
                </div>
            `;
        }
    }
}

// Show newsletter form again
function showNewsletterForm(currentEmail = '') {
    // Reset all newsletter forms
    const forms = document.querySelectorAll('.newsletter-form');
    forms.forEach(form => {
        form.innerHTML = `
            <div class="input-group mb-3">
                <input type="email" class="form-control" id="newsletterEmail" placeholder="Enter your email address" value="${currentEmail}">
                <button class="btn btn-primary" type="button" id="newsletterSubmit">
                    <i class="fas fa-paper-plane me-1"></i>Subscribe
                </button>
            </div>
            <div id="newsletterMessage" class="alert" style="display: none;"></div>
        `;
    });
    
    // Reset modal form
    const modalForm = document.getElementById('modalNewsletterForm');
    const modalSubscribedContent = document.getElementById('modalSubscribedContent');
    if (modalForm && modalSubscribedContent) {
        modalForm.style.display = 'block';
        modalSubscribedContent.style.display = 'none';
        document.getElementById('modalNewsletterEmail').value = currentEmail;
    }
    
    // Re-initialize newsletter forms
    initializeNewsletterForms();
}

// Get subscriber info (placeholder - would be implemented with actual data)
function getSubscriberByEmail(email) {
    return {
        email: email,
        subscribed_at: new Date().toLocaleDateString(),
        is_active: true
    };
}

function subscribeNewsletter() {
    const email = document.getElementById('newsletterEmail')?.value || 
                  document.getElementById('modalNewsletterEmail')?.value ||
                  document.getElementById('sidebarNewsletterEmail')?.value;
    
    if (!email) {
        showAlert('Please enter your email address.', 'warning');
        return;
    }
    
    if (!isValidEmail(email)) {
        showAlert('Please enter a valid email address.', 'warning');
        return;
    }
    
    // Send to server
    fetch('api/newsletter.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('newsletterMessage') || 
                          document.getElementById('modalNewsletterMessage') ||
                          document.getElementById('sidebarNewsletterMessage');
        
        showAlert(data.message, data.success ? 'success' : 'warning', messageDiv);
        
        if (data.success) {
            // Clear form
            const form = document.getElementById('newsletterForm') || 
                        document.getElementById('modalNewsletterForm');
            if (form) form.reset();
            
            // Clear sidebar form
            const sidebarEmail = document.getElementById('sidebarNewsletterEmail');
            if (sidebarEmail) sidebarEmail.value = '';
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('newsletterModal'));
            if (modal) modal.hide();

            // Store the subscribed email in localStorage
            localStorage.setItem('subscribedEmail', email);
            
            // Hide all newsletter forms and show subscribed content
            setTimeout(() => {
                hideNewsletterFormsForSubscriber(email);
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to subscribe. Please try again.', 'danger');
    });
}

// Comment Functions
function initializeCommentForms() {
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', handleCommentSubmit);
    }
}

function handleCommentSubmit(event) {
    event.preventDefault();
    
    const postId = document.getElementById('postId').value;
    const content = document.getElementById('commentContent').value;
    const messageDiv = document.getElementById('commentMessage');
    
    if (!content.trim()) {
        showAlert('Please enter a comment.', 'warning', messageDiv);
        return;
    }
    
    fetch('api/comments.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            post_id: postId,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success', messageDiv);
            document.getElementById('commentContent').value = '';
            
            // Reload comments after a delay
            setTimeout(() => location.reload(), 2000);
        } else {
            showAlert(data.message, 'danger', messageDiv);
        }
    })
    .catch(error => {
        console.error('Comment error:', error);
        showAlert('Failed to post comment. Please try again.', 'danger', messageDiv);
    });
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Alert system
function showAlert(message, type = 'info', container = null) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    if (container) {
        container.innerHTML = '';
        container.appendChild(alertDiv);
        container.style.display = 'block';
    } else {
        // Show as toast or in a default location
        document.body.appendChild(alertDiv);
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

// Smooth scrolling for anchor links
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const offsetTop = targetElement.offsetTop - 80; // Account for fixed navbar
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
});

// Add loading animation to elements
function addLoadingAnimation() {
    const elements = document.querySelectorAll('.card, .btn, .badge');
    elements.forEach((element, index) => {
        setTimeout(() => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.6s ease';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, 100);
        }, index * 50);
    });
}

// Search functionality (for future implementation)
function searchPosts(query) {
    // This would typically filter posts based on search query
    console.log('Searching for:', query);
}

// Tag filtering (for future implementation)
function filterByTag(tag) {
    // This would filter posts by tag
    console.log('Filtering by tag:', tag);
}

// Category filtering (for future implementation)
function filterByCategory(category) {
    // This would filter posts by category
    console.log('Filtering by category:', category);
}

// Comment system (for blog posts)
function addComment(postId, author, content) {
    const comment = {
        id: Date.now(),
        author: author,
        content: content,
        date: new Date().toISOString(),
        likes: 0
    };
    
    // Store comment in localStorage (for demo purposes)
    const comments = JSON.parse(localStorage.getItem(`comments_${postId}`) || '[]');
    comments.push(comment);
    localStorage.setItem(`comments_${postId}`, JSON.stringify(comments));
    
    return comment;
}

// Like comment functionality
function likeComment(postId, commentId) {
    const comments = JSON.parse(localStorage.getItem(`comments_${postId}`) || '[]');
    const comment = comments.find(c => c.id === commentId);
    
    if (comment) {
        comment.likes++;
        localStorage.setItem(`comments_${postId}`, JSON.stringify(comments));
    }
}

// Load comments for a post
function loadComments(postId) {
    return JSON.parse(localStorage.getItem(`comments_${postId}`) || '[]');
}

// Utility function to format dates
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 1) {
        return '1 day ago';
    } else if (diffDays < 7) {
        return `${diffDays} days ago`;
    } else if (diffDays < 30) {
        const weeks = Math.floor(diffDays / 7);
        return `${weeks} week${weeks > 1 ? 's' : ''} ago`;
    } else {
        return date.toLocaleDateString();
    }
}

// Initialize tooltips and popovers
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize Bootstrap popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for search (future feature)
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        // Open search modal or focus search input
        console.log('Search shortcut triggered');
    }
    
    // Ctrl/Cmd + D for dark mode toggle
    if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
        e.preventDefault();
        document.getElementById('darkModeToggle').click();
    }
});

// Performance optimization - lazy loading for images
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Initialize lazy loading
document.addEventListener('DOMContentLoaded', lazyLoadImages);

// Banner Ad Management
function initializeBannerAds() {
    const adBanners = document.querySelectorAll('.ad-banner');
    
    adBanners.forEach(banner => {
        // Add click tracking (for analytics)
        banner.addEventListener('click', function(e) {
            if (e.target.closest('.ad-content')) {
                trackAdClick(banner.id);
            }
        });
        
        // Add hover effects
        banner.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
        });
        
        banner.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
}

// Track ad clicks (for analytics)
function trackAdClick(adId) {
    console.log(`Ad clicked: ${adId}`);
    // In a real implementation, this would send data to analytics
    // Example: gtag('event', 'ad_click', { 'ad_id': adId });
}

// Simulate ad loading (for demo purposes)
function simulateAdLoading() {
    const adPlaceholders = document.querySelectorAll('.ad-placeholder');
    
    adPlaceholders.forEach(placeholder => {
        // Add loading class initially
        placeholder.classList.add('loading');
        
        setTimeout(() => {
            placeholder.classList.remove('loading');
            placeholder.classList.add('loaded');
        }, Math.random() * 1000 + 500); // Random delay between 500ms and 1500ms
    });
}

// Initialize banner ads when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeBannerAds();
    simulateAdLoading();
    initializeCodeBlocks();
});

// Initialize code blocks with copy functionality
function initializeCodeBlocks() {
    const codeBlocks = document.querySelectorAll('pre code');
    
    codeBlocks.forEach((codeBlock, index) => {
        const pre = codeBlock.parentElement;
        
        // Add copy button
        const copyBtn = document.createElement('button');
        copyBtn.className = 'copy-code-btn';
        copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copy';
        copyBtn.onclick = function() {
            copyToClipboard(codeBlock.textContent);
            this.innerHTML = '<i class="fas fa-check"></i> Copied!';
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-copy"></i> Copy';
            }, 2000);
        };
        
        // Wrap pre in a container
        const wrapper = document.createElement('div');
        wrapper.className = 'code-block-wrapper';
        pre.parentNode.insertBefore(wrapper, pre);
        wrapper.appendChild(pre);
        wrapper.appendChild(copyBtn);
    });
}

// Copy text to clipboard
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            console.log('Code copied to clipboard');
        }).catch(err => {
            console.error('Failed to copy: ', err);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        fallbackCopyTextToClipboard(text);
    }
}

// Fallback copy method
function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        console.log('Code copied to clipboard (fallback)');
    } catch (err) {
        console.error('Fallback copy failed: ', err);
    }
    
    document.body.removeChild(textArea);
}

// Voting functionality
function initializeVoting() {
    // Look for both vote button classes
    const voteButtons = document.querySelectorAll('.vote-btn, .vote-btn-modern');
    console.log('Initializing voting, found', voteButtons.length, 'vote buttons');
    
    voteButtons.forEach((button, index) => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Vote button clicked!', this.dataset.voteType, 'for post', this.dataset.postId);
            
            const postId = this.dataset.postId;
            const voteType = this.dataset.voteType;
            const currentVote = this.dataset.currentVote;
            
            // Check if user is logged in
            if (!isLoggedIn) {
                showAlert('Please log in to vote', 'warning');
                return;
            }
            
            // Disable button during request
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            console.log('Sending vote request to API...');
            console.log('Request data:', { post_id: postId, vote_type: voteType });
            
            // Send vote request
            fetch('api/vote.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    post_id: postId,
                    vote_type: voteType
                })
            })
            .then(response => {
                console.log('API response status:', response.status);
                console.log('API response headers:', response.headers);
                return response.text().then(text => {
                    console.log('Raw API response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('Parsed API response:', data);
                if (data.success) {
                    console.log('Vote successful, updating UI...');
                    // Update vote counts
                    updateVoteCounts(postId, data.vote_stats, data.user_vote);
                    showAlert(data.message, 'success');
                } else {
                    console.log('Vote failed:', data.message);
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Vote error:', error);
                showAlert('Error casting vote. Please try again.', 'danger');
            })
            .finally(() => {
                console.log('Restoring button state...');
                // Re-enable button
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    });
}

// Update vote counts for a specific post
function updateVoteCounts(postId, voteStats, userVote) {
    console.log('Updating vote counts for post', postId, 'with stats:', voteStats, 'user vote:', userVote);
    
    // Find all vote buttons for this specific post
    const allVoteButtons = document.querySelectorAll(`[data-post-id="${postId}"]`);
    console.log('Found', allVoteButtons.length, 'vote buttons for post', postId);
    
    if (allVoteButtons.length === 0) {
        console.error('Could not find any vote buttons for post', postId);
        return;
    }
    
    // Update each vote button
    allVoteButtons.forEach(button => {
        const voteType = button.dataset.voteType;
        const voteCount = button.querySelector('.vote-count');
        
        if (voteType === 'upvote') {
            // Update upvote button
            if (voteCount) {
                voteCount.textContent = voteStats.upvotes;
                console.log('Updated upvote count to', voteStats.upvotes);
            }
            
                    // Update button appearance
        if (userVote === 'upvote') {
            // Handle both Bootstrap and custom classes
            if (button.classList.contains('vote-btn-modern')) {
                button.classList.add('voted');
            } else {
                button.classList.remove('btn-outline-success');
                button.classList.add('btn-success');
            }
            console.log('Set upvote button to active state');
        } else {
            if (button.classList.contains('vote-btn-modern')) {
                button.classList.remove('voted');
            } else {
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-success');
            }
            console.log('Set upvote button to inactive state');
        }
            
        } else if (voteType === 'downvote') {
            // Update downvote button
            if (voteCount) {
                voteCount.textContent = voteStats.downvotes;
                console.log('Updated downvote count to', voteStats.downvotes);
            }
            
            // Update button appearance
            if (userVote === 'downvote') {
                // Handle both Bootstrap and custom classes
                if (button.classList.contains('vote-btn-modern')) {
                    button.classList.add('voted');
                } else {
                    button.classList.remove('btn-outline-danger');
                    button.classList.add('btn-danger');
                }
                console.log('Set downvote button to active state');
            } else {
                if (button.classList.contains('vote-btn-modern')) {
                    button.classList.remove('voted');
                } else {
                    button.classList.remove('btn-danger');
                    button.classList.add('btn-outline-danger');
                }
                console.log('Set downvote button to inactive state');
            }
        }
        
        // Update the current vote data attribute
        button.dataset.currentVote = userVote || '';
    });
    
    // Update vote score - find it relative to any vote button
    const firstButton = allVoteButtons[0];
    const cardContainer = firstButton.closest('.card, .post-card, .trending-post-card');
    if (cardContainer) {
        // Try different vote score selectors
        let voteScore = cardContainer.querySelector('.vote-score .badge, .vote-score-modern .score-badge');
        if (voteScore) {
            voteScore.innerHTML = `<i class="fas fa-chart-line me-1"></i>${voteStats.vote_score}`;
            console.log('Updated vote score to', voteStats.vote_score);
        } else {
            console.warn('Could not find vote score element for post', postId);
        }
    }
    
    console.log('Vote counts update completed for post', postId);
}

// Show unsubscribe confirmation modal
function showUnsubscribeConfirm() {
    const modal = new bootstrap.Modal(document.getElementById('unsubscribeModal'));
    modal.show();
}

// Handle unsubscribe process
function handleUnsubscribe() {
    if (!isLoggedIn) {
        showAlert('Please login first', 'warning');
        return;
    }
    
    if (confirm('Are you sure you want to unsubscribe from the newsletter? This action cannot be undone.')) {
        // Send unsubscribe request
        fetch('api/unsubscribe.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                
                // Reload page to update subscription status
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Unsubscribe error:', error);
            alert('Failed to unsubscribe. Please try again.');
        });
    }
}

// Handle unsubscribe confirmation from modal
function handleUnsubscribeConfirm() {
    const button = event.target;
    const originalText = button.innerHTML;
    const messageDiv = document.getElementById('unsubscribeMessage');
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Unsubscribing...';
    
    // Send unsubscribe request
    fetch('api/unsubscribe.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success', messageDiv);
            
            // Reload page after successful unsubscribe
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showAlert(data.message, 'danger', messageDiv);
        }
    })
    .catch(error => {
        console.error('Unsubscribe error:', error);
        showAlert('Failed to unsubscribe. Please try again.', 'danger', messageDiv);
    })
    .finally(() => {
        // Restore button
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Show login modal
function showLoginModal() {
    const newsletterModal = bootstrap.Modal.getInstance(document.getElementById('newsletterModal'));
    if (newsletterModal) {
        newsletterModal.hide();
    }
    
    setTimeout(() => {
        const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
    }, 300);
}

// Show register modal
function showRegisterModal() {
    const newsletterModal = bootstrap.Modal.getInstance(document.getElementById('newsletterModal'));
    if (newsletterModal) {
        newsletterModal.hide();
    }
    
    setTimeout(() => {
        const registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
        registerModal.show();
    }, 300);
}

// OAuth Functions
function loginWithLinkedIn() {
    // Check if LinkedIn is configured
    fetch('api/auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'check_oauth_status',
            provider: 'linkedin'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.configured) {
            // Generate OAuth state
            const state = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
            
            // Clear any existing OAuth state first
            localStorage.removeItem('oauth_state');
            
            // Store new OAuth state
            localStorage.setItem('oauth_state', state);
            
            // Redirect to LinkedIn OAuth
            const linkedinUrl = `auth/linkedin-auth.php?state=${state}`;
            window.location.href = linkedinUrl;
        } else {
            showAlert('LinkedIn OAuth is not configured. Please contact the administrator.', 'warning');
        }
    })
    .catch(error => {
        console.error('LinkedIn OAuth error:', error);
        showAlert('LinkedIn OAuth is not configured. Please contact the administrator.', 'warning');
    });
}

function registerWithLinkedIn() {
    // Same as login for now - LinkedIn will create account if it doesn't exist
    loginWithLinkedIn();
}

function loginWithGoogle() {
    console.log('loginWithGoogle function called');
    
    // Check if Google is configured
    fetch('api/auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'check_oauth_status',
            provider: 'google'
        })
    })
    .then(response => {
        console.log('Google OAuth check response:', response);
        return response.json();
    })
    .then(data => {
        console.log('Google OAuth check data:', data);
        if (data.configured) {
            console.log('Google OAuth is configured, redirecting...');
            // Generate OAuth state
            const state = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
            
            // Clear any existing OAuth state first
            localStorage.removeItem('oauth_state');
            
            // Store new OAuth state
            localStorage.setItem('oauth_state', state);
            
            // Redirect to Google OAuth
            const googleUrl = `auth/google-auth.php?state=${state}`;
            console.log('Redirecting to:', googleUrl);
            window.location.href = googleUrl;
        } else {
            console.log('Google OAuth is not configured');
            showAlert('Google OAuth is not configured. Please contact the administrator.', 'warning');
        }
    })
    .catch(error => {
        console.error('Google OAuth error:', error);
        showAlert('Google OAuth is not configured. Please contact the administrator.', 'warning');
    });
}

function registerWithGoogle() {
    // Same as login for now - Google will create account if it doesn't exist
    loginWithGoogle();
}

function loginWithGitHub() {
    // Check if GitHub is configured
    fetch('api/auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'check_oauth_status',
            provider: 'github'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.configured) {
            // Generate OAuth state
            const state = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
            
            // Clear any existing OAuth state first
            localStorage.removeItem('oauth_state');
            
            // Store new OAuth state
            localStorage.setItem('oauth_state', state);
            
            // Redirect to GitHub OAuth
            const githubUrl = `auth/github-auth.php?state=${state}`;
            window.location.href = githubUrl;
        } else {
            showAlert('GitHub OAuth is not configured. Please contact the administrator.', 'warning');
        }
    })
    .catch(error => {
        console.error('GitHub OAuth error:', error);
        showAlert('GitHub OAuth is not configured. Please contact the administrator.', 'warning');
    });
}

function registerWithGitHub() {
    // Same as login for now - GitHub will create account if it doesn't exist
    loginWithGitHub();
}

// Email Verification Functions
function showEmailVerificationModal() {
    const modal = new bootstrap.Modal(document.getElementById('emailVerificationModal'));
    modal.show();
}

function resendVerificationEmail() {
    const email = localStorage.getItem('pending_verification_email');
    if (!email) {
        showAlert('No email found for verification', 'error');
        return;
    }
    
    fetch('api/auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'resend_verification',
            email: email
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Verification email sent successfully!', 'success');
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error resending verification:', error);
        showAlert('Failed to resend verification email', 'error');
    });
}

// Forgot Password Functions
function showForgotPasswordModal() {
    // Hide login modal
    const loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
    if (loginModal) {
        loginModal.hide();
    }
    
    // Show forgot password modal
    const forgotPasswordModal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
    forgotPasswordModal.show();
}

function handleForgotPassword(event) {
    event.preventDefault();
    
    const email = document.getElementById('forgotPasswordEmail').value;
    const messageDiv = document.getElementById('forgotPasswordMessage');
    
    fetch('api/auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'forgot_password',
            email: email
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success', messageDiv);
            // Clear form
            document.getElementById('forgotPasswordForm').reset();
        } else {
            showAlert(data.message, 'danger', messageDiv);
        }
    })
    .catch(error => {
        console.error('Forgot password error:', error);
        showAlert('Failed to send reset email', 'error', messageDiv);
    });
}

// Select subscription type
function selectSubscriptionType(button) {
    if (!isLoggedIn) {
        showAlert('Please login or register first to subscribe', 'warning');
        return;
    }
    
    const subscriptionType = button.dataset.subscriptionType;
    const price = button.dataset.price;
    const isNewsletter = subscriptionType === 'newsletter';
    const isPremium = subscriptionType === 'premium';
    
    // Hide login message and show form
    document.getElementById('loginRequiredMessage').style.display = 'none';
    document.getElementById('subscriptionFormContainer').style.display = 'block';
    
    // Set form values
    document.getElementById('selectedSubscriptionType').value = subscriptionType;
    document.getElementById('selectedPrice').value = price;
    
    // Update form title
    const titleElement = document.getElementById('selectedPlanTitle');
    if (isNewsletter) {
        titleElement.innerHTML = '<i class="fas fa-envelope me-2 text-primary"></i>Subscribe to Newsletter (Free)';
        titleElement.className = 'mb-3 text-primary';
    } else {
        titleElement.innerHTML = '<i class="fas fa-crown me-2 text-warning"></i>Subscribe to Premium ($' + price + '/month)';
        titleElement.className = 'mb-3 text-warning';
    }
    
    // Update submit button
    const submitBtn = document.querySelector('#modalNewsletterForm button[type="submit"]');
    if (isNewsletter) {
        submitBtn.className = 'btn btn-primary btn-lg w-100';
        submitBtn.innerHTML = '<i class="fas fa-envelope me-2"></i>Subscribe to Newsletter';
    } else {
        submitBtn.className = 'btn btn-warning btn-lg w-100';
        submitBtn.innerHTML = '<i class="fas fa-crown me-2"></i>Subscribe to Premium';
    }
    
    // Pre-fill email with logged-in user's email
    const emailField = document.getElementById('modalNewsletterEmail');
    const nameField = document.getElementById('modalNewsletterName');
    
    if (currentUser && currentUser.email && emailField) {
        emailField.value = currentUser.email;
        emailField.readOnly = true;
        console.log('Pre-filled email field with:', currentUser.email);
    } else {
        console.warn('Could not pre-fill email:', {
            currentUser: currentUser,
            emailField: emailField,
            email: currentUser ? currentUser.email : 'no user'
        });
    }
    
    // Pre-fill name if available
    if (currentUser && currentUser.username && nameField) {
        nameField.value = currentUser.username;
        console.log('Pre-filled name field with:', currentUser.username);
    }
}

// Initialize newsletter modal based on login status
function initializeNewsletterModal() {
    const loginMessage = document.getElementById('loginRequiredMessage');
    const formContainer = document.getElementById('subscriptionFormContainer');
    
    if (isLoggedIn) {
        loginMessage.style.display = 'none';
        // Don't show form yet - wait for user to select subscription type
        
        // Pre-populate user information if form exists
        const emailInput = document.getElementById('modalNewsletterEmail');
        const nameInput = document.getElementById('modalNewsletterName');
        
        if (currentUser) {
            if (emailInput && currentUser.email) {
                emailInput.value = currentUser.email;
                emailInput.readOnly = true;
            }
            if (nameInput && currentUser.username) {
                nameInput.value = currentUser.username;
            }
        }
    } else {
        loginMessage.style.display = 'block';
        formContainer.style.display = 'none';
    }
}

// Enhanced newsletter subscription function
function subscribeNewsletterAdvanced() {
    if (!isLoggedIn) {
        showAlert('Please login or register first to subscribe', 'warning');
        return;
    }

    const nameInput = document.getElementById('modalNewsletterName');
    const emailInput = document.getElementById('modalNewsletterEmail');
    const frequencyRadios = document.getElementsByName('newsletterFrequency');
    const preferenceCheckboxes = document.querySelectorAll('.newsletter-pref');
    const messageDiv = document.getElementById('modalNewsletterMessage');
    const subscriptionTypeInput = document.getElementById('selectedSubscriptionType');
    const priceInput = document.getElementById('selectedPrice');
    
    const name = nameInput ? nameInput.value.trim() : '';
    const email = emailInput ? emailInput.value.trim() : '';
    const subscriptionType = subscriptionTypeInput ? subscriptionTypeInput.value : 'newsletter';
    const price = priceInput ? priceInput.value : '0';
    
    console.log('Subscription form data:', {
        nameInput: nameInput,
        emailInput: emailInput,
        name: name,
        email: email,
        subscriptionType: subscriptionType,
        currentUser: currentUser
    });
    
    if (!email) {
        showAlert('Please enter a valid email address', 'danger', messageDiv);
        console.error('Email validation failed - no email provided');
        return;
    }
    
    if (!subscriptionType) {
        showAlert('Please select a subscription type', 'danger', messageDiv);
        return;
    }
    
    // Get selected frequency
    let frequency = 'weekly';
    for (const radio of frequencyRadios) {
        if (radio.checked) {
            frequency = radio.value;
            break;
        }
    }
    
    // Get selected preferences
    const preferences = {};
    preferenceCheckboxes.forEach(checkbox => {
        preferences[checkbox.value] = checkbox.checked;
    });
    
    console.log('Advanced newsletter subscription:', {
        name: name,
        email: email,
        frequency: frequency,
        preferences: preferences,
        subscription_type: subscriptionType,
        price: price
    });
    
    // Show loading state
    const submitBtn = document.querySelector('#modalNewsletterForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    
    if (subscriptionType === 'newsletter') {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Subscribing to Newsletter...';
    } else {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Subscribing to Premium...';
    }
    
    // Send subscription request
    fetch('api/newsletter.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            email: email,
            name: name,
            frequency: frequency,
            preferences: preferences,
            subscription_type: subscriptionType,
            source: 'modal'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success', messageDiv);
            
            // Store subscription info
            localStorage.setItem('subscribedEmail', email);
            localStorage.setItem('subscriberPreferences', JSON.stringify(preferences));
            localStorage.setItem('subscriberFrequency', frequency);
            localStorage.setItem('subscriptionType', subscriptionType);
            
            // If premium subscription, update user status and reload page
            if (data.user_updated && data.is_premium && subscriptionType === 'premium') {
                console.log('Premium subscription activated! User is now premium.');
                
                // Update current user object
                if (currentUser) {
                    currentUser.is_premium = true;
                }
                
                // Show special premium success message
                showAlert('🎉 Welcome to Premium! You now have access to exclusive content. The page will refresh to show your new privileges.', 'success', messageDiv);
                
                // Reload page after a delay to show premium content
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
                
                return; // Don't hide modal immediately for premium
            }
            
            // Hide form and show success message for regular newsletter
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('newsletterModal'));
                if (modal) {
                    modal.hide();
                }
                hideNewsletterFormsForSubscriber(email);
            }, 2000);
            
        } else {
            showAlert(data.message, 'danger', messageDiv);
        }
    })
    .catch(error => {
        console.error('Newsletter subscription error:', error);
        showAlert('Failed to subscribe. Please try again.', 'danger', messageDiv);
    })
    .finally(() => {
        // Restore button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Profile Picture Upload Function
function uploadProfilePicture(input) {
    const file = input.files[0];
    if (!file) return;

    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!allowedTypes.includes(file.type)) {
        showAlert('Invalid file type. Only JPG, PNG, and GIF are allowed.', 'danger');
        return;
    }

    // Validate file size (max 5MB)
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        showAlert('File size too large. Maximum size is 5MB.', 'danger');
        return;
    }

    // Show progress bar
    const progressBar = document.getElementById('uploadProgress');
    const progressBarInner = progressBar.querySelector('.progress-bar');
    progressBar.style.display = 'block';
    progressBarInner.style.width = '0%';

    // Simulate progress
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += 10;
        progressBarInner.style.width = progress + '%';
        if (progress >= 90) {
            clearInterval(progressInterval);
        }
    }, 100);

    // Create FormData
    const formData = new FormData();
    formData.append('profile_picture', file);

    // Upload file
    fetch('api/profile-picture.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(progressInterval);
        progressBarInner.style.width = '100%';
        
        if (data.success) {
            // Update the profile picture display
            const profileAvatar = document.querySelector('.profile-avatar');
            const existingImg = profileAvatar.querySelector('img');
            const existingIcon = profileAvatar.querySelector('i');

            if (existingImg) {
                existingImg.src = data.profile_picture + '?t=' + new Date().getTime();
            } else if (existingIcon) {
                // Replace icon with image
                existingIcon.remove();
                const newImg = document.createElement('img');
                newImg.src = data.profile_picture + '?t=' + new Date().getTime();
                newImg.alt = 'Profile Picture';
                newImg.className = 'rounded-circle img-fluid';
                newImg.style = 'width: 120px; height: 120px; object-fit: cover;';
                profileAvatar.insertBefore(newImg, profileAvatar.firstChild);
            }

            // Show success message with additional info for OAuth users
            let successMessage = 'Profile picture updated successfully!';
            if (data.replaced_oauth_picture) {
                successMessage = 'Profile picture updated successfully! You\'ve replaced your OAuth profile picture with a custom one.';
            }
            
            showAlert(successMessage, 'success');
            
        } else {
            showAlert(data.message || 'Failed to upload profile picture.', 'danger');
        }
    })
    .catch(error => {
        clearInterval(progressInterval);
        console.error('Profile picture upload error:', error);
        showAlert('Failed to upload profile picture. Please try again.', 'danger');
    })
    .finally(() => {
        // Hide progress bar after a short delay
        setTimeout(() => {
            progressBar.style.display = 'none';
            progressBarInner.style.width = '0%';
        }, 1000);
        
        // Clear the input
        input.value = '';
    });
}

// Search Functionality
function initializeSearch() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = searchInput.value.trim();
            
            if (query.length > 0) {
                window.location.href = `search.php?q=${encodeURIComponent(query)}`;
            }
        });
    }
    
    if (window.location.pathname.includes('search.php')) {
        initializeSearchSuggestions();
    }
    
    // Initialize search suggestions for all search inputs
    if (searchInput) {
        initializeSearchSuggestions(searchInput);
    }
}

// Search Suggestions Functionality
function initializeSearchSuggestions(searchInput = null) {
    const input = searchInput || document.getElementById('searchInput');
    if (!input) return;
    
    let suggestionsContainer = null;
    let debounceTimer = null;
    
    // Create suggestions container
    function createSuggestionsContainer() {
        if (suggestionsContainer) return;
        
        suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'search-suggestions position-absolute w-100';
        suggestionsContainer.style.cssText = 'top: 100%; left: 0; display: none; max-height: 400px; overflow-y: auto;';
        
        // Insert after the search input's parent
        const inputGroup = input.closest('.input-group');
        if (inputGroup) {
            inputGroup.style.position = 'relative';
            inputGroup.appendChild(suggestionsContainer);
        }
    }
    
    // Show suggestions
    function showSuggestions(suggestions) {
        if (!suggestionsContainer) return;
        
        if (suggestions.length === 0) {
            suggestionsContainer.style.display = 'none';
            return;
        }
        
        suggestionsContainer.innerHTML = suggestions.map(item => `
            <div class="suggestion-item p-2 border-bottom" style="cursor: pointer;">
                <div class="d-flex align-items-center">
                    <i class="${item.icon} me-2 text-muted"></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold">${item.title}</div>
                        ${item.category ? `<small class="text-muted">${item.category}</small>` : ''}
                        ${item.description ? `<small class="text-muted d-block">${item.description}</small>` : ''}
                    </div>
                </div>
            </div>
        `).join('');
        
        suggestionsContainer.style.display = 'block';
        
        // Add click handlers
        const items = suggestionsContainer.querySelectorAll('.suggestion-item');
        items.forEach((item, index) => {
            item.addEventListener('click', () => {
                window.location.href = suggestions[index].url;
            });
            
            item.addEventListener('mouseenter', () => {
                item.style.backgroundColor = '#f8f9fa';
            });
            
            item.addEventListener('mouseleave', () => {
                item.style.backgroundColor = '';
            });
        });
    }
    
    // Fetch suggestions from API
    async function fetchSuggestions(query) {
        if (query.length < 2) {
            showSuggestions([]);
            return;
        }
        
        try {
            const response = await fetch(`api/search_suggestions.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success) {
                showSuggestions(data.suggestions);
            } else {
                showSuggestions([]);
            }
        } catch (error) {
            console.error('Error fetching suggestions:', error);
            showSuggestions([]);
        }
    }
    
    // Handle input changes
    input.addEventListener('input', function() {
        const query = this.value.trim();
        
        // Clear previous timer
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        
        // Set new timer for debouncing
        debounceTimer = setTimeout(() => {
            fetchSuggestions(query);
        }, 300);
    });
    
    // Handle focus
    input.addEventListener('focus', function() {
        createSuggestionsContainer();
        const query = this.value.trim();
        if (query.length >= 2) {
            fetchSuggestions(query);
        }
    });
    
    // Handle blur (hide suggestions after a delay)
    input.addEventListener('blur', function() {
        setTimeout(() => {
            if (suggestionsContainer) {
                suggestionsContainer.style.display = 'none';
            }
        }, 200);
    });
    
    // Handle escape key
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && suggestionsContainer) {
            suggestionsContainer.style.display = 'none';
        }
    });
}

// Back to Top Button Functionality
function initializeBackToTop() {
    // Create back to top button
    const backToTopBtn = document.createElement('button');
    backToTopBtn.id = 'backToTopBtn';
    backToTopBtn.className = 'btn btn-primary position-fixed';
    backToTopBtn.style.cssText = 'bottom: 20px; right: 20px; z-index: 1000; display: none; border-radius: 50%; width: 50px; height: 50px; padding: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
    backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopBtn.title = 'Back to Top';
    
    // Add to body
    document.body.appendChild(backToTopBtn);
    
    // Show/hide button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.style.display = 'block';
            backToTopBtn.classList.add('fade-in');
        } else {
            backToTopBtn.style.display = 'none';
            backToTopBtn.classList.remove('fade-in');
        }
    });
    
    // Smooth scroll to top when clicked
    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Add hover effects
    backToTopBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
        this.style.boxShadow = '0 6px 16px rgba(0,0,0,0.2)';
    });
    
    backToTopBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    });
}

// Bookmark Functionality
function initializeBookmarks() {
    console.log('Initializing bookmarks...');
    console.log('User logged in:', isLoggedIn);
    console.log('Current user:', currentUser);
    
    // Add click event listeners to all bookmark buttons (both old and new classes)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.bookmark-btn, .bookmark-btn-icon')) {
            e.preventDefault();
            const button = e.target.closest('.bookmark-btn, .bookmark-btn-icon');
            const postId = button.dataset.postId;
            
            if (!postId) {
                console.error('No post ID found for bookmark button');
                return;
            }
            
            handleBookmarkClick(button, postId);
        }
    });
    
    // Update bookmark button states for logged-in users
    if (isLoggedIn && currentUser) {
        console.log('User is logged in, updating bookmark button states...');
        // Add a small delay to ensure DOM is fully loaded
        setTimeout(() => {
            updateBookmarkButtonStates();
        }, 100);
    } else {
        console.log('User not logged in, skipping bookmark state update');
    }
}

async function handleBookmarkClick(button, postId) {
    if (!isLoggedIn) {
        showLoginModal();
        return;
    }
    
    try {
        const isCurrentlyBookmarked = button.classList.contains('bookmarked');
        const method = isCurrentlyBookmarked ? 'DELETE' : 'POST';
        
        const response = await fetch('api/bookmarks.php', {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ post_id: postId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update button state
            updateBookmarkButton(button, data.isBookmarked);
            
            // Show success message
            const message = data.isBookmarked ? 'Post bookmarked successfully!' : 'Bookmark removed successfully!';
            showAlert(message, 'success');
            
            // Update bookmark count if displayed
            updateBookmarkCount(postId, data.bookmarkCount);
        } else {
            showAlert(data.error || 'Failed to update bookmark', 'error');
        }
    } catch (error) {
        console.error('Error handling bookmark:', error);
        showAlert('An error occurred while updating the bookmark', 'error');
    }
}

function updateBookmarkButton(button, isBookmarked) {
    const icon = button.querySelector('i');
    const title = button.getAttribute('title');
    
    if (isBookmarked) {
        button.classList.add('bookmarked');
        // Handle both Bootstrap and custom CSS classes
        if (button.classList.contains('btn-outline-secondary')) {
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-primary');
        }
        // For the new icon-only button, the CSS will handle the styling
        icon.className = 'fas fa-bookmark';
        button.setAttribute('title', 'Remove bookmark');
    } else {
        button.classList.remove('bookmarked');
        // Handle both Bootstrap and custom CSS classes
        if (button.classList.contains('btn-primary')) {
            button.classList.remove('btn-primary');
            button.classList.add('btn-outline-secondary');
        }
        // For the new icon-only button, the CSS will handle the styling
        icon.className = 'far fa-bookmark';
        button.setAttribute('title', 'Bookmark');
    }
}

function updateBookmarkButtonStates() {
    console.log('Updating bookmark button states...');
    // Get all bookmark buttons and check their current state (both old and new classes)
    const bookmarkButtons = document.querySelectorAll('.bookmark-btn, .bookmark-btn-icon');
    console.log('Found', bookmarkButtons.length, 'bookmark buttons to update');
    
    bookmarkButtons.forEach(async (button, index) => {
        const postId = button.dataset.postId;
        console.log(`Button ${index}: post ID = ${postId}`);
        
        if (postId) {
            try {
                console.log(`Checking bookmark status for post ${postId}...`);
                const response = await fetch(`api/bookmarks.php?action=check&post_id=${postId}`);
                const data = await response.json();
                console.log(`Bookmark check response for post ${postId}:`, data);
                
                if (data.success) {
                    console.log(`Updating button for post ${postId} to bookmarked: ${data.isBookmarked}`);
                    updateBookmarkButton(button, data.isBookmarked);
                } else {
                    console.warn(`Bookmark check failed for post ${postId}:`, data.error);
                }
            } catch (error) {
                console.error('Error checking bookmark state for post', postId, ':', error);
            }
        } else {
            console.warn(`Button ${index} has no post ID`);
        }
    });
}

function updateBookmarkCount(postId, count) {
    // Find and update bookmark count display if it exists
    const countElement = document.querySelector(`[data-bookmark-count="${postId}"]`);
    if (countElement) {
        countElement.textContent = count;
    }
}

function showAlert(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}