-- Create estimates table
CREATE TABLE IF NOT EXISTS `estimates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estimate_number` varchar(50) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `project_name` varchar(255) NOT NULL,
  `project_description` text,
  `router_time` decimal(10,2) DEFAULT 0.00 COMMENT 'Router time in minutes',
  `laser_time` decimal(10,2) DEFAULT 0.00 COMMENT 'Laser time in minutes',
  `labor_hours` decimal(10,2) DEFAULT 0.00 COMMENT 'Labor hours',
  `materials_cost` decimal(10,2) DEFAULT 0.00 COMMENT 'Total materials cost',
  `labor_cost` decimal(10,2) DEFAULT 0.00 COMMENT 'Calculated labor cost',
  `machine_cost` decimal(10,2) DEFAULT 0.00 COMMENT 'Calculated machine cost (router + laser)',
  `subtotal` decimal(10,2) DEFAULT 0.00 COMMENT 'Subtotal before markup',
  `total_estimate` decimal(10,2) DEFAULT 0.00 COMMENT 'Final estimate total',
  `status` enum('draft','sent','approved','rejected','converted') DEFAULT 'draft',
  `project_id` int(11) DEFAULT NULL COMMENT 'Linked project ID if converted',
  `notes` text COMMENT 'Internal notes',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `estimate_number` (`estimate_number`),
  KEY `customer_name` (`customer_name`),
  KEY `status` (`status`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create estimate_materials table for line items
CREATE TABLE IF NOT EXISTS `estimate_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estimate_id` int(11) NOT NULL,
  `material_name` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_type` enum('sheet','piece','board_foot','linear_foot','square_foot','other') DEFAULT 'piece',
  `unit_cost` decimal(10,2) NOT NULL COMMENT 'Cost per unit',
  `total_cost` decimal(10,2) NOT NULL COMMENT 'Quantity * unit_cost',
  `notes` text,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `estimate_id` (`estimate_id`),
  CONSTRAINT `estimate_materials_ibfk_1` FOREIGN KEY (`estimate_id`) REFERENCES `estimates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create estimate_custom_items table for additional charges (hardware, shipping, etc.)
CREATE TABLE IF NOT EXISTS `estimate_custom_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estimate_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text,
  `cost` decimal(10,2) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `estimate_id` (`estimate_id`),
  CONSTRAINT `estimate_custom_items_ibfk_1` FOREIGN KEY (`estimate_id`) REFERENCES `estimates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
