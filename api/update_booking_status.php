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
if (empty($input['bookingId']) || empty($input['status'])) {
    sendJSONResponse(false, 'Booking ID and Status are required');
}

$bookingId = sanitizeInput($input['bookingId']);
$newStatus = sanitizeInput($input['status']);
$notes = isset($input['notes']) ? sanitizeInput($input['notes']) : '';

// Validate status
$validStatuses = ['pending', 'in-progress', 'completed', 'cancelled'];
if (!in_array($newStatus, $validStatuses)) {
    sendJSONResponse(false, 'Invalid status. Must be one of: ' . implode(', ', $validStatuses));
}

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    sendJSONResponse(false, 'Database connection failed');
}

try {
    // Check if booking exists
    $stmt = $pdo->prepare("
        SELECT id, status, assignedWorkerId, customerName, customerPhone, amount
        FROM bookings 
        WHERE bookingId = ?
    ");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        sendJSONResponse(false, 'Booking not found');
    }
    
    // Check if status transition is valid
    $currentStatus = $booking['status'];
    $validTransitions = [
        'pending' => ['in-progress', 'cancelled'],
        'in-progress' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => []
    ];
    
    if (!in_array($newStatus, $validTransitions[$currentStatus])) {
        sendJSONResponse(false, "Cannot change status from '$currentStatus' to '$newStatus'");
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    try {
        // Update booking status
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = ?, updatedAt = CURRENT_TIMESTAMP
            WHERE bookingId = ?
        ");
        $stmt->execute([$newStatus, $bookingId]);
        
        // Add notes if provided
        if ($notes) {
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET specialInstructions = CONCAT(COALESCE(specialInstructions, ''), '\nStatus Update: ', ?)
                WHERE bookingId = ?
            ");
            $stmt->execute([$notes, $bookingId]);
        }
        
        // Handle worker status based on booking status
        if ($booking['assignedWorkerId']) {
            if ($newStatus === 'completed') {
                // Mark job as completed and free up worker
                $stmt = $pdo->prepare("
                    UPDATE workers 
                    SET status = 'active', currentJobId = NULL, completedJobs = completedJobs + 1
                    WHERE id = ?
                ");
                $stmt->execute([$booking['assignedWorkerId']]);
                
                // Send completion notification to customer (placeholder)
                // sendCompletionNotification($booking['customerName'], $booking['customerPhone'], $bookingId);
                
            } elseif ($newStatus === 'cancelled') {
                // Free up worker if booking is cancelled
                $stmt = $pdo->prepare("
                    UPDATE workers 
                    SET status = 'active', currentJobId = NULL
                    WHERE id = ?
                ");
                $stmt->execute([$booking['assignedWorkerId']]);
                
                // Send cancellation notification to customer (placeholder)
                // sendCancellationNotification($booking['customerName'], $booking['customerPhone'], $bookingId);
            }
        }
        
        $pdo->commit();
        
        // Prepare response data
        $responseData = [
            'bookingId' => $bookingId,
            'oldStatus' => $currentStatus,
            'newStatus' => $newStatus,
            'updatedAt' => date('Y-m-d H:i:s')
        ];
        
        sendJSONResponse(true, 'Booking status updated successfully!', $responseData);
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Booking status update error: " . $e->getMessage());
    sendJSONResponse(false, 'Failed to update booking status. Please try again.');
}

// Function to send completion notification (placeholder)
function sendCompletionNotification($customerName, $customerPhone, $bookingId) {
    // This would integrate with SMS or email service
    error_log("Completion notification would be sent to: $customerName ($customerPhone)");
    error_log("Booking ID: $bookingId");
}

// Function to send cancellation notification (placeholder)
function sendCancellationNotification($customerName, $customerPhone, $bookingId) {
    // This would integrate with SMS or email service
    error_log("Cancellation notification would be sent to: $customerName ($customerPhone)");
    error_log("Booking ID: $bookingId");
}
?> 