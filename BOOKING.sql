CREATE TABLE booking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_id INT NOT NULL,
    seat_no VARCHAR(5) NOT NULL,
    passenger_name VARCHAR(100) NOT NULL,
    passenger_phone VARCHAR(20),
    passenger_email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (flight_id) REFERENCES flights(id)
);
