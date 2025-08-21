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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['userId'])) {
    sendJSONResponse(false, 'User ID is required');
}

$userId = (int)$input['userId'];

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    sendJSONResponse(false, 'Database connection failed');
}

try {
    // Get user information
    $stmt = $pdo->prepare("
        SELECT id,
               COALESCE(firstName, first_name) AS firstName,
               COALESCE(lastName, last_name) AS lastName,
               email,
               phone,
               address,
               COALESCE(vehicleType, vehicle_type) AS vehicleType,
               COALESCE(userType, user_type) AS userType,
               COALESCE(createdAt, created_at) AS createdAt
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        sendJSONResponse(false, 'User not found');
    }
    
    // Get booking statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as totalBookings,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completedBookings,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pendingBookings,
            SUM(CASE WHEN status = 'in-progress' THEN 1 ELSE 0 END) as inProgressBookings,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelledBookings,
            SUM(amount) as totalSpent
        FROM bookings 
        WHERE userId = ?
    ");
    $stmt->execute([$userId]);
    $stats = $stmt->fetch();
    
    // Get recent bookings (last 5)
    $stmt = $pdo->prepare("
        SELECT 
            b.booking_id AS bookingId,
            b.vehicle_type AS vehicleType,
            b.service_package AS servicePackage,
            b.scheduled_date AS scheduledDate,
            b.scheduled_time AS scheduledTime,
            b.status,
            b.amount,
            b.created_at AS createdAt,
            w.name as workerName
        FROM bookings b
        LEFT JOIN workers w ON b.assigned_worker_id = w.id
        WHERE COALESCE(b.userId, b.user_id) = ?
        ORDER BY COALESCE(b.createdAt, b.created_at) DESC
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $recentBookings = $stmt->fetchAll();
    
    // Get all bookings
    $stmt = $pdo->prepare("
        SELECT 
            b.booking_id AS bookingId,
            b.vehicle_type AS vehicleType,
            b.service_package AS servicePackage,
            b.scheduled_date AS scheduledDate,
            b.scheduled_time AS scheduledTime,
            b.status,
            b.amount,
            b.created_at AS createdAt,
            w.name as workerName
        FROM bookings b
        LEFT JOIN workers w ON b.assigned_worker_id = w.id
        WHERE COALESCE(b.userId, b.user_id) = ?
        ORDER BY COALESCE(b.createdAt, b.created_at) DESC
    ");
    $stmt->execute([$userId]);
    $allBookings = $stmt->fetchAll();
    
    // Format the data
    $formattedRecentBookings = [];
    foreach ($recentBookings as $booking) {
        $formattedRecentBookings[] = [
            'id' => $booking['bookingId'],
            'date' => $booking['scheduledDate'],
            'service' => ucfirst($booking['vehicleType']) . ' Wash',
            'package' => $booking['servicePackage'],
            'status' => $booking['status'],
            'amount' => '৳' . number_format($booking['amount']),
            'scheduledDate' => $booking['scheduledDate'],
            'scheduledTime' => $booking['scheduledTime'],
            'worker' => $booking['workerName'] ?: '-'
        ];
    }
    
    $formattedAllBookings = [];
    foreach ($allBookings as $booking) {
        $formattedAllBookings[] = [
            'id' => $booking['bookingId'],
            'date' => $booking['scheduledDate'],
            'service' => ucfirst($booking['vehicleType']) . ' Wash',
            'package' => $booking['servicePackage'],
            'status' => $booking['status'],
            'amount' => '৳' . number_format($booking['amount']),
            'scheduledDate' => $booking['scheduledDate'],
            'scheduledTime' => $booking['scheduledTime'],
            'worker' => $booking['workerName'] ?: '-'
        ];
    }
    
    // Prepare response data
    $responseData = [
        'user' => $user,
        'stats' => [
            'totalBookings' => (int)$stats['totalBookings'],
            'completedBookings' => (int)$stats['completedBookings'],
            'pendingBookings' => (int)$stats['pendingBookings'],
            'totalSpent' => '৳' . number_format($stats['totalSpent'] ?: 0)
        ],
        'recentBookings' => $formattedRecentBookings,
        'allBookings' => $formattedAllBookings
    ];
    
    sendJSONResponse(true, 'Dashboard data retrieved successfully', $responseData);
    
} catch (PDOException $e) {
    error_log("Dashboard data error: " . $e->getMessage());
    sendJSONResponse(false, 'Failed to retrieve dashboard data');
}
?> 