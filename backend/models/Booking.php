<?php
/**
 * Booking Model
 * Flight Booking System
 */

class Booking {
    private $conn;
    private $table_name = "bookings";
    
    // Object properties
    public $booking_id;
    public $booking_reference;
    public $user_id;
    public $flight_id;
    public $booking_date;
    public $travel_date;
    public $number_of_passengers;
    public $total_amount;
    public $payment_status;
    public $booking_status;
    public $seat_class;
    public $special_requests;
    
    /**
     * Constructor
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create new booking
     */
    public function createBooking() {
        // Generate unique booking reference
        $this->booking_reference = $this->generateBookingReference();
        
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // Insert booking
            $query = "INSERT INTO " . $this->table_name . "
                    SET booking_reference = :booking_reference,
                        user_id = :user_id,
                        flight_id = :flight_id,
                        travel_date = :travel_date,
                        number_of_passengers = :number_of_passengers,
                        total_amount = :total_amount,
                        seat_class = :seat_class,
                        special_requests = :special_requests,
                        booking_status = 'pending',
                        payment_status = 'pending'";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":booking_reference", $this->booking_reference);
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":flight_id", $this->flight_id);
            $stmt->bindParam(":travel_date", $this->travel_date);
            $stmt->bindParam(":number_of_passengers", $this->number_of_passengers);
            $stmt->bindParam(":total_amount", $this->total_amount);
            $stmt->bindParam(":seat_class", $this->seat_class);
            $stmt->bindParam(":special_requests", $this->special_requests);
            
            $stmt->execute();
            $this->booking_id = $this->conn->lastInsertId();
            
            // Update flight available seats
            $seat_column = "available_seats_" . $this->seat_class;
            $update_query = "UPDATE flights 
                           SET $seat_column = $seat_column - :passengers 
                           WHERE flight_id = :flight_id 
                           AND $seat_column >= :passengers";
            
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(":passengers", $this->number_of_passengers);
            $update_stmt->bindParam(":flight_id", $this->flight_id);
            
            if(!$update_stmt->execute() || $update_stmt->rowCount() == 0) {
                throw new Exception("Not enough seats available");
            }
            
            // Commit transaction
            $this->conn->commit();
            return $this->booking_id;
            
        } catch(Exception $e) {
            // Rollback on error
            $this->conn->rollBack();
            error_log("Booking creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get booking by ID
     */
    public function getBookingById($booking_id) {
        $query = "SELECT 
                    b.*,
                    f.flight_number,
                    f.departure_time,
                    f.arrival_time,
                    f.duration_minutes,
                    f.gate_number,
                    f.terminal,
                    al.airline_name,
                    al.airline_code,
                    dep.airport_name as departure_airport,
                    dep.airport_code as departure_code,
                    dep.city as departure_city,
                    arr.airport_name as arrival_airport,
                    arr.airport_code as arrival_code,
                    arr.city as arrival_city,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone
                FROM " . $this->table_name . " b
                INNER JOIN flights f ON b.flight_id = f.flight_id
                INNER JOIN airlines al ON f.airline_id = al.airline_id
                INNER JOIN airports dep ON f.departure_airport_id = dep.airport_id
                INNER JOIN airports arr ON f.arrival_airport_id = arr.airport_id
                INNER JOIN users u ON b.user_id = u.user_id
                WHERE b.booking_id = :booking_id
                LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":booking_id", $booking_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get booking by reference
     */
    public function getBookingByReference($booking_reference) {
        $query = "SELECT 
                    b.*,
                    f.flight_number,
                    f.departure_time,
                    f.arrival_time,
                    al.airline_name,
                    dep.city as departure_city,
                    arr.city as arrival_city
                FROM " . $this->table_name . " b
                INNER JOIN flights f ON b.flight_id = f.flight_id
                INNER JOIN airlines al ON f.airline_id = al.airline_id
                INNER JOIN airports dep ON f.departure_airport_id = dep.airport_id
                INNER JOIN airports arr ON f.arrival_airport_id = arr.airport_id
                WHERE b.booking_reference = :booking_reference
                LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":booking_reference", $booking_reference);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user bookings
     */
    public function getUserBookings($user_id, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT 
                    b.booking_id,
                    b.booking_reference,
                    b.booking_date,
                    b.travel_date,
                    b.number_of_passengers,
                    b.total_amount,
                    b.payment_status,
                    b.booking_status,
                    b.seat_class,
                    f.flight_number,
                    f.departure_time,
                    f.arrival_time,
                    al.airline_name,
                    dep.city as departure_city,
                    arr.city as arrival_city
                FROM " . $this->table_name . " b
                INNER JOIN flights f ON b.flight_id = f.flight_id
                INNER JOIN airlines al ON f.airline_id = al.airline_id
                INNER JOIN airports dep ON f.departure_airport_id = dep.airport_id
                INNER JOIN airports arr ON f.arrival_airport_id = arr.airport_id
                WHERE b.user_id = :user_id
                ORDER BY b.booking_date DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cancel booking
     */
    public function cancelBooking($booking_id, $user_id) {
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // Get booking details
            $query = "SELECT flight_id, seat_class, number_of_passengers, booking_status 
                     FROM " . $this->table_name . " 
                     WHERE booking_id = :booking_id AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":booking_id", $booking_id);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$booking || $booking['booking_status'] == 'cancelled') {
                throw new Exception("Booking not found or already cancelled");
            }
            
            // Update booking status
            $update_query = "UPDATE " . $this->table_name . "
                           SET booking_status = 'cancelled'
                           WHERE booking_id = :booking_id";
            
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(":booking_id", $booking_id);
            $update_stmt->execute();
            
            // Return seats to availability
            $seat_column = "available_seats_" . $booking['seat_class'];
            $seats_query = "UPDATE flights 
                          SET $seat_column = $seat_column + :passengers 
                          WHERE flight_id = :flight_id";
            
            $seats_stmt = $this->conn->prepare($seats_query);
            $seats_stmt->bindParam(":passengers", $booking['number_of_passengers']);
            $seats_stmt->bindParam(":flight_id", $booking['flight_id']);
            $seats_stmt->execute();
            
            // Commit transaction
            $this->conn->commit();
            return true;
            
        } catch(Exception $e) {
            // Rollback on error
            $this->conn->rollBack();
            error_log("Booking cancellation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update booking status
     */
    public function updateBookingStatus($booking_id, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET booking_status = :status
                WHERE booking_id = :booking_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":booking_id", $booking_id);
        
        return $stmt->execute();
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus($booking_id, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET payment_status = :status,
                    booking_status = CASE WHEN :status2 = 'paid' THEN 'confirmed' ELSE booking_status END
                WHERE booking_id = :booking_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":status2", $status);
        $stmt->bindParam(":booking_id", $booking_id);
        
        return $stmt->execute();
    }
    
    /**
     * Generate unique booking reference
     */
    private function generateBookingReference() {
        $reference = 'BK' . strtoupper(substr(uniqid(), -6));
        
        // Check if reference exists
        $query = "SELECT booking_id FROM " . $this->table_name . " 
                 WHERE booking_reference = :reference";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":reference", $reference);
        $stmt->execute();
        
        // Generate new reference if exists
        if($stmt->rowCount() > 0) {
            return $this->generateBookingReference();
        }
        
        return $reference;
    }
    
    /**
     * Get booking statistics (admin)
     */
    public function getBookingStats() {
        $query = "SELECT 
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                    SUM(CASE WHEN booking_status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                    SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                    SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as total_revenue,
                    SUM(number_of_passengers) as total_passengers
                FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
