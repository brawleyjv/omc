CREATE TABLE `customers` (
    `customer_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(20) NOT NULL,
    `Project` VARCHAR(20) DEFAULT NULL,
    `address` VARCHAR(20) DEFAULT NULL,
    `city` VARCHAR(12) DEFAULT NULL,
    `state` VARCHAR(2) DEFAULT NULL,
    `zip` INT(5) DEFAULT NULL,
    `phone` INT(10) DEFAULT NULL,
    `email` VARCHAR(30) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL
);
