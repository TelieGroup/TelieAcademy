// TechTutorial Blog JavaScript

// Dark Mode Toggle
document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;
    
    // Check for saved dark mode preference
    const savedMode = localStorage.getItem('darkMode');
    if (savedMode === 'dark') {
        body.classList.add('dark-mode');
        updateDarkModeIcon(true);
    }
    
    // Dark mode toggle functionality
    darkModeToggle.addEventListener('click', function() {
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
        if (isDark) {
            icon.className = 'fas fa-sun';
        } else {
            icon.className = 'fas fa-moon';
        }
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
            showUserInfo(data.user);
        } else {
            console.log('User is not logged in, showing login button');
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
    const messageDiv = document.getElementById('registerMessage');
    
    fetch('api/auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'register',
            username: username,
            email: email,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            
            // Switch to login modal
            const registerModal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
            const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            registerModal.hide();
            loginModal.show();
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
    fetch('api/auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ action: 'logout' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            showLoginButton();
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Logout error:', error);
        showAlert('Logout failed. Please try again.', 'danger');
    });
}

function showUserInfo(user) {
    console.log('Showing user info for:', user);
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
}

function showLoginButton() {
    console.log('Showing login button');
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
            subscribeNewsletter();
        });
    }
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
                          document.getElementById('modalNewsletterMessage');
        
        showAlert(data.message, data.success ? 'success' : 'warning', messageDiv);
        
        if (data.success) {
            // Clear form
            const form = document.getElementById('newsletterForm') || 
                        document.getElementById('modalNewsletterForm');
            if (form) form.reset();
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('newsletterModal'));
            if (modal) modal.hide();
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