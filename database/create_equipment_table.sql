-- Create equipment table for OMC system
-- This table stores information about manufacturing equipment

CREATE TABLE equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_name VARCHAR(255) NOT NULL,
    equipment_type VARCHAR(100) NOT NULL,
    manufacturer VARCHAR(255),
    model_number VARCHAR(100),
    serial_number VARCHAR(100) UNIQUE,
    purchase_date DATE,
    purchase_price DECIMAL(10,2),
    current_value DECIMAL(10,2),
    warranty_expiration DATE,
    last_maintenance_date DATE,
    next_maintenance_date DATE,
    maintenance_interval_days INT DEFAULT 365,
    operating_hours DECIMAL(10,2) DEFAULT 0.00,
    power_consumption VARCHAR(50),
    dimensions VARCHAR(100),
    weight_kg DECIMAL(8,2),
    location VARCHAR(255),
    status ENUM('operational', 'maintenance', 'repair', 'retired') DEFAULT 'operational',
    notes TEXT,
    image_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX idx_equipment_name ON equipment(equipment_name);
CREATE INDEX idx_equipment_type ON equipment(equipment_type);
CREATE INDEX idx_equipment_status ON equipment(status);
CREATE INDEX idx_serial_number ON equipment(serial_number);

-- Insert some sample equipment data
INSERT INTO equipment (
    equipment_name, 
    equipment_type, 
    manufacturer, 
    model_number, 
    serial_number, 
    purchase_date, 
    purchase_price, 
    current_value, 
    location, 
    status, 
    notes
) VALUES 
(
    'CNC Laser Cutter', 
    'Laser Cutting Machine', 
    'Epilog', 
    'Fusion Pro 48', 
    'EP-FP48-2023-001', 
    '2023-01-15', 
    45000.00, 
    40000.00, 
    'Main Workshop Bay 1', 
    'operational', 
    'Primary laser cutter for precision cutting operations'
),
(
    'CNC Router Table', 
    'Router', 
    'ShopBot', 
    'Desktop MAX', 
    'SB-DM-2022-001', 
    '2022-06-10', 
    12000.00, 
    10000.00, 
    'Main Workshop Bay 2', 
    'operational', 
    'Used for routing and milling operations on various materials'
),
(
    'Band Saw', 
    'Cutting Equipment', 
    'Delta', 
    '28-400', 
    'DT-28400-2021-001', 
    '2021-03-20', 
    800.00, 
    650.00, 
    'Workshop Corner A', 
    'operational', 
    'General purpose cutting for rough material preparation'
),
(
    'Air Compressor', 
    'Support Equipment', 
    'Ingersoll Rand', 
    'SS3J3-WB', 
    'IR-SS3J3-2020-001', 
    '2020-11-05', 
    450.00, 
    350.00, 
    'Utility Room', 
    'maintenance', 
    'Requires filter replacement and oil change'
);
