-- ============================================
-- Flight Booking Website - Project Database Schema
-- According to project requirements
-- ============================================

CREATE DATABASE IF NOT EXISTS flight_booking_system;
USE flight_booking_system;

-- Disable foreign key checks to allow dropping tables
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS flight_itinerary;
DROP TABLE IF EXISTS flights;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS passengers;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS promotional_offers;
DROP TABLE IF EXISTS user_sessions;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS airlines;
DROP TABLE IF EXISTS airports;
DROP TABLE IF EXISTS aircraft;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- Table: users
-- Stores both Company and Passenger accounts
-- ============================================
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    user_type ENUM('company', 'passenger') NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tel VARCHAR(20) NOT NULL,
    
    -- Company-specific fields
    bio TEXT NULL,
    address TEXT NULL,
    location VARCHAR(255) NULL,
    logo_img VARCHAR(255) NULL,
    
    -- Passenger-specific fields
    photo VARCHAR(255) NULL,
    passport_img VARCHAR(255) NULL,
    
    -- Account balance
    account_balance DECIMAL(10, 2) DEFAULT 0.00,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_type (user_type),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: flights
-- Flights created by companies
-- ============================================
CREATE TABLE flights (
    flight_id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    flight_name VARCHAR(100) NOT NULL,
    flight_code VARCHAR(20) UNIQUE NOT NULL,
    fees DECIMAL(10, 2) NOT NULL,
    max_passengers INT NOT NULL,
    registered_passengers INT DEFAULT 0,
    pending_passengers INT DEFAULT 0,
    is_completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_company (company_id),
    INDEX idx_completed (is_completed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: flight_itinerary
-- Cities to pass through with time info
-- ============================================
CREATE TABLE flight_itinerary (
    itinerary_id INT PRIMARY KEY AUTO_INCREMENT,
    flight_id INT NOT NULL,
    city VARCHAR(100) NOT NULL,
    sequence_order INT NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    
    FOREIGN KEY (flight_id) REFERENCES flights(flight_id) ON DELETE CASCADE,
    INDEX idx_flight (flight_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: bookings
-- Passenger flight bookings (pending or registered)
-- ============================================
CREATE TABLE bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    passenger_id INT NOT NULL,
    flight_id INT NOT NULL,
    status ENUM('pending', 'registered', 'cancelled') DEFAULT 'pending',
    payment_type ENUM('account', 'cash') NOT NULL,
    amount_paid DECIMAL(10, 2) NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (passenger_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (flight_id) REFERENCES flights(flight_id) ON DELETE CASCADE,
    INDEX idx_passenger (passenger_id),
    INDEX idx_flight (flight_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Table: messages
-- Communication between passengers and companies
-- ============================================
CREATE TABLE messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    flight_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (flight_id) REFERENCES flights(flight_id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_flight (flight_id),
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Insert sample data
-- ============================================

-- Sample Companies
INSERT INTO users (user_type, name, email, password, tel, bio, address, account_balance) VALUES
('company', 'SkyWings Airlines', 'skywings@airline.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-0100', 'Premium airline service with global coverage', '123 Airport Blvd, New York, NY', 50000.00),
('company', 'AirExpress Co.', 'info@airexpress.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-0200', 'Fast and affordable flights worldwide', '456 Aviation St, Los Angeles, CA', 35000.00);

-- Sample Passengers  
INSERT INTO users (user_type, name, email, password, tel, account_balance) VALUES
('passenger', 'John Smith', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-1001', 1500.00),
('passenger', 'Sarah Johnson', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-1002', 2000.00),
('passenger', 'Mike Davis', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1-555-1003', 500.00);

-- Sample Flights by Company 1 (SkyWings)
INSERT INTO flights (company_id, flight_name, flight_code, fees, max_passengers, registered_passengers, pending_passengers) VALUES
(1, 'New York to London Express', 'SW101', 550.00, 200, 45, 5),
(1, 'Los Angeles to Tokyo Direct', 'SW202', 850.00, 180, 120, 8),
(1, 'Miami to Paris Route', 'SW303', 650.00, 150, 80, 3);

-- Sample Flights by Company 2 (AirExpress)
INSERT INTO flights (company_id, flight_name, flight_code, fees, max_passengers, registered_passengers, pending_passengers) VALUES
(2, 'Chicago to Dubai Fast', 'AE401', 920.00, 220, 150, 10),
(2, 'Boston to Berlin Direct', 'AE502', 680.00, 160, 90, 5);

-- Sample Flight Itineraries
-- Flight SW101: New York -> London
INSERT INTO flight_itinerary (flight_id, city, sequence_order, start_datetime, end_datetime) VALUES
(1, 'New York', 1, '2025-12-26 08:00:00', '2025-12-26 08:30:00'),
(1, 'London', 2, '2025-12-26 20:00:00', '2025-12-26 20:30:00');

-- Flight SW202: Los Angeles -> Tokyo (with stopover)
INSERT INTO flight_itinerary (flight_id, city, sequence_order, start_datetime, end_datetime) VALUES
(2, 'Los Angeles', 1, '2025-12-27 10:00:00', '2025-12-27 10:30:00'),
(2, 'Honolulu', 2, '2025-12-27 16:00:00', '2025-12-27 18:00:00'),
(2, 'Tokyo', 3, '2025-12-28 14:00:00', '2025-12-28 14:30:00');

-- Flight SW303: Miami -> Paris
INSERT INTO flight_itinerary (flight_id, city, sequence_order, start_datetime, end_datetime) VALUES
(3, 'Miami', 1, '2025-12-28 14:00:00', '2025-12-28 14:30:00'),
(3, 'Paris', 2, '2025-12-29 04:00:00', '2025-12-29 04:30:00');

-- Flight AE401: Chicago -> Dubai
INSERT INTO flight_itinerary (flight_id, city, sequence_order, start_datetime, end_datetime) VALUES
(4, 'Chicago', 1, '2025-12-29 18:00:00', '2025-12-29 18:30:00'),
(4, 'Dubai', 2, '2025-12-30 20:00:00', '2025-12-30 20:30:00');

-- Flight AE502: Boston -> Berlin
INSERT INTO flight_itinerary (flight_id, city, sequence_order, start_datetime, end_datetime) VALUES
(5, 'Boston', 1, '2025-12-30 11:00:00', '2025-12-30 11:30:00'),
(5, 'Berlin', 2, '2025-12-30 23:00:00', '2025-12-30 23:30:00');

-- Sample Bookings
INSERT INTO bookings (passenger_id, flight_id, status, payment_type, amount_paid) VALUES
(3, 1, 'registered', 'account', 550.00),
(4, 1, 'pending', 'cash', 550.00),
(5, 2, 'registered', 'account', 850.00);

-- Sample Messages
INSERT INTO messages (flight_id, sender_id, receiver_id, message) VALUES
(1, 4, 1, 'Hello, what is the baggage allowance for this flight?'),
(1, 1, 4, 'Hi! The baggage allowance is 2 checked bags (23kg each) and 1 carry-on (10kg).'),
(2, 5, 1, 'Is meal service included in the ticket price?');

