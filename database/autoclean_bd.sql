-- AutoClean BD Database Schema
-- MySQL Database for Vehicle Wash Service Management System
-- Created for XAMPP/phpMyAdmin

-- Drop database if exists and create new one
DROP DATABASE IF EXISTS `autoclean_bd`;
CREATE DATABASE `autoclean_bd` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `autoclean_bd`;

-- Users table (for all user types: admin, customer, worker)
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('admin','customer','worker') NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `profile_image` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_user_type` (`user_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service packages table
CREATE TABLE `service_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `car_price` decimal(10,2) NOT NULL,
  `bike_price` decimal(10,2) NOT NULL,
  `duration_minutes` int(11) NOT NULL DEFAULT 60,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings table
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(20) NOT NULL UNIQUE,
  `customer_id` int(11) NOT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `package_id` int(11) NOT NULL,
  `vehicle_type` enum('car','bike') NOT NULL,
  `vehicle_model` varchar(100) DEFAULT NULL,
  `vehicle_color` varchar(50) DEFAULT NULL,
  `scheduled_date` date NOT NULL,
  `scheduled_time` time NOT NULL,
  `service_address` text NOT NULL,
  `payment_method` enum('Cash on Delivery','bKash','Nagad') NOT NULL DEFAULT 'Cash on Delivery',
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','in-progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `special_instructions` text DEFAULT NULL,
  `customer_rating` int(11) DEFAULT NULL CHECK (`customer_rating` >= 1 AND `customer_rating` <= 5),
  `customer_review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_worker_id` (`worker_id`),
  KEY `idx_package_id` (`package_id`),
  KEY `idx_status` (`status`),
  KEY `idx_scheduled_date` (`scheduled_date`),
  FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`worker_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`package_id`) REFERENCES `service_packages` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Worker schedules table
CREATE TABLE `worker_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `worker_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `scheduled_date` date NOT NULL,
  `scheduled_time` time NOT NULL,
  `status` enum('assigned','in-progress','completed','cancelled') NOT NULL DEFAULT 'assigned',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_worker_id` (`worker_id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_scheduled_date` (`scheduled_date`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`worker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact messages table
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews table
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `review_text` text NOT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_worker_id` (`worker_id`),
  KEY `idx_rating` (`rating`),
  KEY `idx_is_approved` (`is_approved`),
  FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`worker_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System settings table
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data

