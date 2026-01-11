/**
 * API Integration Module
 * Handles all backend communication
 */

const API_BASE_URL = 'http://localhost/backend/api';

// API Helper Function
async function apiRequest(endpoint, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    // Add auth token if available
    const token = localStorage.getItem('authToken');
    if (token) {
        defaultOptions.headers['Authorization'] = `Bearer ${token}`;
    }
    
    const config = { ...defaultOptions, ...options };
    
    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'API request failed');
        }
        
        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Authentication APIs
const AuthAPI = {
    async login(email, password) {
        return apiRequest('/auth.php?action=login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });
    },
    
    async register(userData) {
        return apiRequest('/auth.php?action=register', {
            method: 'POST',
            body: JSON.stringify(userData)
        });
    },
    
    async logout() {
        return apiRequest('/auth.php?action=logout', {
            method: 'POST'
        });
    }
};

// Flights APIs
const FlightsAPI = {
    async search(searchParams) {
        const queryString = new URLSearchParams(searchParams).toString();
        return apiRequest(`/flights.php?action=search&${queryString}`);
    },
    
    async getFlightById(flightId) {
        return apiRequest(`/flights.php?id=${flightId}`);
    },
    
    async getPopularRoutes() {
        return apiRequest('/flights.php?action=popular');
    },
    
    async getAllFlights() {
        return apiRequest('/flights.php?action=all');
    }
};

// Airports APIs
const AirportsAPI = {
    async getAll() {
        return apiRequest('/airports.php');
    },
    
    async getById(airportId) {
        return apiRequest(`/airports.php?id=${airportId}`);
    }
};

// Bookings APIs
const BookingsAPI = {
    async create(bookingData) {
        return apiRequest('/bookings.php?action=create', {
            method: 'POST',
            body: JSON.stringify(bookingData)
        });
    },
    
    async getByUser(userId) {
        return apiRequest(`/bookings.php?user_id=${userId}`);
    },
    
    async getByReference(reference) {
        return apiRequest(`/bookings.php?reference=${reference}`);
    },
    
    async cancel(bookingId) {
        return apiRequest('/bookings.php?action=cancel', {
            method: 'PUT',
            body: JSON.stringify({ booking_id: bookingId })
        });
    },
    
    async updatePayment(bookingId, paymentData) {
        return apiRequest('/bookings.php?action=payment', {
            method: 'PUT',
            body: JSON.stringify({
                booking_id: bookingId,
                ...paymentData
            })
        });
    }
};

// Load Airports for Dropdowns
async function loadAirports() {
    try {
        const response = await AirportsAPI.getAll();
        if (response.success) {
            return response.data;
        }
        return [];
    } catch (error) {
        console.error('Error loading airports:', error);
        return [];
    }
}

// Populate Airport Dropdowns
async function populateAirportSelects() {
    const fromSelect = document.getElementById('from');
    const toSelect = document.getElementById('to');
    
    if (!fromSelect || !toSelect) return;
    
    try {
        const airports = await loadAirports();
        
        airports.forEach(airport => {
            const option1 = document.createElement('option');
            option1.value = airport.airport_id;
            option1.textContent = `${airport.city} (${airport.iata_code}) - ${airport.airport_name}`;
            fromSelect.appendChild(option1);
            
            const option2 = document.createElement('option');
            option2.value = airport.airport_id;
            option2.textContent = `${airport.city} (${airport.iata_code}) - ${airport.airport_name}`;
            toSelect.appendChild(option2);
        });
    } catch (error) {
        console.error('Error populating airports:', error);
    }
}

// Search Flights
async function searchFlights(searchParams) {
    try {
        const response = await FlightsAPI.search({
            origin_airport_id: searchParams.from,
            destination_airport_id: searchParams.to,
            departure_date: searchParams.departure,
            class: searchParams.class,
            passengers: searchParams.passengers
        });
        
        if (response.success) {
            return response.data;
        }
        return [];
    } catch (error) {
        console.error('Error searching flights:', error);
        throw error;
    }
}

// User Authentication Helper
function isAuthenticated() {
    return !!localStorage.getItem('authToken');
}

function getUserData() {
    const userData = localStorage.getItem('userData');
    return userData ? JSON.parse(userData) : null;
}

function saveAuthData(token, userData) {
    localStorage.setItem('authToken', token);
    localStorage.setItem('userData', JSON.stringify(userData));
}

function clearAuthData() {
    localStorage.removeItem('authToken');
    localStorage.removeItem('userData');
}

// Format Currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Format Date
function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Format Time
function formatTime(dateString) {
    return new Date(dateString).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Calculate Flight Duration
function calculateDuration(departure, arrival) {
    const diff = new Date(arrival) - new Date(departure);
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    return `${hours}h ${minutes}m`;
}

// Initialize API functions on page load
document.addEventListener('DOMContentLoaded', () => {
    // Populate airports on pages with search forms
    if (document.querySelector('#from') || document.querySelector('#to')) {
        populateAirportSelects();
    }
});

// Export API modules for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        AuthAPI,
        FlightsAPI,
        AirportsAPI,
        BookingsAPI,
        isAuthenticated,
        getUserData,
        saveAuthData,
        clearAuthData,
        formatCurrency,
        formatDate,
        formatTime,
        calculateDuration
    };
}
