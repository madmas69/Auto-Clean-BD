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

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    sendJSONResponse(false, 'Database connection failed');
}

try {
    $userId = isset($input['userId']) ? (int)$input['userId'] : null;
    $limit = isset($input['limit']) ? (int)$input['limit'] : 10;
    
    // Build query based on whether we want user-specific reviews or all reviews
    if ($userId) {
        // Get reviews for specific user
        $stmt = $pdo->prepare("
            SELECT 
                r.id,
                r.rating,
                r.comment,
                r.createdAt,
                u.firstName,
                u.lastName,
                b.bookingId,
                b.servicePackage,
                b.vehicleType
            FROM reviews r
            JOIN users u ON r.userId = u.id
            JOIN bookings b ON r.bookingId = b.id
            WHERE r.userId = ?
            ORDER BY r.createdAt DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
    } else {
        // Get all reviews for public display
        $stmt = $pdo->prepare("
            SELECT 
                r.id,
                r.rating,
                r.comment,
                r.createdAt,
                u.firstName,
                u.lastName,
                b.bookingId,
                b.servicePackage,
                b.vehicleType
            FROM reviews r
            JOIN users u ON r.userId = u.id
            JOIN bookings b ON r.bookingId = b.id
            ORDER BY r.createdAt DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
    }
    
    $reviews = $stmt->fetchAll();
    
    // Get review statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as totalReviews,
            AVG(rating) as averageRating,
            COUNT(CASE WHEN rating = 5 THEN 1 END) as fiveStar,
            COUNT(CASE WHEN rating = 4 THEN 1 END) as fourStar,
            COUNT(CASE WHEN rating = 3 THEN 1 END) as threeStar,
            COUNT(CASE WHEN rating = 2 THEN 1 END) as twoStar,
            COUNT(CASE WHEN rating = 1 THEN 1 END) as oneStar
        FROM reviews
    ");
    $stmt->execute();
    $stats = $stmt->fetch();
    
    // Format reviews
    $formattedReviews = [];
    foreach ($reviews as $review) {
        $formattedReviews[] = [
            'id' => $review['id'],
            'rating' => (int)$review['rating'],
            'comment' => $review['comment'],
            'customerName' => $review['firstName'] . ' ' . $review['lastName'],
            'service' => $review['servicePackage'] . ' (' . ucfirst($review['vehicleType']) . ')',
            'date' => date('M d, Y', strtotime($review['createdAt'])),
            'createdAt' => $review['createdAt']
        ];
    }
    
    // Format statistics
    $formattedStats = [
        'totalReviews' => (int)$stats['totalReviews'],
        'averageRating' => round($stats['averageRating'], 1),
        'ratingBreakdown' => [
            'fiveStar' => (int)$stats['fiveStar'],
            'fourStar' => (int)$stats['fourStar'],
            'threeStar' => (int)$stats['threeStar'],
            'twoStar' => (int)$stats['twoStar'],
            'oneStar' => (int)$stats['oneStar']
        ]
    ];
    
    // Prepare response data
    $responseData = [
        'reviews' => $formattedReviews,
        'stats' => $formattedStats
    ];
    
    sendJSONResponse(true, 'Reviews retrieved successfully', $responseData);
    
} catch (PDOException $e) {
    error_log("Get reviews error: " . $e->getMessage());
    sendJSONResponse(false, 'Failed to retrieve reviews');
}
?> 