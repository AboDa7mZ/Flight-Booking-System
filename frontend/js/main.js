/**
 * Main JavaScript - UI Interactions & Animations
 */

// Available cities
const cities = [
    'New York',
    'London',
    'Los Angeles',
    'Honolulu',
    'Tokyo',
    'Miami',
    'Paris',
    'Chicago',
    'Dubai',
    'Boston',
    'Berlin',
    'San Francisco',
    'Seattle',
    'Las Vegas',
    'Orlando',
    'Rome',
    'Barcelona',
    'Amsterdam',
    'Singapore',
    'Hong Kong',
    'Sydney',
    'Toronto',
    'Vancouver',
    'Mexico City',
    'Bangkok'
];

// Load cities into select dropdowns
function loadCities() {
    const fromSelect = document.getElementById('from');
    const toSelect = document.getElementById('to');
    
    if (fromSelect && toSelect) {
        cities.forEach(city => {
            const option1 = new Option(city, city);
            const option2 = new Option(city, city);
            fromSelect.add(option1);
            toSelect.add(option2);
        });
    }
}

// Swap cities function
function swapCities() {
    const fromSelect = document.getElementById('from');
    const toSelect = document.getElementById('to');
    
    if (fromSelect && toSelect) {
        const temp = fromSelect.value;
        fromSelect.value = toSelect.value;
        toSelect.value = temp;
    }
}

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
    // If elements don't exist, just continue - no error
}

// Logout Function
function logout() {
    localStorage.removeItem('authToken');
    localStorage.removeItem('userData');
    localStorage.removeItem('userId');
    localStorage.removeItem('userName');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userRole');
    window.location.href = 'index.html';
}

// Load Airports
async function loadAirports() {
    console.log('loadAirports function called');
    try {
        const response = await fetch('http://localhost/backend/api/airports.php');
        const data = await response.json();
        console.log('Airports data received:', data);
        
        const fromSelect = document.getElementById('from');
        const toSelect = document.getElementById('to');
        
        console.log('From select element:', fromSelect);
        console.log('To select element:', toSelect);
        
        if (fromSelect && toSelect && data.airports && Array.isArray(data.airports)) {
            console.log('Adding', data.airports.length, 'airports to dropdowns');
            data.airports.forEach(airport => {
                const option1 = document.createElement('option');
                option1.value = airport.airport_id;
                option1.textContent = `${airport.city} (${airport.airport_code}) - ${airport.airport_name}`;
                fromSelect.appendChild(option1);
                
                const option2 = document.createElement('option');
                option2.value = airport.airport_id;
                option2.textContent = `${airport.city} (${airport.airport_code}) - ${airport.airport_name}`;
                toSelect.appendChild(option2);
            });
            console.log('Airports loaded successfully');
        } else {
            console.error('Required elements or data not found', {
                fromSelect: !!fromSelect,
                toSelect: !!toSelect,
                hasAirports: !!(data.airports),
                isArray: Array.isArray(data.airports)
            });
        }
    } catch (error) {
        console.error('Error loading airports:', error);
    }
}

// Flight Search Form
const flightSearchForm = document.getElementById('flightSearchForm');
if (flightSearchForm) {
    flightSearchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const from = document.getElementById('from').value;
        const to = document.getElementById('to').value;
        
        if (!from || !to) {
            alert('Please select both departure and destination cities');
            return;
        }
        
        if (from === to) {
            alert('Departure and destination cities must be different');
            return;
        }
        
        // Check if user is logged in
        const userId = localStorage.getItem('userId');
        const userType = localStorage.getItem('userType');
        
        if (!userId) {
            alert('Please login to search for flights');
            window.location.href = 'login.html';
            return;
        }
        
        if (userType === 'passenger') {
            window.location.href = `search-flights.html?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;
        } else {
            alert('Please login as a passenger to search flights');
            window.location.href = 'login.html';
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded');
    checkAuth();
    loadCities();
    
    // Give a small delay to ensure all elements are rendered
    setTimeout(() => {
        loadAirports();
    }, 100);
    
    // Add animation delays to feature cards
    document.querySelectorAll('.feature-card').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
    
    // Add animation delays to destination cards
    document.querySelectorAll('.destination-card').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
});
