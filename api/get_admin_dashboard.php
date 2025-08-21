<?php
require_once 'config.php';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(false, 'Invalid request method');
}

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    sendJSONResponse(false, 'Database connection failed');
}

try {
    // Get overall statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as totalBookings,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pendingBookings,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completedBookings,
            SUM(CASE WHEN status = 'in-progress' THEN 1 ELSE 0 END) as inProgressBookings,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelledBookings,
            SUM(amount) as totalRevenue
        FROM bookings
    ");
    $stmt->execute();
    $stats = $stmt->fetch();
    
    // Get customer count (supports snake_case and camelCase)
    $stmt = $pdo->prepare("SELECT COUNT(*) as totalCustomers FROM users WHERE COALESCE(userType, user_type) = 'customer'");
    $stmt->execute();
    $customerCount = $stmt->fetch()['totalCustomers'];
    
    // Get active workers count
    $stmt = $pdo->prepare("SELECT COUNT(*) as activeWorkers FROM workers WHERE status = 'active'");
    $stmt->execute();
    $workerCount = $stmt->fetch()['activeWorkers'];
    
    // Get recent bookings (last 10) - alias snake_case to camelCase for downstream formatting
    $stmt = $pdo->prepare("
        SELECT 
            b.booking_id AS bookingId,
            b.customer_name AS customerName,
            b.customer_phone AS customerPhone,
            b.vehicle_type AS vehicleType,
            b.service_package AS servicePackage,
            b.scheduled_date AS scheduledDate,
            b.scheduled_time AS scheduledTime,
            b.status,
            b.amount,
            w.name as workerName
        FROM bookings b
        LEFT JOIN workers w ON b.assigned_worker_id = w.id
        ORDER BY b.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recentBookings = $stmt->fetchAll();
    
    // Get all bookings (aliased)
    $stmt = $pdo->prepare("
        SELECT 
            b.booking_id AS bookingId,
            b.customer_name AS customerName,
            b.customer_phone AS customerPhone,
            b.vehicle_type AS vehicleType,
            b.service_package AS servicePackage,
            b.scheduled_date AS scheduledDate,
            b.scheduled_time AS scheduledTime,
            b.status,
            b.amount,
            w.name as workerName
        FROM bookings b
        LEFT JOIN workers w ON b.assigned_worker_id = w.id
        ORDER BY b.created_at DESC
    ");
    $stmt->execute();
    $allBookings = $stmt->fetchAll();
    
    // Get workers (aliased)
    $stmt = $pdo->prepare("
        SELECT 
            id,
            name,
            phone,
            email,
            status,
            current_job_id AS currentJobId,
            completed_jobs AS completedJobs,
            created_at AS createdAt
        FROM workers
        ORDER BY name
    ");
    $stmt->execute();
    $workers = $stmt->fetchAll();
    
    // Get service packages (aliased)
    $stmt = $pdo->prepare("
        SELECT 
            id,
            name,
            description,
            car_price AS carPrice,
            bike_price AS bikePrice,
            duration,
            is_active AS isActive
        FROM service_packages
        ORDER BY name
    ");
    $stmt->execute();
    $packages = $stmt->fetchAll();
    
    // Get customers (aliased)
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            COALESCE(u.firstName, u.first_name) AS firstName,
            COALESCE(u.lastName, u.last_name) AS lastName,
            u.email,
            u.phone,
            COALESCE(u.vehicleType, u.vehicle_type) AS vehicleType,
            COALESCE(u.createdAt, u.created_at) AS createdAt,
            COUNT(b.id) as totalBookings,
            SUM(b.amount) as totalSpent,
            MAX(COALESCE(b.createdAt, b.created_at)) as lastBooking
        FROM users u
        LEFT JOIN bookings b ON u.id = COALESCE(b.userId, b.user_id)
        WHERE COALESCE(u.userType, u.user_type) = 'customer'
        GROUP BY u.id
        ORDER BY COALESCE(u.createdAt, u.created_at) DESC
    ");
    $stmt->execute();
    $customers = $stmt->fetchAll();
    
    // Format recent bookings
    $formattedRecentBookings = [];
    foreach ($recentBookings as $booking) {
        $formattedRecentBookings[] = [
            'id' => $booking['bookingId'],
            'customer' => $booking['customerName'],
            'phone' => $booking['customerPhone'],
            'service' => ucfirst($booking['vehicleType']) . ' Wash',
            'package' => $booking['servicePackage'],
            'dateTime' => $booking['scheduledDate'] . ' ' . $booking['scheduledTime'],
            'status' => $booking['status'],
            'worker' => $booking['workerName'] ?: '-',
            'amount' => '৳' . number_format($booking['amount'])
        ];
    }
    
    // Format all bookings
    $formattedAllBookings = [];
    foreach ($allBookings as $booking) {
        $formattedAllBookings[] = [
            'id' => $booking['bookingId'],
            'customer' => $booking['customerName'],
            'phone' => $booking['customerPhone'],
            'service' => ucfirst($booking['vehicleType']) . ' Wash',
            'package' => $booking['servicePackage'],
            'dateTime' => $booking['scheduledDate'] . ' ' . $booking['scheduledTime'],
            'status' => $booking['status'],
            'worker' => $booking['workerName'] ?: '-',
            'amount' => '৳' . number_format($booking['amount'])
        ];
    }
    
    // Format workers
    $formattedWorkers = [];
    foreach ($workers as $worker) {
        $formattedWorkers[] = [
            'id' => $worker['id'],
            'name' => $worker['name'],
            'phone' => $worker['phone'],
            'email' => $worker['email'],
            'status' => $worker['status'],
            'currentJob' => $worker['currentJobId'] ? 'Job #' . $worker['currentJobId'] : 'Available',
            'completedJobs' => $worker['completedJobs']
        ];
    }
    
    // Format packages
    $formattedPackages = [];
    foreach ($packages as $package) {
        $formattedPackages[] = [
            'id' => $package['id'],
            'name' => $package['name'],
            'description' => $package['description'],
            'carPrice' => '৳' . number_format($package['carPrice']),
            'bikePrice' => '৳' . number_format($package['bikePrice']),
            'duration' => $package['duration'] . ' minutes',
            'status' => $package['isActive'] ? 'Active' : 'Inactive'
        ];
    }
    
    // Format customers
    $formattedCustomers = [];
    foreach ($customers as $customer) {
        $formattedCustomers[] = [
            'id' => $customer['id'],
            'name' => $customer['firstName'] . ' ' . $customer['lastName'],
            'email' => $customer['email'],
            'phone' => $customer['phone'],
            'totalBookings' => (int)$customer['totalBookings'],
            'totalSpent' => '৳' . number_format($customer['totalSpent'] ?: 0),
            'lastBooking' => $customer['lastBooking'] ?: 'Never'
        ];
    }
    
    // Prepare response data
    $responseData = [
        'stats' => [
            'totalBookings' => (int)$stats['totalBookings'],
            'pendingBookings' => (int)$stats['pendingBookings'],
            'completedBookings' => (int)$stats['completedBookings'],
            'totalRevenue' => '৳' . number_format($stats['totalRevenue'] ?: 0),
            'activeWorkers' => (int)$workerCount,
            'totalCustomers' => (int)$customerCount
        ],
        'recentBookings' => $formattedRecentBookings,
        'allBookings' => $formattedAllBookings,
        'workers' => $formattedWorkers,
        'packages' => $formattedPackages,
        'customers' => $formattedCustomers
    ];
    
    sendJSONResponse(true, 'Admin dashboard data retrieved successfully', $responseData);
    
} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    sendJSONResponse(false, 'Failed to retrieve admin dashboard data');
}
?> 