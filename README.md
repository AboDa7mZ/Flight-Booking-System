# SkyVoyage Flight Booking Website

A premium, full-featured flight booking platform built with PHP backend and modern frontend technologies.

## 🚀 Features

### User Features
- **Flight Search**: Advanced search with multiple filters (origin, destination, dates, class, passengers)
- **User Authentication**: Secure registration and login system
- **Booking Management**: Create, view, and cancel flight bookings
- **Payment Integration**: Secure payment processing (Stripe ready)
- **User Dashboard**: View booking history and manage profile
- **Flight Reviews**: Rate and review flights
- **Promotional Codes**: Apply discount codes to bookings
- **Email Notifications**: Booking confirmations and updates

### Admin Features
- **Flight Management**: Add, edit, and delete flights
- **Booking Oversight**: View and manage all bookings
- **User Management**: Manage user accounts
- **Revenue Reports**: View booking statistics and revenue
- **Airport & Airline Management**: Manage airport and airline data

### Technical Features
- **RESTful API Architecture**: Clean API endpoints for all operations
- **Database Transactions**: Safe booking process with rollback capability
- **Stored Procedures**: Optimized database operations
- **Triggers**: Automatic updates for seat availability and booking status
- **Database Views**: Pre-computed reports for better performance
- **Session Management**: Secure token-based authentication
- **Responsive Design**: Mobile-first, fully responsive UI

## 📁 Project Structure

```
Flight Booking Website/
├── backend/
│   ├── api/
│   │   ├── auth.php              # Authentication endpoints
│   │   ├── flights.php           # Flight search and management
│   │   ├── bookings.php          # Booking operations
│   │   └── airports.php          # Airport data
│   ├── config/
│   │   ├── database.php          # Database connection
│   │   └── config.php            # App configuration
│   └── models/
│       ├── User.php              # User model
│       ├── Flight.php            # Flight model
│       └── Booking.php           # Booking model
├── frontend/
│   ├── css/
│   │   └── style.css             # Premium styling
│   ├── js/
│   │   ├── main.js               # UI interactions
│   │   └── api.js                # API integration
│   ├── index.html                # Landing page
│   ├── login.html                # Login page
│   ├── register.html             # Registration page
│   └── search-results.html       # Flight results
└── database/
    └── flight_booking.sql        # Database schema
```

## 🛠️ Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/LAMP (recommended for local development)

### Step 1: Clone or Download
Download the project to your local machine and place it in your web server directory:
- **XAMPP**: `C:\xampp\htdocs\`
- **WAMP**: `C:\wamp64\www\`
- **LAMP**: `/var/www/html/`

### Step 2: Database Setup

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `flight_booking_system`
3. Import the SQL file:
   - Click on the database
   - Go to "Import" tab
   - Choose file: `database/flight_booking.sql`
   - Click "Go" to import

4. Verify the database contains these tables:
   - users
   - flights
   - bookings
   - passengers
   - payments
   - airlines
   - airports
   - aircraft
   - reviews
   - notifications
   - promotional_offers
   - user_sessions
   - contact_messages

### Step 3: Configure Backend

1. Open `backend/config/database.php`
2. Update database credentials if needed:
```php
private $host = "localhost";
private $db_name = "flight_booking_system";
private $username = "root";
private $password = "";  // Your MySQL password
```

3. Open `backend/config/config.php`
4. Update configuration as needed (JWT secret, SMTP settings, etc.)

### Step 4: Configure Frontend

1. Open `frontend/js/api.js`
2. Update the API base URL if needed:
```javascript
const API_BASE_URL = 'http://localhost/Flight%20Booking%20Website/backend/api';
```

### Step 5: Start the Server

#### Using XAMPP:
1. Start Apache and MySQL from XAMPP Control Panel
2. Access the website: `http://localhost/Flight%20Booking%20Website/frontend/index.html`

#### Using PHP Built-in Server:
```bash
cd "Flight Booking Website/backend"
php -S localhost:8000
```
Then access: `http://localhost:8000/../frontend/index.html`

## 🔑 Default Admin Account

After importing the database, use these credentials to login:
- **Email**: admin@skyvoyage.com
- **Password**: admin123

## 📚 API Documentation

### Authentication

