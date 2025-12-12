/**
 * Main JavaScript - UI Interactions & Animations
 */

// Mobile Menu Toggle
const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
const navMenu = document.querySelector('.nav-menu');

if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        mobileMenuToggle.classList.toggle('active');
    });
}

// Search Tabs
const tabButtons = document.querySelectorAll('.tab-btn');
const tabContents = document.querySelectorAll('.tab-content');

tabButtons.forEach(button => {
    button.addEventListener('click', () => {
        const tabId = button.dataset.tab;
        
        // Remove active class from all tabs
        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));
        
        // Add active class to clicked tab
        button.classList.add('active');
        const activeContent = document.querySelector(`#${tabId}`);
        if (activeContent) {
            activeContent.classList.add('active');
        }
    });
});

// Smooth Scroll for Navigation Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Sticky Navbar on Scroll
window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 100) {
        navbar.style.boxShadow = 'var(--shadow-lg)';
    } else {
        navbar.style.boxShadow = 'none';
    }
});

// Stats Counter Animation
function animateCounter(element, target, duration = 2000) {
    let current = 0;
    const increment = target / (duration / 16);
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target.toLocaleString() + '+';
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current).toLocaleString();
        }
    }, 16);
}

// Intersection Observer for Stats
const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const statNumbers = entry.target.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const target = parseInt(stat.dataset.count);
                animateCounter(stat, target);
            });
            statsObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

const statsSection = document.querySelector('.stats-section');
if (statsSection) {
    statsObserver.observe(statsSection);
}

// Fade-in Animation on Scroll
const fadeInObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('fade-in');
            fadeInObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.feature-card, .destination-card').forEach(card => {
    fadeInObserver.observe(card);
});

// Form Validation
const searchForm = document.querySelector('.search-card form');
if (searchForm) {
    searchForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = {
            from: document.getElementById('from').value,
            to: document.getElementById('to').value,
            departure: document.getElementById('departure').value,
            return: document.getElementById('return')?.value,
            passengers: document.getElementById('passengers').value,
            class: document.getElementById('class').value
        };
        
        // Validate form
        if (!formData.from || !formData.to || !formData.departure) {
            alert('Please fill in all required fields');
            return;
        }
        
        if (formData.from === formData.to) {
            alert('Origin and destination cannot be the same');
            return;
        }
        
        // Store search data and redirect to results page
        sessionStorage.setItem('searchData', JSON.stringify(formData));
        window.location.href = 'search-results.html';
    });
}

// Newsletter Form
const newsletterForm = document.querySelector('.newsletter-form');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = newsletterForm.querySelector('input[type="email"]').value;
        
        if (!email) {
            alert('Please enter your email address');
            return;
        }
        
        try {
            // Here you would normally send to backend
            console.log('Newsletter subscription:', email);
            alert('Thank you for subscribing to our newsletter!');
            newsletterForm.reset();
        } catch (error) {
            console.error('Newsletter error:', error);
            alert('Something went wrong. Please try again.');
        }
    });
}

// Date Input Restrictions
const departureInput = document.getElementById('departure');
const returnInput = document.getElementById('return');

if (departureInput) {
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    departureInput.min = today;
    
    departureInput.addEventListener('change', () => {
        if (returnInput) {
            returnInput.min = departureInput.value;
            if (returnInput.value && returnInput.value < departureInput.value) {
                returnInput.value = departureInput.value;
            }
        }
    });
}

// Passenger Selection Counter
const passengersSelect = document.getElementById('passengers');
if (passengersSelect) {
    for (let i = 1; i <= 9; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = i === 1 ? '1 Passenger' : `${i} Passengers`;
        passengersSelect.appendChild(option);
    }
}

// Flight Class Options
const classSelect = document.getElementById('class');
if (classSelect) {
    const classes = [
        { value: 'economy', label: 'Economy' },
        { value: 'premium_economy', label: 'Premium Economy' },
        { value: 'business', label: 'Business Class' },
        { value: 'first', label: 'First Class' }
    ];
    
    classes.forEach(cls => {
        const option = document.createElement('option');
        option.value = cls.value;
        option.textContent = cls.label;
        classSelect.appendChild(option);
    });
}

// Check Authentication Status
function checkAuth() {
    const token = localStorage.getItem('authToken');
    const loginBtn = document.querySelector('.btn-login');
    const signupBtn = document.querySelector('.btn-signup');
    
    if (token && loginBtn && signupBtn) {
        // User is logged in
        loginBtn.textContent = 'Dashboard';
        loginBtn.href = 'dashboard.html';
        signupBtn.textContent = 'Logout';
        signupBtn.addEventListener('click', (e) => {
            e.preventDefault();
            logout();
        });
    }
}

// Logout Function
function logout() {
    localStorage.removeItem('authToken');
    localStorage.removeItem('userData');
    window.location.href = 'index.html';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
    
    // Add animation delays to feature cards
    document.querySelectorAll('.feature-card').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
    
    // Add animation delays to destination cards
    document.querySelectorAll('.destination-card').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
});
