-- ============================================
-- Flight Booking Website Database Schema
-- Database: flight_booking_system
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS flight_booking_system;
USE flight_booking_system;

-- ============================================
-- Table: users
-- Stores user account information
-- ============================================
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    passport_number VARCHAR(50),
    nationality VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    postal_code VARCHAR(20),
    user_role ENUM('customer', 'admin', 'agent') DEFAULT 'customer',
    account_status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_user_role (user_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: airlines
-- Stores airline company information
-- ============================================
CREATE TABLE airlines (
    airline_id INT PRIMARY KEY AUTO_INCREMENT,
    airline_code VARCHAR(10) UNIQUE NOT NULL,
    airline_name VARCHAR(100) NOT NULL,
    country VARCHAR(100),
    logo_url VARCHAR(255),
    website VARCHAR(255),
    contact_phone VARCHAR(20),
    contact_email VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_airline_code (airline_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: airports
-- Stores airport information
-- ============================================
CREATE TABLE airports (
    airport_id INT PRIMARY KEY AUTO_INCREMENT,
    airport_code VARCHAR(10) UNIQUE NOT NULL,
    airport_name VARCHAR(150) NOT NULL,
    city VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    timezone VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_airport_code (airport_code),
    INDEX idx_city_country (city, country)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: aircraft
-- Stores aircraft/plane information
-- ============================================
CREATE TABLE aircraft (
    aircraft_id INT PRIMARY KEY AUTO_INCREMENT,
    aircraft_model VARCHAR(100) NOT NULL,
    manufacturer VARCHAR(100),
    capacity_economy INT,
    capacity_business INT,
    capacity_first_class INT,
    total_capacity INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: flights
-- Stores flight schedule information
-- ============================================
CREATE TABLE flights (
    flight_id INT PRIMARY KEY AUTO_INCREMENT,
    flight_number VARCHAR(20) UNIQUE NOT NULL,
    airline_id INT NOT NULL,
    aircraft_id INT,
    departure_airport_id INT NOT NULL,
    arrival_airport_id INT NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    duration_minutes INT,
    base_price_economy DECIMAL(10, 2),
    base_price_business DECIMAL(10, 2),
    base_price_first_class DECIMAL(10, 2),
    available_seats_economy INT DEFAULT 0,
    available_seats_business INT DEFAULT 0,
    available_seats_first_class INT DEFAULT 0,
    flight_status ENUM('scheduled', 'boarding', 'departed', 'arrived', 'delayed', 'cancelled') DEFAULT 'scheduled',
    gate_number VARCHAR(10),
    terminal VARCHAR(10),
    baggage_allowance_kg INT DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (airline_id) REFERENCES airlines(airline_id),
    FOREIGN KEY (aircraft_id) REFERENCES aircraft(aircraft_id),
    FOREIGN KEY (departure_airport_id) REFERENCES airports(airport_id),
    FOREIGN KEY (arrival_airport_id) REFERENCES airports(airport_id),
    INDEX idx_flight_number (flight_number),
    INDEX idx_departure_time (departure_time),
    INDEX idx_departure_airport (departure_airport_id),
    INDEX idx_arrival_airport (arrival_airport_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: bookings
-- Stores booking/reservation information
-- ============================================
CREATE TABLE bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    flight_id INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    travel_date DATE NOT NULL,
    number_of_passengers INT NOT NULL DEFAULT 1,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('pending', 'paid', 'refunded', 'cancelled') DEFAULT 'pending',
    booking_status ENUM('confirmed', 'pending', 'cancelled', 'completed') DEFAULT 'pending',
    seat_class ENUM('economy', 'business', 'first_class') NOT NULL,
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (flight_id) REFERENCES flights(flight_id),
    INDEX idx_booking_reference (booking_reference),
    INDEX idx_user_id (user_id),
    INDEX idx_booking_date (booking_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: passengers
-- Stores passenger information for bookings
-- ============================================
CREATE TABLE passengers (
    passenger_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    title ENUM('Mr', 'Mrs', 'Ms', 'Dr', 'Miss') NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    passport_number VARCHAR(50) NOT NULL,
    passport_expiry DATE NOT NULL,
    nationality VARCHAR(50) NOT NULL,
    seat_number VARCHAR(10),
    meal_preference ENUM('regular', 'vegetarian', 'vegan', 'halal', 'kosher', 'none') DEFAULT 'regular',
    special_assistance TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: payments
-- Stores payment transaction information
-- ============================================
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    payment_method ENUM('credit_card', 'debit_card', 'paypal', 'bank_transfer') NOT NULL,
    card_type VARCHAR(20),
    card_last_four VARCHAR(4),
    transaction_id VARCHAR(100) UNIQUE,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    billing_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_booking_id (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: reviews
-- Stores customer reviews and ratings
-- ============================================
CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    flight_id INT NOT NULL,
    booking_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    title VARCHAR(100),
    review_text TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_verified BOOLEAN DEFAULT FALSE,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (flight_id) REFERENCES flights(flight_id),
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
    INDEX idx_flight_id (flight_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: notifications
-- Stores user notifications
-- ============================================
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    notification_type ENUM('booking_confirmation', 'payment_received', 'flight_reminder', 'flight_delay', 'cancellation', 'promotion') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    related_booking_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (related_booking_id) REFERENCES bookings(booking_id),
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: promotional_offers
-- Stores promotional codes and discounts
-- ============================================
CREATE TABLE promotional_offers (
    offer_id INT PRIMARY KEY AUTO_INCREMENT,
    promo_code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed_amount') NOT NULL,
    discount_value DECIMAL(10, 2) NOT NULL,
    min_booking_amount DECIMAL(10, 2) DEFAULT 0,
    max_discount_amount DECIMAL(10, 2),
    valid_from DATE NOT NULL,
    valid_to DATE NOT NULL,
    usage_limit INT,
    times_used INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_promo_code (promo_code),
    INDEX idx_valid_dates (valid_from, valid_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: user_sessions
-- Stores active user sessions
-- ============================================
CREATE TABLE user_sessions (
    session_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    INDEX idx_session_token (session_token),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: contact_messages
-- Stores customer contact/inquiry messages
-- ============================================
CREATE TABLE contact_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'in_progress', 'resolved', 'closed') DEFAULT 'new',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Insert Sample Data
-- ============================================

-- Sample Airlines
INSERT INTO airlines (airline_code, airline_name, country, is_active) VALUES
('AA', 'American Airlines', 'United States', TRUE),
('BA', 'British Airways', 'United Kingdom', TRUE),
('EK', 'Emirates', 'United Arab Emirates', TRUE),
('LH', 'Lufthansa', 'Germany', TRUE),
('QR', 'Qatar Airways', 'Qatar', TRUE),
('AF', 'Air France', 'France', TRUE),
('DL', 'Delta Airlines', 'United States', TRUE),
('SQ', 'Singapore Airlines', 'Singapore', TRUE);

-- Sample Airports
INSERT INTO airports (airport_code, airport_name, city, country, is_active) VALUES
('JFK', 'John F. Kennedy International Airport', 'New York', 'United States', TRUE),
('LHR', 'London Heathrow Airport', 'London', 'United Kingdom', TRUE),
('DXB', 'Dubai International Airport', 'Dubai', 'United Arab Emirates', TRUE),
('CDG', 'Charles de Gaulle Airport', 'Paris', 'France', TRUE),
('FRA', 'Frankfurt Airport', 'Frankfurt', 'Germany', TRUE),
('SIN', 'Singapore Changi Airport', 'Singapore', 'Singapore', TRUE),
('HND', 'Tokyo Haneda Airport', 'Tokyo', 'Japan', TRUE),
('LAX', 'Los Angeles International Airport', 'Los Angeles', 'United States', TRUE),
('SYD', 'Sydney Kingsford Smith Airport', 'Sydney', 'Australia', TRUE),
('CAI', 'Cairo International Airport', 'Cairo', 'Egypt', TRUE);

-- Sample Aircraft
INSERT INTO aircraft (aircraft_model, manufacturer, capacity_economy, capacity_business, capacity_first_class, total_capacity) VALUES
('Boeing 737-800', 'Boeing', 150, 20, 0, 170),
('Boeing 777-300ER', 'Boeing', 264, 42, 8, 314),
('Airbus A320', 'Airbus', 150, 20, 0, 170),
('Airbus A380', 'Airbus', 399, 76, 14, 489),
('Boeing 787 Dreamliner', 'Boeing', 224, 35, 8, 267);

-- Sample Admin User (password: Admin@123)
INSERT INTO users (first_name, last_name, email, password_hash, phone, user_role, account_status) VALUES
('Admin', 'User', 'admin@flightbooking.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567890', 'admin', 'active');

-- Sample Customer Users
INSERT INTO users (first_name, last_name, email, password_hash, phone, date_of_birth, gender, nationality, user_role) VALUES
('John', 'Doe', 'john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567891', '1990-05-15', 'Male', 'United States', 'customer'),
('Sarah', 'Smith', 'sarah.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567892', '1988-08-20', 'Female', 'United Kingdom', 'customer'),
('Ahmed', 'Hassan', 'ahmed.hassan@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+20123456789', '1992-03-10', 'Male', 'Egypt', 'customer');

-- Sample Flights
INSERT INTO flights (flight_number, airline_id, aircraft_id, departure_airport_id, arrival_airport_id, 
                    departure_time, arrival_time, duration_minutes, base_price_economy, base_price_business, 
                    base_price_first_class, available_seats_economy, available_seats_business, available_seats_first_class) VALUES
('AA101', 1, 2, 1, 2, '2024-12-20 10:00:00', '2024-12-20 22:30:00', 750, 550.00, 1500.00, 3500.00, 150, 20, 8),
('BA202', 2, 3, 2, 1, '2024-12-20 14:00:00', '2024-12-20 18:15:00', 495, 480.00, 1400.00, 3200.00, 140, 18, 0),
('EK303', 3, 4, 3, 2, '2024-12-21 08:00:00', '2024-12-21 13:30:00', 450, 650.00, 2000.00, 5000.00, 200, 30, 14),
('LH404', 4, 1, 5, 1, '2024-12-21 11:00:00', '2024-12-21 12:30:00', 90, 180.00, 450.00, 0.00, 130, 15, 0),
('QR505', 5, 2, 3, 6, '2024-12-22 02:00:00', '2024-12-22 10:30:00', 510, 720.00, 2200.00, 5500.00, 160, 25, 8);

-- Sample Promotional Offers
INSERT INTO promotional_offers (promo_code, description, discount_type, discount_value, min_booking_amount, valid_from, valid_to, usage_limit, is_active) VALUES
('WELCOME20', 'Welcome discount for new customers', 'percentage', 20.00, 100.00, '2024-01-01', '2024-12-31', 1000, TRUE),
('SUMMER50', 'Summer special $50 off', 'fixed_amount', 50.00, 300.00, '2024-06-01', '2024-08-31', 500, TRUE),
('FAMILY15', 'Family booking discount', 'percentage', 15.00, 500.00, '2024-01-01', '2024-12-31', NULL, TRUE);

-- ============================================
-- Views for Reports and Analytics
-- ============================================

-- View: Active Bookings Summary
CREATE VIEW active_bookings_summary AS
SELECT 
    b.booking_id,
    b.booking_reference,
    CONCAT(u.first_name, ' ', u.last_name) AS customer_name,
    u.email,
    f.flight_number,
    al.airline_name,
    dep.airport_name AS departure_airport,
    arr.airport_name AS arrival_airport,
    f.departure_time,
    f.arrival_time,
    b.seat_class,
    b.number_of_passengers,
    b.total_amount,
    b.payment_status,
    b.booking_status
FROM bookings b
JOIN users u ON b.user_id = u.user_id
JOIN flights f ON b.flight_id = f.flight_id
JOIN airlines al ON f.airline_id = al.airline_id
JOIN airports dep ON f.departure_airport_id = dep.airport_id
JOIN airports arr ON f.arrival_airport_id = arr.airport_id
WHERE b.booking_status != 'cancelled';

-- View: Flight Revenue Report
CREATE VIEW flight_revenue_report AS
SELECT 
    f.flight_id,
    f.flight_number,
    al.airline_name,
    dep.city AS departure_city,
    arr.city AS arrival_city,
    f.departure_time,
    COUNT(b.booking_id) AS total_bookings,
    SUM(b.number_of_passengers) AS total_passengers,
    SUM(b.total_amount) AS total_revenue,
    f.flight_status
FROM flights f
LEFT JOIN bookings b ON f.flight_id = b.flight_id AND b.booking_status = 'confirmed'
JOIN airlines al ON f.airline_id = al.airline_id
JOIN airports dep ON f.departure_airport_id = dep.airport_id
JOIN airports arr ON f.arrival_airport_id = arr.airport_id
GROUP BY f.flight_id;

-- ============================================
-- Stored Procedures
-- ============================================

DELIMITER //

-- Procedure: Search Flights
CREATE PROCEDURE search_flights(
    IN p_departure_airport_id INT,
    IN p_arrival_airport_id INT,
    IN p_departure_date DATE,
    IN p_seat_class VARCHAR(20)
)
BEGIN
    SELECT 
        f.flight_id,
        f.flight_number,
        al.airline_name,
        al.airline_code,
        dep.airport_name AS departure_airport,
        dep.airport_code AS departure_code,
        arr.airport_name AS arrival_airport,
        arr.airport_code AS arrival_code,
        f.departure_time,
        f.arrival_time,
        f.duration_minutes,
        CASE 
            WHEN p_seat_class = 'economy' THEN f.base_price_economy
            WHEN p_seat_class = 'business' THEN f.base_price_business
            WHEN p_seat_class = 'first_class' THEN f.base_price_first_class
        END AS price,
        CASE 
            WHEN p_seat_class = 'economy' THEN f.available_seats_economy
            WHEN p_seat_class = 'business' THEN f.available_seats_business
            WHEN p_seat_class = 'first_class' THEN f.available_seats_first_class
        END AS available_seats,
        f.flight_status
    FROM flights f
    JOIN airlines al ON f.airline_id = al.airline_id
    JOIN airports dep ON f.departure_airport_id = dep.airport_id
    JOIN airports arr ON f.arrival_airport_id = arr.airport_id
    WHERE f.departure_airport_id = p_departure_airport_id
    AND f.arrival_airport_id = p_arrival_airport_id
    AND DATE(f.departure_time) = p_departure_date
    AND f.flight_status = 'scheduled'
    ORDER BY f.departure_time;
END //

-- Procedure: Create Booking
CREATE PROCEDURE create_booking(
    IN p_user_id INT,
    IN p_flight_id INT,
    IN p_seat_class VARCHAR(20),
    IN p_number_of_passengers INT,
    OUT p_booking_reference VARCHAR(20)
)
BEGIN
    DECLARE v_price DECIMAL(10, 2);
    DECLARE v_total_amount DECIMAL(10, 2);
    
    -- Generate booking reference
    SET p_booking_reference = CONCAT('BK', LPAD(FLOOR(RAND() * 999999), 6, '0'));
    
    -- Get price based on seat class
    SELECT 
        CASE 
            WHEN p_seat_class = 'economy' THEN base_price_economy
            WHEN p_seat_class = 'business' THEN base_price_business
            WHEN p_seat_class = 'first_class' THEN base_price_first_class
        END INTO v_price
    FROM flights
    WHERE flight_id = p_flight_id;
    
    SET v_total_amount = v_price * p_number_of_passengers;
    
    -- Insert booking
    INSERT INTO bookings (booking_reference, user_id, flight_id, number_of_passengers, 
                         total_amount, seat_class, travel_date)
    SELECT p_booking_reference, p_user_id, p_flight_id, p_number_of_passengers,
           v_total_amount, p_seat_class, DATE(departure_time)
    FROM flights
    WHERE flight_id = p_flight_id;
    
    -- Update available seats
    UPDATE flights
    SET available_seats_economy = CASE WHEN p_seat_class = 'economy' 
                                      THEN available_seats_economy - p_number_of_passengers 
                                      ELSE available_seats_economy END,
        available_seats_business = CASE WHEN p_seat_class = 'business' 
                                       THEN available_seats_business - p_number_of_passengers 
                                       ELSE available_seats_business END,
        available_seats_first_class = CASE WHEN p_seat_class = 'first_class' 
                                          THEN available_seats_first_class - p_number_of_passengers 
                                          ELSE available_seats_first_class END
    WHERE flight_id = p_flight_id;
END //

DELIMITER ;

-- ============================================
-- Triggers
-- ============================================

DELIMITER //

-- Trigger: After payment completion, update booking status
CREATE TRIGGER after_payment_completion
AFTER UPDATE ON payments
FOR EACH ROW
BEGIN
    IF NEW.payment_status = 'completed' AND OLD.payment_status != 'completed' THEN
        UPDATE bookings 
        SET payment_status = 'paid', booking_status = 'confirmed'
        WHERE booking_id = NEW.booking_id;
        
        -- Create notification
        INSERT INTO notifications (user_id, notification_type, title, message, related_booking_id)
        VALUES (NEW.user_id, 'payment_received', 'Payment Confirmed', 
                'Your payment has been successfully processed.', NEW.booking_id);
    END IF;
END //

-- Trigger: Calculate flight duration before insert
CREATE TRIGGER before_flight_insert
BEFORE INSERT ON flights
FOR EACH ROW
BEGIN
    SET NEW.duration_minutes = TIMESTAMPDIFF(MINUTE, NEW.departure_time, NEW.arrival_time);
END //

-- Trigger: Send notification on booking cancellation
CREATE TRIGGER after_booking_cancellation
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
    IF NEW.booking_status = 'cancelled' AND OLD.booking_status != 'cancelled' THEN
        -- Return seats to availability
        UPDATE flights f
        JOIN bookings b ON f.flight_id = b.flight_id
        SET f.available_seats_economy = CASE WHEN NEW.seat_class = 'economy' 
                                            THEN f.available_seats_economy + NEW.number_of_passengers 
                                            ELSE f.available_seats_economy END,
            f.available_seats_business = CASE WHEN NEW.seat_class = 'business' 
                                             THEN f.available_seats_business + NEW.number_of_passengers 
                                             ELSE f.available_seats_business END,
            f.available_seats_first_class = CASE WHEN NEW.seat_class = 'first_class' 
                                                THEN f.available_seats_first_class + NEW.number_of_passengers 
                                                ELSE f.available_seats_first_class END
        WHERE f.flight_id = NEW.flight_id;
        
        -- Create notification
        INSERT INTO notifications (user_id, notification_type, title, message, related_booking_id)
        VALUES (NEW.user_id, 'cancellation', 'Booking Cancelled', 
                CONCAT('Your booking ', NEW.booking_reference, ' has been cancelled.'), NEW.booking_id);
    END IF;
END //

DELIMITER ;

-- ============================================
-- Indexes for Performance Optimization
-- ============================================

CREATE INDEX idx_flights_search ON flights(departure_airport_id, arrival_airport_id, departure_time, flight_status);
CREATE INDEX idx_bookings_user_status ON bookings(user_id, booking_status, payment_status);
CREATE INDEX idx_payments_status ON payments(payment_status, payment_date);

-- ============================================
-- End of Database Schema
-- ============================================
