<?php
// Database configuration for XAMPP
define('DB_HOST', 'localhost');
define('DB_NAME', 'autoclean_bd');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create database connection
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

// Initialize database tables
function initializeDatabase() {
    $pdo = getDBConnection();
    if (!$pdo) {
        return false;
    }
    
    try {
        // Create users table (compatible with existing snake_case schema)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                phone VARCHAR(20) NOT NULL,
                address TEXT,
                password VARCHAR(255) NOT NULL,
                user_type ENUM('customer', 'admin', 'worker') DEFAULT 'customer',
                status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
                profile_image VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Create service_packages table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS service_packages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                carPrice DECIMAL(10,2) NOT NULL,
                bikePrice DECIMAL(10,2) NOT NULL,
                duration INT NOT NULL COMMENT 'Duration in minutes',
                isActive BOOLEAN DEFAULT TRUE,
                createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create workers table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS workers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                email VARCHAR(100),
                status ENUM('active', 'inactive', 'busy') DEFAULT 'active',
                currentJobId INT NULL,
                completedJobs INT DEFAULT 0,
                createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create bookings table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS bookings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                bookingId VARCHAR(20) UNIQUE NOT NULL,
                userId INT NOT NULL,
                vehicleType ENUM('car', 'bike') NOT NULL,
                servicePackage VARCHAR(50) NOT NULL,
                scheduledDate DATE NOT NULL,
                scheduledTime TIME NOT NULL,
                customerName VARCHAR(100) NOT NULL,
                customerPhone VARCHAR(20) NOT NULL,
                customerAddress TEXT NOT NULL,
                paymentMethod ENUM('cash', 'bkash', 'nagad') NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                status ENUM('pending', 'in-progress', 'completed', 'cancelled') DEFAULT 'pending',
                assignedWorkerId INT NULL,
                specialInstructions TEXT,
                createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (assignedWorkerId) REFERENCES workers(id) ON DELETE SET NULL
            )
            ");
        
        // Create reviews table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                bookingId INT NOT NULL,
                userId INT NOT NULL,
                rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
                comment TEXT,
                createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (bookingId) REFERENCES bookings(id) ON DELETE CASCADE,
                FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        
        // Create contact_messages table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                message TEXT NOT NULL,
                isRead BOOLEAN DEFAULT FALSE,
                createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Insert default admin user (using snake_case columns)
        $adminExists = $pdo->query("SELECT id FROM users WHERE email = 'admin@autocleanbd.com'")->fetch();
        if (!$adminExists) {
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->exec("
                INSERT INTO users (first_name, last_name, email, phone, address, password, user_type) 
                VALUES ('Admin', 'User', 'admin@autocleanbd.com', '+880 1234-567890', 'Bashundhara R/A, Dhaka', '$hashedPassword', 'admin')
            ");
        }
        
        // Insert default service packages
        $packagesExist = $pdo->query("SELECT id FROM service_packages LIMIT 1")->fetch();
        if (!$packagesExist) {
            $pdo->exec("
                INSERT INTO service_packages (name, description, carPrice, bikePrice, duration) VALUES
                ('Basic', 'Exterior wash, interior vacuum, window cleaning, tire cleaning', 299.00, 199.00, 30),
                ('Premium', 'Everything in Basic plus wax application, interior detailing, dashboard cleaning, air freshener', 599.00, 399.00, 60),
                ('Detailing', 'Everything in Premium plus paint protection, deep interior cleaning, engine bay cleaning, odor elimination, showroom finish', 899.00, 599.00, 180)
            ");
        }
        
        // Insert sample workers
        $workersExist = $pdo->query("SELECT id FROM workers LIMIT 1")->fetch();
        if (!$workersExist) {
            $pdo->exec("
                INSERT INTO workers (name, phone, email, status) VALUES
                ('Worker 1', '+880 1712345678', 'worker1@autocleanbd.com', 'active'),
                ('Worker 2', '+880 1812345678', 'worker2@autocleanbd.com', 'active'),
                ('Worker 3', '+880 1912345678', 'worker3@autocleanbd.com', 'active'),
                ('Worker 4', '+880 1612345678', 'worker4@autocleanbd.com', 'active'),
                ('Worker 5', '+880 1512345678', 'worker5@autocleanbd.com', 'active')
            ");
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Database initialization failed: " . $e->getMessage());
        return false;
    }
}

// Helper function to generate booking ID
function generateBookingId() {
    return 'BK' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Helper function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Helper function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Helper function to send JSON response
function sendJSONResponse($success, $message = '', $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Helper function to check if user is logged in
function checkUserSession() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        sendJSONResponse(false, 'User not authenticated');
    }
    return $_SESSION['user_id'];
}

// Helper function to check if user is admin
function checkAdminSession() {
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
        sendJSONResponse(false, 'Admin access required');
    }
    return $_SESSION['user_id'];
}

// Initialize database on first run
if (!file_exists(__DIR__ . '/database_initialized.txt')) {
    if (initializeDatabase()) {
        file_put_contents(__DIR__ . '/database_initialized.txt', 'Database initialized on ' . date('Y-m-d H:i:s'));
    }
}
?>