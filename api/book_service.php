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

if (!$input) {
    sendJSONResponse(false, 'Invalid JSON input');
}

// Normalize and validate fields from different clients (homepage vs dashboard)
// vehicleType
$vehicleType = isset($input['vehicleType']) ? strtolower(sanitizeInput($input['vehicleType'])) : '';
// servicePackage: accept Basic/Premium/Detailing or basic/premium/detailing
$servicePackageInput = isset($input['servicePackage']) ? trim($input['servicePackage']) : '';
$servicePackageKey = strtolower($servicePackageInput);
if (in_array($servicePackageKey, ['basic','premium','detailing'])) {
    $servicePackage = $servicePackageKey;
} elseif (in_array($servicePackageInput, ['Basic','Premium','Detailing'])) {
    $servicePackage = strtolower($servicePackageInput);
} else {
    $servicePackage = '';
}
// Dates: bookingDate or scheduledDate
$bookingDate = isset($input['bookingDate']) ? sanitizeInput($input['bookingDate']) : (isset($input['scheduledDate']) ? sanitizeInput($input['scheduledDate']) : '');
// Times: bookingTime or scheduledTime
$bookingTime = isset($input['bookingTime']) ? sanitizeInput($input['bookingTime']) : (isset($input['scheduledTime']) ? sanitizeInput($input['scheduledTime']) : '');
// Customer contact: may be filled from user if not provided
$customerName = isset($input['customerName']) ? sanitizeInput($input['customerName']) : '';
$customerPhone = isset($input['customerPhone']) ? sanitizeInput($input['customerPhone']) : '';
$customerAddress = isset($input['customerAddress']) ? sanitizeInput($input['customerAddress']) : (isset($input['address']) ? sanitizeInput($input['address']) : '');
// Payment: accept labels or enum values
$paymentMethodInput = isset($input['paymentMethod']) ? trim($input['paymentMethod']) : '';
$pm = strtolower($paymentMethodInput);
if ($pm === 'cash on delivery' || $pm === 'cash') {
    $paymentMethod = 'cash';
} elseif ($pm === 'bkash' || $paymentMethodInput === 'bKash') {
    $paymentMethod = 'bkash';
} elseif ($pm === 'nagad') {
    $paymentMethod = 'nagad';
} else {
    $paymentMethod = '';
}
$specialInstructions = isset($input['specialInstructions']) ? sanitizeInput($input['specialInstructions']) : '';

// Presence checks
if ($vehicleType === '' || $servicePackage === '' || $bookingDate === '' || $bookingTime === '' || $paymentMethod === '' || $customerAddress === '') {
    sendJSONResponse(false, 'Missing required booking details');
}

// Validate vehicle type
if (!in_array($vehicleType, ['car', 'bike'])) {
    sendJSONResponse(false, 'Invalid vehicle type');
}

// Validate service package
if (!in_array($servicePackage, ['basic', 'premium', 'detailing'])) {
    sendJSONResponse(false, 'Invalid service package');
}

// Validate payment method
if (!in_array($paymentMethod, ['cash', 'bkash', 'nagad'])) {
    sendJSONResponse(false, 'Invalid payment method');
}

// Validate date (must be today or future)
$bookingDateTime = new DateTime($bookingDate . ' ' . $bookingTime);
$now = new DateTime();
if ($bookingDateTime <= $now) {
    sendJSONResponse(false, 'Booking date and time must be in the future');
}

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    sendJSONResponse(false, 'Database connection failed');
}

try {
    // Get package pricing
    $stmt = $pdo->prepare("SELECT car_price AS carPrice, bike_price AS bikePrice FROM service_packages WHERE name = ? AND is_active = 1");
    $stmt->execute([ucfirst($servicePackage)]);
    $package = $stmt->fetch();
    
    if (!$package) {
        sendJSONResponse(false, 'Service package not found');
    }
    
    // Calculate amount based on vehicle type
    $amount = $vehicleType === 'car' ? $package['carPrice'] : $package['bikePrice'];
    
    // Generate unique booking ID
    $bookingId = generateBookingId();
    
    // Check if booking ID already exists
    $stmt = $pdo->prepare("SELECT id FROM bookings WHERE booking_id = ?");
    $stmt->execute([$bookingId]);
    while ($stmt->fetch()) {
        $bookingId = generateBookingId();
    }
    
    // Check for time slot availability
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE scheduled_date = ? AND scheduled_time = ? AND status IN ('pending', 'in-progress')");
    $stmt->execute([$bookingDate, $bookingTime]);
    $existingBookings = $stmt->fetch()['count'];
    
    // Limit to 2 bookings per time slot
    if ($existingBookings >= 2) {
        sendJSONResponse(false, 'This time slot is already fully booked. Please select a different time.');
    }
    
    // Resolve user and backfill customer details
    $userId = 0;
    if (isset($input['userId']) && (int)$input['userId'] > 0) {
        $userId = (int)$input['userId'];
    } elseif (isset($input['customerId']) && (int)$input['customerId'] > 0) {
        $userId = (int)$input['customerId'];
    }
    if ($userId > 0) {
        $stmt = $pdo->prepare("SELECT id, COALESCE(firstName, first_name) AS firstName, COALESCE(lastName, last_name) AS lastName, phone, address FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $u = $stmt->fetch();
        if (!$u) {
            sendJSONResponse(false, 'Invalid user ID');
        }
        if (empty($customerName)) { $customerName = $u['firstName'] . ' ' . $u['lastName']; }
        if (empty($customerPhone)) { $customerPhone = $u['phone']; }
        if (empty($customerAddress)) { $customerAddress = $u['address']; }
    }
    if (empty($customerName) || empty($customerPhone) || empty($customerAddress)) {
        sendJSONResponse(false, 'Customer information is incomplete');
    }
    
    // Insert booking
    $stmt = $pdo->prepare("INSERT INTO bookings (booking_id, user_id, vehicle_type, service_package, scheduled_date, scheduled_time, customer_name, customer_phone, customer_address, payment_method, amount, special_instructions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $bookingId,
        $userId,
        $vehicleType,
        ucfirst($servicePackage),
        $bookingDate,
        $bookingTime,
        $customerName,
        $customerPhone,
        $customerAddress,
        $paymentMethod,
        $amount,
        $specialInstructions
    ]);
    
    $bookingId = $pdo->lastInsertId();
    
    // Prepare response data
    $responseData = [
        'bookingId' => $bookingId,
        'amount' => $amount,
        'scheduledDate' => $bookingDate,
        'scheduledTime' => $bookingTime,
        'status' => 'pending'
    ];
    
    // Send confirmation email (if email functionality is available)
    // sendBookingConfirmationEmail($customerEmail, $responseData);
    
    sendJSONResponse(true, 'Booking created successfully! We will contact you soon to confirm.', $responseData);
    
} catch (PDOException $e) {
    error_log("Booking creation error: " . $e->getMessage());
    sendJSONResponse(false, 'Failed to create booking. Please try again.');
}

// Function to send booking confirmation email (placeholder)
function sendBookingConfirmationEmail($email, $bookingData) {
    // This would integrate with an email service like PHPMailer
    // For now, just log the email details
    error_log("Booking confirmation email would be sent to: $email");
    error_log("Booking details: " . json_encode($bookingData));
}
?> 