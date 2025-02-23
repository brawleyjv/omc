CREATE TABLE setup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mill_rate DECIMAL(10, 2) NOT NULL,
    laser_rate DECIMAL(10, 2) NOT NULL,
    bit_change_rate DECIMAL(10, 2) NOT NULL,
    customize_rate DECIMAL(10, 2) NOT NULL,
    overhead_rate DECIMAL(10, 2) NOT NULL,
    labor_rate DECIMAL(10, 2) NOT NULL,
    sqf_milling_rate DECIMAL(10, 2) NOT NULL,
    packaging_rate DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
