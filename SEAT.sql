CREATE TABLE seat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_id INT NOT NULL,
    seat_no VARCHAR(5) NOT NULL,
    class VARCHAR(20) DEFAULT 'economy',
    status ENUM('available','booked') DEFAULT 'available',
    FOREIGN KEY (flight_id) REFERENCES flights(id)
);
