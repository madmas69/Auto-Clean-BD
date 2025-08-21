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
if (empty($input['bookingId']) || empty($input['workerId'])) {
    sendJSONResponse(false, 'Booking ID and Worker ID are required');
}

$bookingId = sanitizeInput($input['bookingId']);
$workerId = (int)$input['workerId'];
$notes = isset($input['notes']) ? sanitizeInput($input['notes']) : '';

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    sendJSONResponse(false, 'Database connection failed');
}

try {
    // Check if booking exists and is pending
    $stmt = $pdo->prepare("
        SELECT id, status, customerName, scheduledDate, scheduledTime 
        FROM bookings 
        WHERE bookingId = ?
    ");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        sendJSONResponse(false, 'Booking not found');
    }
    
    if ($booking['status'] !== 'pending') {
        sendJSONResponse(false, 'Can only assign workers to pending bookings');
    }
    
    // Check if worker exists and is available
    $stmt = $pdo->prepare("
        SELECT id, name, status, currentJobId 
        FROM workers 
        WHERE id = ?
    ");
    $stmt->execute([$workerId]);
    $worker = $stmt->fetch();
    
    if (!$worker) {
        sendJSONResponse(false, 'Worker not found');
    }
    
    if ($worker['status'] !== 'active') {
        sendJSONResponse(false, 'Worker is not available');
    }
    
    if ($worker['currentJobId']) {
        sendJSONResponse(false, 'Worker is already assigned to another job');
    }
    
    // Check for time conflicts
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM bookings 
        WHERE assignedWorkerId = ? 
        AND scheduledDate = ? 
        AND scheduledTime = ? 
        AND status IN ('pending', 'in-progress')
    ");
    $stmt->execute([$workerId, $booking['scheduledDate'], $booking['scheduledTime']]);
    $conflicts = $stmt->fetch()['count'];
    
    if ($conflicts > 0) {
        sendJSONResponse(false, 'Worker is already assigned to another booking at this time');
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    try {
        // Update booking with worker assignment
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET assignedWorkerId = ?, status = 'in-progress', updatedAt = CURRENT_TIMESTAMP
            WHERE bookingId = ?
        ");
        $stmt->execute([$workerId, $bookingId]);
        
        // Update worker status
        $stmt = $pdo->prepare("
            UPDATE workers 
            SET status = 'busy', currentJobId = ?
            WHERE id = ?
        ");
        $stmt->execute([$booking['id'], $workerId]);
        
        // Log the assignment (optional - you can create a separate table for this)
        // For now, we'll just update the booking with notes
        if ($notes) {
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET specialInstructions = CONCAT(COALESCE(specialInstructions, ''), '\nWorker Assignment: ', ?)
                WHERE bookingId = ?
            ");
            $stmt->execute([$notes, $bookingId]);
        }
        
        $pdo->commit();
        
        // Send notification to worker (placeholder)
        // sendWorkerAssignmentNotification($worker['name'], $booking);
        
        sendJSONResponse(true, 'Worker assigned successfully!', [
            'bookingId' => $bookingId,
            'workerName' => $worker['name'],
            'status' => 'in-progress'
        ]);
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Worker assignment error: " . $e->getMessage());
    sendJSONResponse(false, 'Failed to assign worker. Please try again.');
}

// Function to send worker assignment notification (placeholder)
function sendWorkerAssignmentNotification($workerName, $booking) {
    // This would integrate with SMS or email service
    // For now, just log the notification
    error_log("Worker assignment notification would be sent to: $workerName");
    error_log("Booking details: " . json_encode($booking));
}
?> 