#### Register
```
POST /backend/api/auth.php?action=register
Body: {
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "password": "password123"
}
```

#### Login
```
POST /backend/api/auth.php?action=login
Body: {
  "email": "john@example.com",
  "password": "password123"
}
```

### Flights

#### Search Flights
```
GET /backend/api/flights.php?action=search&origin_airport_id=1&destination_airport_id=2&departure_date=2024-01-15&class=economy&passengers=2
```

#### Get Flight by ID
```
GET /backend/api/flights.php?id=1
```

### Bookings

#### Create Booking
```
POST /backend/api/bookings.php?action=create
Headers: Authorization: Bearer {token}
Body: {
  "user_id": 1,
  "flight_id": 1,
  "passengers": [
    {
      "first_name": "John",
      "last_name": "Doe",
      "date_of_birth": "1990-01-01",
      "passport_number": "AB123456",
      "nationality": "US"
    }
  ],
  "seat_class": "economy"
}
```

#### Get User Bookings
```
GET /backend/api/bookings.php?user_id=1
Headers: Authorization: Bearer {token}
```

#### Cancel Booking
```
PUT /backend/api/bookings.php?action=cancel
Headers: Authorization: Bearer {token}
Body: {
  "booking_id": 1
}
```

### Airports

#### Get All Airports
```
GET /backend/api/airports.php
```

## 🎨 Design System

### Colors
- **Primary**: #1a73e8 (Blue)
- **Secondary**: #34a853 (Green)
- **Accent**: #fbbc04 (Yellow)
- **Danger**: #ea4335 (Red)

### Typography
- **Primary Font**: Inter
- **Display Font**: Playfair Display

### Components
- Buttons with hover effects and shadows
- Cards with elevation and transitions
- Forms with focus states
- Responsive navigation
- Modal dialogs
- Loading states

## 🔒 Security Features

- **Password Hashing**: BCrypt with cost factor 10
- **SQL Injection Prevention**: Prepared statements with PDO
- **Session Management**: Secure token-based authentication
- **CORS Protection**: Configured CORS headers
- **Input Validation**: Server-side validation for all inputs
- **XSS Prevention**: Sanitized outputs

## 🧪 Testing

### Sample Test Data

The database includes sample data:
- **8 Airlines**: American, British Airways, Emirates, etc.
- **10 Airports**: JFK, LHR, DXB, LAX, etc.
- **5 Aircraft Types**: Boeing 737, Airbus A320, etc.
- **4 Users**: Including 1 admin account
- **5 Sample Flights**: Various routes and times
- **3 Promo Codes**: SUMMER2024, FIRSTFLIGHT, etc.

### Test Workflow
1. Register a new user account
2. Login with credentials
3. Search for flights (e.g., JFK to LHR)
4. Select a flight
5. Complete booking with passenger details
6. View booking in dashboard
7. (Optional) Cancel booking

## 📱 Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## 🚧 Future Enhancements

- [ ] Multi-city flight search
- [ ] Seat selection interface
- [ ] Real-time flight status updates
- [ ] Mobile app (React Native)
- [ ] Advanced analytics dashboard
- [ ] Multi-currency support
- [ ] Multi-language support
- [ ] Social media integration
- [ ] Loyalty program
- [ ] Push notifications

## 📝 License

This project is created for educational purposes.

## 👥 Support

For issues or questions:
1. Check the API documentation above
2. Review the database schema in `database/flight_booking.sql`
3. Check browser console for JavaScript errors
4. Review PHP error logs

## 🎯 Development Tips

### Adding New Flights
Use phpMyAdmin or the admin panel to add flights. Ensure:
- Valid airline_id and aircraft_id references
- origin_airport_id and destination_airport_id exist
- departure_time is before arrival_time
- available_seats <= total_capacity

### Testing Bookings
1. Make sure flights have available seats
2. Use valid user_id (must be logged in)
3. Passenger count must match flight capacity
4. Test with promotional codes for discounts

### Debugging API
1. Enable error reporting in PHP:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```
2. Check browser Network tab for API responses
3. Use Postman for API testing

## 🌟 Credits

- Icons: Font Awesome
- Fonts: Google Fonts (Inter, Playfair Display)
- Images: Unsplash (placeholder backgrounds)

---

**Built with ❤️ for premium flight booking experiences**
