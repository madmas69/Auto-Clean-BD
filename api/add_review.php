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

// Validate required fields
if (empty($input['bookingId']) || !isset($input['rating']) || empty($input['userId'])) {
    sendJSONResponse(false, 'Booking ID, Rating, and User ID are required');
}

$bookingId = (int)$input['bookingId'];
$rating = (int)$input['rating'];
$userId = (int)$input['userId'];
$comment = isset($input['comment']) ? sanitizeInput($input['comment']) : '';

// Validate rating
if ($rating < 1 || $rating > 5) {
    sendJSONResponse(false, 'Rating must be between 1 and 5');
}

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    sendJSONResponse(false, 'Database connection failed');
}

try {
    // Check if booking exists and belongs to user
    $stmt = $pdo->prepare("
        SELECT id, status, customerName 
        FROM bookings 
        WHERE id = ? AND userId = ? AND status = 'completed'
    ");
    $stmt->execute([$bookingId, $userId]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        sendJSONResponse(false, 'Booking not found or not eligible for review');
    }
    
    // Check if review already exists
    $stmt = $pdo->prepare("SELECT id FROM reviews WHERE bookingId = ?");
    $stmt->execute([$bookingId]);
    if ($stmt->fetch()) {
        sendJSONResponse(false, 'Review already exists for this booking');
    }
    
    // Insert review
    $stmt = $pdo->prepare("
        INSERT INTO reviews (bookingId, userId, rating, comment) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([$bookingId, $userId, $rating, $comment]);
    
    $reviewId = $pdo->lastInsertId();
    
    // Get the created review data
    $stmt = $pdo->prepare("
        SELECT r.id, r.rating, r.comment, r.createdAt, u.firstName, u.lastName
        FROM reviews r
        JOIN users u ON r.userId = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$reviewId]);
    $review = $stmt->fetch();
    
    if (!$review) {
        sendJSONResponse(false, 'Failed to retrieve created review data');
    }
    
    // Send notification to admin (placeholder)
    // sendReviewNotification($booking['customerName'], $rating, $comment);
    
    sendJSONResponse(true, 'Review submitted successfully! Thank you for your feedback.', $review);
    
} catch (PDOException $e) {
    error_log("Add review error: " . $e->getMessage());
    sendJSONResponse(false, 'Failed to submit review. Please try again.');
}

// Function to send review notification (placeholder)
function sendReviewNotification($customerName, $rating, $comment) {
    // This would integrate with email or notification service
    error_log("Review notification would be sent to admin");
    error_log("Customer: $customerName, Rating: $rating stars");
    if ($comment) {
        error_log("Comment: $comment");
    }
}
?> 