-- Insert default admin user
INSERT INTO `users` (`first_name`, `last_name`, `email`, `phone`, `password`, `user_type`, `status`) VALUES
('Admin', 'User', 'admin@autocleanbd.com', '+880 1712345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Insert sample customers
INSERT INTO `users` (`first_name`, `last_name`, `email`, `phone`, `password`, `user_type`, `status`, `address`) VALUES
('John', 'Doe', 'customer@demo.com', '+880 1712345679', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active', 'House 123, Road 12, Bashundhara R/A, Dhaka'),
('Jane', 'Smith', 'jane.smith@email.com', '+880 1712345680', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active', 'Apartment 45, Road 8, Dhanmondi, Dhaka'),
('Ahmed', 'Khan', 'ahmed.khan@email.com', '+880 1712345681', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active', 'House 67, Road 15, Gulshan, Dhaka'),
('Fatima', 'Rahman', 'fatima.rahman@email.com', '+880 1712345682', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active', 'House 89, Road 20, Banani, Dhaka');

-- Insert sample workers
INSERT INTO `users` (`first_name`, `last_name`, `email`, `phone`, `password`, `user_type`, `status`, `address`) VALUES
('Worker', 'One', 'worker@autocleanbd.com', '+880 1712345683', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'worker', 'active', 'Worker Colony, Road 5, Dhaka'),
('Karim', 'Hossain', 'karim.hossain@autocleanbd.com', '+880 1712345684', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'worker', 'active', 'Worker Colony, Road 6, Dhaka'),
('Rahim', 'Ali', 'rahim.ali@autocleanbd.com', '+880 1712345685', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'worker', 'active', 'Worker Colony, Road 7, Dhaka'),
('Salam', 'Miah', 'salam.miah@autocleanbd.com', '+880 1712345686', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'worker', 'active', 'Worker Colony, Road 8, Dhaka');

-- Insert service packages
INSERT INTO `service_packages` (`name`, `description`, `car_price`, `bike_price`, `duration_minutes`, `is_active`) VALUES
('Basic Wash', 'Exterior wash, tire cleaning, and basic interior cleaning', 299.00, 199.00, 45, 1),
('Premium Wash', 'Complete exterior and interior cleaning with premium products', 599.00, 399.00, 60, 1),
('Detailing', 'Professional detailing with paint protection and interior deep cleaning', 1299.00, 899.00, 180, 1),
('Express Wash', 'Quick exterior wash for busy customers', 199.00, 149.00, 30, 1),
('Full Service', 'Complete wash, wax, and interior detailing', 899.00, 599.00, 120, 1);

-- Insert sample bookings
INSERT INTO `bookings` (`booking_id`, `customer_id`, `worker_id`, `package_id`, `vehicle_type`, `vehicle_model`, `vehicle_color`, `scheduled_date`, `scheduled_time`, `service_address`, `payment_method`, `amount`, `status`, `special_instructions`) VALUES
('BK001', 2, 5, 2, 'car', 'Toyota Corolla', 'White', '2024-01-20', '10:00:00', 'House 123, Road 12, Bashundhara R/A, Dhaka', 'Cash on Delivery', 599.00, 'completed', 'Please be careful with the paint'),
('BK002', 3, 6, 1, 'bike', 'Honda CB150R', 'Red', '2024-01-21', '14:00:00', 'Apartment 45, Road 8, Dhanmondi, Dhaka', 'bKash', 199.00, 'in-progress', NULL),
('BK003', 4, 7, 3, 'car', 'Nissan X-Trail', 'Black', '2024-01-22', '09:00:00', 'House 67, Road 15, Gulshan, Dhaka', 'Nagad', 1299.00, 'confirmed', 'Full interior cleaning required'),
('BK004', 5, 8, 2, 'car', 'Suzuki Swift', 'Blue', '2024-01-23', '11:00:00', 'House 89, Road 20, Banani, Dhaka', 'Cash on Delivery', 599.00, 'pending', NULL),
('BK005', 2, 5, 1, 'bike', 'Yamaha FZ150', 'Grey', '2024-01-24', '15:00:00', 'House 123, Road 12, Bashundhara R/A, Dhaka', 'bKash', 199.00, 'pending', NULL),
('BK006', 3, 6, 4, 'car', 'Mitsubishi Lancer', 'Silver', '2024-01-25', '08:00:00', 'Apartment 45, Road 8, Dhanmondi, Dhaka', 'Cash on Delivery', 199.00, 'pending', 'Quick wash only'),
('BK007', 4, 7, 5, 'car', 'Honda City', 'White', '2024-01-26', '13:00:00', 'House 67, Road 15, Gulshan, Dhaka', 'Nagad', 899.00, 'pending', 'Include waxing service'),
('BK008', 5, 8, 3, 'bike', 'Kawasaki Ninja 300', 'Green', '2024-01-27', '10:00:00', 'House 89, Road 20, Banani, Dhaka', 'Cash on Delivery', 899.00, 'pending', 'Professional detailing required');

-- Insert worker schedules
INSERT INTO `worker_schedules` (`worker_id`, `booking_id`, `scheduled_date`, `scheduled_time`, `status`, `notes`) VALUES
(5, 1, '2024-01-20', '10:00:00', 'completed', 'Job completed successfully'),
(6, 2, '2024-01-21', '14:00:00', 'in-progress', 'Currently working on the bike'),
(7, 3, '2024-01-22', '09:00:00', 'assigned', 'Ready for assignment'),
(8, 4, '2024-01-23', '11:00:00', 'assigned', 'Ready for assignment'),
(5, 5, '2024-01-24', '15:00:00', 'assigned', 'Ready for assignment'),
(6, 6, '2024-01-25', '08:00:00', 'assigned', 'Ready for assignment'),
(7, 7, '2024-01-26', '13:00:00', 'assigned', 'Ready for assignment'),
(8, 8, '2024-01-27', '10:00:00', 'assigned', 'Ready for assignment');

-- Insert sample reviews
INSERT INTO `reviews` (`booking_id`, `customer_id`, `worker_id`, `rating`, `review_text`, `is_approved`) VALUES
(1, 2, 5, 5, 'Excellent service! The car looks brand new. Very professional and punctual.', 1),
(2, 3, 6, 4, 'Good service, but took a bit longer than expected. Overall satisfied.', 1),
(3, 4, 7, 5, 'Amazing detailing work! The interior is spotless and the exterior shines beautifully.', 1);

-- Insert contact messages
INSERT INTO `contact_messages` (`name`, `email`, `phone`, `subject`, `message`, `status`) VALUES
('Sarah Johnson', 'sarah.johnson@email.com', '+880 1712345687', 'Service Inquiry', 'I would like to know more about your premium wash service for my SUV.', 'unread'),
('Michael Brown', 'michael.brown@email.com', '+880 1712345688', 'Booking Question', 'Can I book a service for tomorrow morning? What are the available time slots?', 'read'),
('Emily Davis', 'emily.davis@email.com', '+880 1712345689', 'Feedback', 'Great service! The worker was very professional and the car looks amazing.', 'replied'),
('David Wilson', 'david.wilson@email.com', '+880 1712345690', 'Complaint', 'The service was delayed by 30 minutes. Please improve punctuality.', 'unread');

-- Insert system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('company_name', 'AutoClean BD', 'Company name for the system'),
('company_email', 'info@autocleanbd.com', 'Primary company email address'),
('company_phone', '+880 1712345678', 'Primary company phone number'),
('company_address', 'House 123, Road 12, Bashundhara R/A, Dhaka, Bangladesh', 'Company address'),
('service_hours_start', '08:00', 'Service hours start time'),
('service_hours_end', '18:00', 'Service hours end time'),
('booking_advance_days', '7', 'How many days in advance customers can book'),
('cancellation_hours', '2', 'Hours before service when cancellation is allowed'),
('currency', 'BDT', 'System currency'),
('currency_symbol', '৳', 'Currency symbol');

-- Create views for easier data access

-- View for booking details with customer and worker information
CREATE VIEW `booking_details` AS
SELECT 
    b.id,
    b.booking_id,
    b.customer_id,
    CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
    c.email AS customer_email,
    c.phone AS customer_phone,
    b.worker_id,
    CONCAT(w.first_name, ' ', w.last_name) AS worker_name,
    w.phone AS worker_phone,
    b.package_id,
    sp.name AS package_name,
    sp.description AS package_description,
    b.vehicle_type,
    b.vehicle_model,
    b.vehicle_color,
    b.scheduled_date,
    b.scheduled_time,
    b.service_address,
    b.payment_method,
    b.amount,
    b.status,
    b.special_instructions,
    b.customer_rating,
    b.customer_review,
    b.created_at,
    b.updated_at
FROM bookings b
LEFT JOIN users c ON b.customer_id = c.id
LEFT JOIN users w ON b.worker_id = w.id
LEFT JOIN service_packages sp ON b.package_id = sp.id;

-- View for dashboard statistics
CREATE VIEW `dashboard_stats` AS
SELECT 
    COUNT(*) AS total_bookings,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_bookings,
    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_bookings,
    SUM(CASE WHEN status = 'in-progress' THEN 1 ELSE 0 END) AS in_progress_bookings,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_bookings,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_bookings,
    SUM(amount) AS total_revenue,
    AVG(CASE WHEN customer_rating IS NOT NULL THEN customer_rating END) AS average_rating
FROM bookings;

-- View for worker performance
CREATE VIEW `worker_performance` AS
SELECT 
    w.id AS worker_id,
    CONCAT(w.first_name, ' ', w.last_name) AS worker_name,
    w.email AS worker_email,
    w.phone AS worker_phone,
    COUNT(b.id) AS total_jobs,
    SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) AS completed_jobs,
    SUM(CASE WHEN b.status = 'in-progress' THEN 1 ELSE 0 END) AS active_jobs,
    AVG(CASE WHEN b.customer_rating IS NOT NULL THEN b.customer_rating END) AS average_rating,
    SUM(b.amount) AS total_earnings
FROM users w
LEFT JOIN bookings b ON w.id = b.worker_id
WHERE w.user_type = 'worker'
GROUP BY w.id, w.first_name, w.last_name, w.email, w.phone;

-- Create indexes for better performance
CREATE INDEX `idx_bookings_customer_date` ON `bookings` (`customer_id`, `scheduled_date`);
CREATE INDEX `idx_bookings_worker_date` ON `bookings` (`worker_id`, `scheduled_date`);
CREATE INDEX `idx_bookings_status_date` ON `bookings` (`status`, `scheduled_date`);
CREATE INDEX `idx_users_type_status` ON `users` (`user_type`, `status`);
CREATE INDEX `idx_worker_schedules_worker_date` ON `worker_schedules` (`worker_id`, `scheduled_date`);

-- Create stored procedures for common operations

-- Procedure to get customer dashboard data
DELIMITER //
CREATE PROCEDURE `GetCustomerDashboardData`(IN customer_id INT)
BEGIN
    SELECT 
        COUNT(*) AS total_bookings,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_bookings,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_bookings,
        SUM(CASE WHEN status = 'in-progress' THEN 1 ELSE 0 END) AS in_progress_bookings,
        SUM(amount) AS total_spent,
        AVG(CASE WHEN customer_rating IS NOT NULL THEN customer_rating END) AS average_rating
    FROM bookings 
    WHERE customer_id = customer_id;
    
    SELECT * FROM booking_details 
    WHERE customer_id = customer_id 
    ORDER BY created_at DESC 
    LIMIT 5;
END //
DELIMITER ;

-- Procedure to get admin dashboard data
DELIMITER //
CREATE PROCEDURE `GetAdminDashboardData`()
BEGIN
    SELECT * FROM dashboard_stats;
    
    SELECT * FROM booking_details 
    ORDER BY created_at DESC 
    LIMIT 10;
    
    SELECT * FROM worker_performance;
    
    SELECT COUNT(*) AS total_customers FROM users WHERE user_type = 'customer';
    SELECT COUNT(*) AS total_workers FROM users WHERE user_type = 'worker';
    SELECT COUNT(*) AS unread_messages FROM contact_messages WHERE status = 'unread';
END //
DELIMITER ;

-- Procedure to assign worker to booking
DELIMITER //
CREATE PROCEDURE `AssignWorkerToBooking`(IN booking_id INT, IN worker_id INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    UPDATE bookings SET worker_id = worker_id WHERE id = booking_id;
    
    INSERT INTO worker_schedules (worker_id, booking_id, scheduled_date, scheduled_time, status)
    SELECT worker_id, id, scheduled_date, scheduled_time, 'assigned'
    FROM bookings WHERE id = booking_id;
    
    COMMIT;
END //
DELIMITER ;

-- Create triggers for data integrity

-- Trigger to update booking status when worker schedule changes
DELIMITER //
CREATE TRIGGER `update_booking_status_from_schedule`
AFTER UPDATE ON `worker_schedules`
FOR EACH ROW
BEGIN
    IF NEW.status != OLD.status THEN
        UPDATE bookings 
        SET status = CASE 
            WHEN NEW.status = 'assigned' THEN 'confirmed'
            WHEN NEW.status = 'in-progress' THEN 'in-progress'
            WHEN NEW.status = 'completed' THEN 'completed'
            WHEN NEW.status = 'cancelled' THEN 'cancelled'
            ELSE bookings.status
        END
        WHERE id = NEW.booking_id;
    END IF;
END //
DELIMITER ;

-- Trigger to generate booking ID
DELIMITER //
CREATE TRIGGER `generate_booking_id`
BEFORE INSERT ON `bookings`
FOR EACH ROW
BEGIN
    IF NEW.booking_id IS NULL OR NEW.booking_id = '' THEN
        SET NEW.booking_id = CONCAT('BK', LPAD((SELECT COUNT(*) + 1 FROM bookings), 3, '0'));
    END IF;
END //
DELIMITER ;

-- Grant permissions (adjust as needed for your setup)
-- GRANT ALL PRIVILEGES ON autoclean_bd.* TO 'your_username'@'localhost';
-- FLUSH PRIVILEGES;

-- Final message
SELECT 'AutoClean BD Database created successfully!' AS message;
SELECT 'Demo accounts created:' AS info;
SELECT 'Admin: admin@autocleanbd.com / password' AS admin_account;
SELECT 'Customer: customer@demo.com / password' AS customer_account;
SELECT 'Worker: worker@autocleanbd.com / password' AS worker_account;
SELECT 'Note: All passwords are "password" (hashed with bcrypt)' AS password_note;

-- Additional useful queries for data analysis and maintenance

-- Query 1: Get monthly revenue report
SELECT 
    DATE_FORMAT(scheduled_date, '%Y-%m') AS month,
    COUNT(*) AS total_bookings,
    SUM(amount) AS total_revenue,
    AVG(amount) AS average_booking_value
FROM bookings 
WHERE status = 'completed'
GROUP BY DATE_FORMAT(scheduled_date, '%Y-%m')
ORDER BY month DESC;

-- Query 2: Get popular service packages
SELECT 
    sp.name AS package_name,
    COUNT(*) AS booking_count,
    SUM(b.amount) AS total_revenue,
    AVG(b.amount) AS average_revenue
FROM bookings b
JOIN service_packages sp ON b.package_id = sp.id
WHERE b.status = 'completed'
GROUP BY sp.id, sp.name
ORDER BY booking_count DESC;

-- Query 3: Get customer loyalty report
SELECT 
    c.first_name,
    c.last_name,
    c.email,
    COUNT(b.id) AS total_bookings,
    SUM(b.amount) AS total_spent,
    AVG(b.customer_rating) AS average_rating
FROM users c
LEFT JOIN bookings b ON c.id = b.customer_id
WHERE c.user_type = 'customer'
GROUP BY c.id, c.first_name, c.last_name, c.email
HAVING total_bookings > 0
ORDER BY total_spent DESC;

-- Query 4: Get worker efficiency report
SELECT 
    w.first_name,
    w.last_name,
    w.email,
    COUNT(b.id) AS total_jobs,
    SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) AS completed_jobs,
    ROUND((SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) / COUNT(b.id)) * 100, 2) AS completion_rate,
    AVG(b.customer_rating) AS average_rating,
    SUM(b.amount) AS total_earnings
FROM users w
LEFT JOIN bookings b ON w.id = b.worker_id
WHERE w.user_type = 'worker'
GROUP BY w.id, w.first_name, w.last_name, w.email
ORDER BY completion_rate DESC;

-- Query 5: Get daily booking trends
SELECT 
    scheduled_date,
    COUNT(*) AS total_bookings,
    SUM(CASE WHEN vehicle_type = 'car' THEN 1 ELSE 0 END) AS car_bookings,
    SUM(CASE WHEN vehicle_type = 'bike' THEN 1 ELSE 0 END) AS bike_bookings,
    SUM(amount) AS daily_revenue
FROM bookings
WHERE scheduled_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
GROUP BY scheduled_date
ORDER BY scheduled_date DESC;

-- Query 6: Get payment method analysis
SELECT 
    payment_method,
    COUNT(*) AS usage_count,
    SUM(amount) AS total_amount,
    ROUND((COUNT(*) / (SELECT COUNT(*) FROM bookings)) * 100, 2) AS usage_percentage
FROM bookings
GROUP BY payment_method
ORDER BY usage_count DESC;

-- Query 7: Get customer satisfaction report
SELECT 
    CASE 
        WHEN customer_rating = 5 THEN 'Excellent (5)'
        WHEN customer_rating = 4 THEN 'Good (4)'
        WHEN customer_rating = 3 THEN 'Average (3)'
        WHEN customer_rating = 2 THEN 'Poor (2)'
        WHEN customer_rating = 1 THEN 'Very Poor (1)'
        ELSE 'No Rating'
    END AS rating_category,
    COUNT(*) AS count,
    ROUND((COUNT(*) / (SELECT COUNT(*) FROM bookings WHERE customer_rating IS NOT NULL)) * 100, 2) AS percentage
FROM bookings
GROUP BY customer_rating
ORDER BY customer_rating DESC;

-- Query 8: Get service location analysis
SELECT 
    SUBSTRING_INDEX(service_address, ',', 1) AS area,
    COUNT(*) AS booking_count,
    SUM(amount) AS total_revenue
FROM bookings
GROUP BY SUBSTRING_INDEX(service_address, ',', 1)
ORDER BY booking_count DESC;

-- Query 9: Get peak hours analysis
SELECT 
    HOUR(scheduled_time) AS hour_of_day,
    COUNT(*) AS booking_count,
    SUM(amount) AS total_revenue
FROM bookings
GROUP BY HOUR(scheduled_time)
ORDER BY booking_count DESC;

-- Query 10: Get vehicle type preference
SELECT 
    vehicle_type,
    COUNT(*) AS booking_count,
    SUM(amount) AS total_revenue,
    AVG(amount) AS average_amount,
    ROUND((COUNT(*) / (SELECT COUNT(*) FROM bookings)) * 100, 2) AS percentage
FROM bookings
GROUP BY vehicle_type
ORDER BY booking_count DESC;

-- Maintenance queries

-- Query 11: Clean up old completed bookings (older than 1 year)
-- DELETE FROM bookings WHERE status = 'completed' AND scheduled_date < DATE_SUB(CURDATE(), INTERVAL 1 YEAR);

-- Query 12: Archive old contact messages (older than 6 months)
-- DELETE FROM contact_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);

-- Query 13: Get database size information
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)',
    table_rows
FROM information_schema.tables 
WHERE table_schema = 'autoclean_bd'
ORDER BY (data_length + index_length) DESC;

-- Query 14: Check for orphaned records
SELECT 'Orphaned bookings without customers' AS issue, COUNT(*) AS count
FROM bookings b
LEFT JOIN users u ON b.customer_id = u.id
WHERE u.id IS NULL
UNION ALL
SELECT 'Orphaned bookings without workers' AS issue, COUNT(*) AS count
FROM bookings b
LEFT JOIN users u ON b.worker_id = u.id
WHERE b.worker_id IS NOT NULL AND u.id IS NULL
UNION ALL
SELECT 'Orphaned bookings without packages' AS issue, COUNT(*) AS count
FROM bookings b
LEFT JOIN service_packages sp ON b.package_id = sp.id
WHERE sp.id IS NULL;

-- Query 15: Get system health check
SELECT 
    'Total Users' AS metric, COUNT(*) AS value FROM users
UNION ALL
SELECT 'Active Users', COUNT(*) FROM users WHERE status = 'active'
UNION ALL
SELECT 'Total Bookings', COUNT(*) FROM bookings
UNION ALL
SELECT 'Pending Bookings', COUNT(*) FROM bookings WHERE status = 'pending'
UNION ALL
SELECT 'Completed Bookings', COUNT(*) FROM bookings WHERE status = 'completed'
UNION ALL
SELECT 'Total Revenue', SUM(amount) FROM bookings WHERE status = 'completed'
UNION ALL
SELECT 'Unread Messages', COUNT(*) FROM contact_messages WHERE status = 'unread'
UNION ALL
SELECT 'Active Service Packages', COUNT(*) FROM service_packages WHERE is_active = 1;

-- Backup queries (commented out for safety)

-- Query 16: Create backup of important data
-- CREATE TABLE bookings_backup AS SELECT * FROM bookings;
-- CREATE TABLE users_backup AS SELECT * FROM users;
-- CREATE TABLE service_packages_backup AS SELECT * FROM service_packages;

-- Query 17: Export data to CSV format (for reporting)
-- SELECT 'booking_id,customer_name,worker_name,package_name,vehicle_type,amount,status,scheduled_date' 
-- UNION ALL
-- SELECT CONCAT(b.booking_id, ',', 
--               CONCAT(c.first_name, ' ', c.last_name), ',',
--               CONCAT(w.first_name, ' ', w.last_name), ',',
--               sp.name, ',',
--               b.vehicle_type, ',',
--               b.amount, ',',
--               b.status, ',',
--               b.scheduled_date)
-- FROM bookings b
-- LEFT JOIN users c ON b.customer_id = c.id
-- LEFT JOIN users w ON b.worker_id = w.id
-- LEFT JOIN service_packages sp ON b.package_id = sp.id
-- INTO OUTFILE '/tmp/bookings_export.csv'
-- FIELDS TERMINATED BY ','
-- ENCLOSED BY '"'
-- LINES TERMINATED BY '\n';

-- Query 18: Reset demo data (for testing)
-- DELETE FROM bookings WHERE id > 8;
-- DELETE FROM worker_schedules WHERE id > 8;
-- DELETE FROM reviews WHERE id > 3;
-- DELETE FROM contact_messages WHERE id > 4;
-- DELETE FROM users WHERE id > 8;

-- Query 19: Get performance metrics
SELECT 
    'Average booking value' AS metric,
    ROUND(AVG(amount), 2) AS value
FROM bookings
WHERE status = 'completed'
UNION ALL
SELECT 
    'Average customer rating',
    ROUND(AVG(customer_rating), 2)
FROM bookings
WHERE customer_rating IS NOT NULL
UNION ALL
SELECT 
    'Booking completion rate (%)',
    ROUND((SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2)
FROM bookings
UNION ALL
SELECT 
    'Average response time (hours)',
    ROUND(AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)), 2)
FROM bookings
WHERE status IN ('confirmed', 'in-progress', 'completed');

-- Query 20: Get seasonal trends
SELECT 
    MONTH(scheduled_date) AS month,
    MONTHNAME(scheduled_date) AS month_name,
    COUNT(*) AS booking_count,
    SUM(amount) AS total_revenue,
    AVG(amount) AS average_amount
FROM bookings
WHERE YEAR(scheduled_date) = YEAR(CURDATE())
GROUP BY MONTH(scheduled_date), MONTHNAME(scheduled_date)
ORDER BY month;

-- End of additional queries
SELECT 'Additional analysis and maintenance queries added successfully!' AS completion_message; 