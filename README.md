# Flight Booking System

A comprehensive web-based flight booking platform that allows passengers to search and book flights, and airline companies to manage their flight operations.

## 📋 Table of Contents
- [Features](#features)
- [Project Structure](#project-structure)
- [Technologies Used](#technologies-used)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Endpoints](#api-endpoints)

## Features

### For Passengers
- Search and book flights
- View booking history
- Manage profile
- Check account balance
- Send messages to airline companies
- Cancel bookings

### For Companies
- Add and manage flights
- View flight details and passenger bookings
- Manage company profile
- Handle customer messages
- Cancel flights

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Architecture**: REST API

## Project Structure

```
flight-booking-website/
├── backend/
│   ├── api/              # API endpoints
│   │   ├── auth.php              # User authentication
│   │   ├── register.php          # User registration
│   │   ├── flights.php           # Flight search & listing
│   │   ├── search-flights.php    # Advanced flight search
│   │   ├── add-flight.php        # Add new flights (Company)
│   │   ├── cancel-flight.php     # Cancel flights (Company)
│   │   ├── company-flights.php    # Company flight management
│   │   ├── company-flight-details.php
│   │   ├── bookings.php          # Create bookings
│   │   ├── passenger-bookings.php # View passenger bookings
│   │   ├── cancel-booking.php     # Cancel bookings
│   │   ├── messages.php            # Messaging system
│   │   ├── get-profile.php         # Get user profile
│   │   ├── update-profile.php      # Update user profile
│   │   ├── change-password.php     # Change password
│   │   ├── get-cities.php          # Get cities
│   │   ├── get-companies.php        # Get airline companies
│   │   └── airports.php             # Airports data
│   │
│   ├── config/
│   │   ├── config.php              # Configuration settings
│   │   └── database.php            # Database connection
│   │
│   └── models/
│       ├── Booking.php             # Booking model
│       ├── Flight.php              # Flight model
│       └── User.php                # User model
│
├── database/
│   └── flight_booking.sql         # Main database schema
│
└── frontend/
    ├── *.html                      # Various HTML pages
    ├── components/                 # Reusable UI components
    │   ├── company-navbar.html
    │   └── passenger-navbar.html
    ├── css/
    │   └── style.css              # Main stylesheet
    └── js/
        ├── api.js                  # API integration
        ├── main.js                 # UI interactions
        └── navbar-loader.js        # Navigation component loader
```

## Features

### User Types
- **Passengers**: Can search and book flights, manage their bookings, view profile and balance
- **Companies**: Can manage flights (add/cancel), view bookings, and access company-specific features

### Core Functionality
- **User Authentication**: Login, registration, and profile management
- **Flight Search**: Search flights by origin, destination, date, and number of passengers
- **Flight Booking**: Book flights with seat selection and payment processing
- **Company Management**: Airlines can add flights, view bookings, and manage their flight schedules
- **Passenger Features**: View bookings, manage profile, search and book flights
- **Messaging System**: Communication between companies and passengers

## 🛠️ Tech Stack

### Frontend
- **HTML5, CSS3, JavaScript**
- Pure vanilla JavaScript (no frameworks)
- Responsive design with mobile support
- Font Awesome icons

### Backend
- **PHP 7.4+**
- RESTful API architecture
- PDO for database operations
- Password hashing with bcrypt

### Database
- **MySQL/MariaDB**
- Normalized database schema
- Stored procedures and views for complex queries

## 📁 Project Structure

```
Flight-Booking-System/
├── frontend/                  # Client-side files
│   ├── index.html            # Landing page
│   ├── login.html            # User login
│   ├── register.html         # User registration
│   ├── passenger-home.html   # Passenger dashboard
│   ├── passenger-profile.html
│   ├── company-home.html      # Company dashboard
│   ├── company-profile.html
│   ├── search-flights.html
│   ├── search-results.html
│   ├── booking.html
│   ├── flight-info.html
│   ├── add-flight.html
│   ├── company-flight-details.html
│   ├── company-messages.html
│   ├── messages.html
│   ├── components/
│   │   ├── company-navbar.html
│   │   └── passenger-navbar.html
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── api.js
│       ├── main.js
│       └── navbar-loader.js
│
├── backend/
│   ├── api/
│   │   ├── add-flight.php
│   │   ├── airports.php
│   │   ├── auth.php
│   │   ├── bookings.php
│   │   ├── cancel-booking.php
│   │   ├── cancel-flight.php
│   │   ├── change-password.php
│   │   ├── company-flight-details.php
│   │   ├── company-flights.php
│   │   ├── flights.php
│   │   ├── get-cities.php
│   │   ├── get-companies.php
│   │   ├── get-profile.php
│   │   ├── messages.php
│   │   ├── passenger-bookings.php
│   │   ├── register.php
│   │   ├── search-flights.php
│   │   └── update-profile.php
│   ├── config/
│   │   ├── config.php
│   │   └── database.php
│   └── models/
│       ├── Booking.php
│       ├── Flight.php
│       └── User.php
├── database/
│   └── flight_booking.sql
├── frontend/
│   ├── add-flight.html
│   ├── booking.html
│   ├── company-flight-details.html
│   ├── company-home.html
│   ├── company-messages.html
│   ├── company-profile.html
│   ├── flight-info.html
│   ├── index.html
│   ├── login.html
│   ├── messages.html
│   ├── passenger-home.html
│   ├── passenger-profile.html
│   ├── register.html
│   ├── search-flights.html
│   ├── search-results.html
│   ├── booking.html
│   ├── components/
│   │   ├── company-navbar.html
│   │   └── passenger-navbar.html
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── api.js
│       ├── main.js
│       └── navbar-loader.js
├── database/
│   └── flight_booking.sql
└── README.md
```

## Features

### For Passengers
- **User Registration & Authentication**: Secure account creation and login system
- **Flight Search**: Search flights by origin, destination, and dates
- **Booking Management**: Book flights, view bookings, and cancel reservations
- **Profile Management**: Update personal information and account settings
- **Account Balance**: Manage wallet balance for bookings
- **Messaging**: Communicate with airline companies

### For Airline Companies
- **Company Dashboard**: Manage flights and view statistics
- **Flight Management**: Add, update, and cancel flights
- **Booking Overview**: View all bookings for company flights
- **Company Profile**: Update company information
- **Messaging**: Communicate with passengers

## Technology Stack

### Frontend
- **HTML5**: Semantic markup and structure
- **CSS3**: Modern styling with responsive design
- **JavaScript (ES6+)**: Interactive UI and API integration
- **Font Awesome**: Icon library

### Backend
- **PHP 7.4+**: Server-side logic
- **MySQL/MariaDB**: Database management
- **PDO**: Database abstraction layer
- **RESTful API**: Clean API architecture

## Project Structure

```
Flight-Booking-Website/
├── frontend/                 # Frontend files
│   ├── index.html           # Landing page
│   ├── login.html           # Login page
│   ├── register.html        # Registration page
│   ├── passenger-home.html  # Passenger dashboard
│   ├── company-home.html    # Company dashboard
│   ├── search-flights.html  # Flight search interface
│   ├── search-results.html  # Search results display
│   ├── booking.html         # Booking confirmation
│   ├── flight-info.html     # Flight details
│   ├── add-flight.html      # Add new flight (company)
│   ├── company-flight-details.html  # Flight management
│   ├── passenger-profile.html       # Passenger profile
│   ├── company-profile.html         # Company profile
│   ├── messages.html        # Passenger messaging
│   ├── company-messages.html        # Company messaging
│   ├── components/          # Reusable components
│   │   ├── passenger-navbar.html
│   │   └── company-navbar.html
│   ├── css/
│   │   └── style.css        # Main stylesheet
│   └── js/
│       ├── api.js           # API integration
│       ├── main.js          # UI interactions
│       └── navbar-loader.js # Navigation component loader
│
├── backend/                 # Backend files
│   ├── api/                 # API endpoints
│   │   ├── auth.php         # Authentication API
│   │   ├── register.php     # User registration
│   │   ├── flights.php      # Flight operations
│   │   ├── search-flights.php       # Flight search
│   │   ├── bookings.php     # Booking operations
│   │   ├── passenger-bookings.php   # Passenger bookings
│   │   ├── cancel-booking.php       # Cancel bookings
│   │   ├── add-flight.php   # Add new flights
│   │   ├── company-flights.php      # Company flight list
│   │   ├── company-flight-details.php  # Flight details
│   │   ├── cancel-flight.php        # Cancel flights
│   │   ├── airports.php     # Airport data
│   │   ├── get-cities.php   # City data
│   │   ├── get-companies.php        # Company data
│   │   ├── get-profile.php  # User profile
│   │   ├── update-profile.php       # Update profile
│   │   ├── change-password.php      # Change password
│   │   └── messages.php     # Messaging system
│   │
│   ├── config/              # Configuration files
│   │   ├── database.php     # Database connection
│   │   └── config.php       # General configuration
│   │
│   └── models/              # Data models
│       ├── User.php         # User model
│       ├── Flight.php       # Flight model
│       └── Booking.php      # Booking model
│
└── database/                # Database files
    └── flight_booking.sql   # Database schema and sample data
```

## Installation

### Prerequisites
- **Web Server**: Apache, Nginx, or similar
- **PHP**: Version 7.4 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Optional**: XAMPP, WAMP, or MAMP for local development

### Setup Instructions

1. **Clone or Download the Repository**
   ```bash
   git clone https://github.com/yourusername/Flight-Booking-Website.git
   cd Flight-Booking-Website
   ```

2. **Database Setup**
   - Create a new database named `flight_booking_system`
   - Import the database schema:
     ```bash
     mysql -u your_username -p flight_booking_system < database/flight_booking.sql
     ```
   - Or use phpMyAdmin to import `database/flight_booking.sql`

3. **Configure Database Connection**
   - Open `backend/config/database.php`
   - Update the database credentials:
     ```php
     private $host = "localhost";
     private $db_name = "flight_booking_system";
     private $username = "your_username";
     private $password = "your_password";
     ```

4. **Update API Base URL**
   - Open `frontend/js/api.js`
   - Update the API base URL to match your setup:
     ```javascript
     const API_BASE_URL = 'http://localhost/Flight-Booking-Website/backend/api';
     ```

5. **Start the Web Server**
   - If using XAMPP/WAMP/MAMP, place the project in the `htdocs` directory
   - Start Apache and MySQL services
   - Access the application at `http://localhost/Flight-Booking-Website/frontend/`

## Usage

### For Passengers

1. **Register an Account**
   - Navigate to the registration page
   - Fill in your details and select "Passenger" as account type
   - Submit the form to create your account

2. **Login**
   - Use your email and password to login
   - You'll be redirected to the passenger dashboard

3. **Search for Flights**
   - Use the search form on the homepage or dashboard
   - Select origin, destination, and travel dates
   - Browse available flights

4. **Book a Flight**
   - Select a flight from search results
   - Confirm booking details
   - Payment will be deducted from your account balance

5. **Manage Bookings**
   - View all your bookings from the dashboard
   - Cancel bookings if needed (refund applies)

### For Airline Companies

1. **Register a Company Account**
   - Navigate to the registration page
   - Fill in company details and select "Company" as account type
   - Submit the form to create your account

2. **Login**
   - Use your company email and password to login
   - You'll be redirected to the company dashboard

3. **Add Flights**
   - Navigate to "Add Flight" page
   - Fill in flight details (route, schedule, pricing, capacity)
   - Submit to add the flight to the system

4. **Manage Flights**
   - View all your company's flights
   - Update flight details or cancel flights
   - View booking statistics for each flight

## Database Schema

### Key Tables

- **users**: Stores user accounts (both passengers and companies)
- **flights**: Contains flight information
- **bookings**: Manages flight reservations
- **messages**: Handles communication between users and companies
- **airports**: Airport information and codes

## API Endpoints

### Authentication
- `POST /auth.php` - User login
- `POST /register.php` - User registration

### Flights
- `GET /flights.php` - Get all flights
- `POST /search-flights.php` - Search flights
- `POST /add-flight.php` - Add new flight (company only)
- `GET /company-flights.php` - Get company flights
- `POST /cancel-flight.php` - Cancel flight (company only)

### Bookings
- `POST /bookings.php` - Create booking
- `GET /passenger-bookings.php` - Get passenger bookings
- `POST /cancel-booking.php` - Cancel booking

### Profile & Settings
- `GET /get-profile.php` - Get user profile
- `POST /update-profile.php` - Update profile
- `POST /change-password.php` - Change password

## Security Features

- Password hashing using `PASSWORD_BCRYPT`
- SQL injection prevention using PDO prepared statements
- Input validation and sanitization
- CORS headers for API security
- Session management for authentication

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Opera (latest)

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a new branch (`git checkout -b feature/YourFeature`)
3. Commit your changes (`git commit -m 'Add some feature'`)
4. Push to the branch (`git push origin feature/YourFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please email: support@flightbooking.com

## Acknowledgments

- Font Awesome for icons
- Google Fonts for typography
- The PHP and MySQL communities

---

**Note**: This is a demonstration project for educational purposes. For production use, additional security measures and optimizations should be implemented.
