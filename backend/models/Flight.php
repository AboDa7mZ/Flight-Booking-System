<?php
/**
 * Flight Model
 * Flight Booking System
 */

class Flight {
    private $conn;
    private $table_name = "flights";
    
    // Object properties
    public $flight_id;
    public $flight_number;
    public $airline_id;
    public $aircraft_id;
    public $departure_airport_id;
    public $arrival_airport_id;
    public $departure_time;
    public $arrival_time;
    public $duration_minutes;
    public $base_price_economy;
    public $base_price_business;
    public $base_price_first_class;
    public $available_seats_economy;
    public $available_seats_business;
    public $available_seats_first_class;
    public $flight_status;
    public $gate_number;
    public $terminal;
    
    /**
     * Constructor
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Search flights
     */
    public function searchFlights($departure_airport_id, $arrival_airport_id, $departure_date, $seat_class = 'economy', $passengers = 1) {
        // Determine which price and seats columns to use
        $price_column = 'f.base_price_economy';
        $seats_column = 'f.available_seats_economy';
        
        if ($seat_class == 'business') {
            $price_column = 'f.base_price_business';
            $seats_column = 'f.available_seats_business';
        } elseif ($seat_class == 'first_class') {
            $price_column = 'f.base_price_first_class';
            $seats_column = 'f.available_seats_first_class';
        }
        
        $query = "SELECT 
                    f.flight_id,
                    f.flight_number,
                    f.departure_time,
                    f.arrival_time,
                    f.duration_minutes,
                    f.flight_status,
                    f.gate_number,
                    f.terminal,
                    al.airline_name,
                    al.airline_code,
                    al.logo_url,
                    dep.airport_name as departure_airport,
                    dep.airport_code as departure_code,
                    dep.city as departure_city,
                    dep.country as departure_country,
                    arr.airport_name as arrival_airport,
                    arr.airport_code as arrival_code,
                    arr.city as arrival_city,
                    arr.country as arrival_country,
                    ac.aircraft_model,
                    $price_column as price,
                    $seats_column as available_seats
                FROM " . $this->table_name . " f
                INNER JOIN airlines al ON f.airline_id = al.airline_id
                INNER JOIN airports dep ON f.departure_airport_id = dep.airport_id
                INNER JOIN airports arr ON f.arrival_airport_id = arr.airport_id
                LEFT JOIN aircraft ac ON f.aircraft_id = ac.aircraft_id
                WHERE f.departure_airport_id = :departure_airport_id
                AND f.arrival_airport_id = :arrival_airport_id
                AND DATE(f.departure_time) = :departure_date
                AND f.flight_status = 'scheduled'
                AND $seats_column >= :passengers
                ORDER BY f.departure_time";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":departure_airport_id", $departure_airport_id);
        $stmt->bindParam(":arrival_airport_id", $arrival_airport_id);
        $stmt->bindParam(":departure_date", $departure_date);
        $stmt->bindParam(":passengers", $passengers, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get flight by ID
     */
    public function getFlightById($flight_id) {
        $query = "SELECT 
                    f.*,
                    al.airline_name,
                    al.airline_code,
                    al.logo_url,
                    al.website,
                    dep.airport_name as departure_airport,
                    dep.airport_code as departure_code,
                    dep.city as departure_city,
                    dep.country as departure_country,
                    arr.airport_name as arrival_airport,
                    arr.airport_code as arrival_code,
                    arr.city as arrival_city,
                    arr.country as arrival_country,
                    ac.aircraft_model,
                    ac.manufacturer,
                    ac.total_capacity
                FROM " . $this->table_name . " f
                INNER JOIN airlines al ON f.airline_id = al.airline_id
                INNER JOIN airports dep ON f.departure_airport_id = dep.airport_id
                INNER JOIN airports arr ON f.arrival_airport_id = arr.airport_id
                LEFT JOIN aircraft ac ON f.aircraft_id = ac.aircraft_id
                WHERE f.flight_id = :flight_id
                LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":flight_id", $flight_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all flights (admin)
     */
    public function getAllFlights($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT 
                    f.flight_id,
                    f.flight_number,
                    f.departure_time,
                    f.arrival_time,
                    f.duration_minutes,
                    f.flight_status,
                    al.airline_name,
                    dep.airport_code as departure_code,
                    dep.city as departure_city,
                    arr.airport_code as arrival_code,
                    arr.city as arrival_city,
                    f.available_seats_economy,
                    f.available_seats_business,
                    f.available_seats_first_class
                FROM " . $this->table_name . " f
                INNER JOIN airlines al ON f.airline_id = al.airline_id
                INNER JOIN airports dep ON f.departure_airport_id = dep.airport_id
                INNER JOIN airports arr ON f.arrival_airport_id = arr.airport_id
                ORDER BY f.departure_time DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new flight (admin)
     */
    public function createFlight() {
        $query = "INSERT INTO " . $this->table_name . "
                SET flight_number = :flight_number,
                    airline_id = :airline_id,
                    aircraft_id = :aircraft_id,
                    departure_airport_id = :departure_airport_id,
                    arrival_airport_id = :arrival_airport_id,
                    departure_time = :departure_time,
                    arrival_time = :arrival_time,
                    base_price_economy = :base_price_economy,
                    base_price_business = :base_price_business,
                    base_price_first_class = :base_price_first_class,
                    available_seats_economy = :available_seats_economy,
                    available_seats_business = :available_seats_business,
                    available_seats_first_class = :available_seats_first_class,
                    flight_status = 'scheduled'";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":flight_number", $this->flight_number);
        $stmt->bindParam(":airline_id", $this->airline_id);
        $stmt->bindParam(":aircraft_id", $this->aircraft_id);
        $stmt->bindParam(":departure_airport_id", $this->departure_airport_id);
        $stmt->bindParam(":arrival_airport_id", $this->arrival_airport_id);
        $stmt->bindParam(":departure_time", $this->departure_time);
        $stmt->bindParam(":arrival_time", $this->arrival_time);
        $stmt->bindParam(":base_price_economy", $this->base_price_economy);
        $stmt->bindParam(":base_price_business", $this->base_price_business);
        $stmt->bindParam(":base_price_first_class", $this->base_price_first_class);
        $stmt->bindParam(":available_seats_economy", $this->available_seats_economy);
        $stmt->bindParam(":available_seats_business", $this->available_seats_business);
        $stmt->bindParam(":available_seats_first_class", $this->available_seats_first_class);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update flight status
     */
    public function updateFlightStatus($flight_id, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET flight_status = :status
                WHERE flight_id = :flight_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":flight_id", $flight_id);
        
        return $stmt->execute();
    }
    
    /**
     * Get popular routes
     */
    public function getPopularRoutes($limit = 10) {
        $query = "SELECT 
                    dep.city as departure_city,
                    dep.country as departure_country,
                    arr.city as arrival_city,
                    arr.country as arrival_country,
                    COUNT(b.booking_id) as booking_count,
                    MIN(f.base_price_economy) as min_price
                FROM " . $this->table_name . " f
                INNER JOIN airports dep ON f.departure_airport_id = dep.airport_id
                INNER JOIN airports arr ON f.arrival_airport_id = arr.airport_id
                LEFT JOIN bookings b ON f.flight_id = b.flight_id
                WHERE f.departure_time >= NOW()
                GROUP BY dep.city, arr.city
                ORDER BY booking_count DESC
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
