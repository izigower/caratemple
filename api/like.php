<?php
/**
 * Ajax endpoint for toggling post likes.
 *
 * @package CaraTemple\API
 */

declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/discussions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check authentication
$currentUser = current_user();
if ($currentUser === null) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Get post ID from request
$postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
if ($postId === false || $postId === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
    exit;
}

try {
    // Toggle the like
    toggle_post_like($postId, $currentUser['id']);

    // Get updated like count
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM post_likes WHERE post_id = :post_id');
    $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();
    $likesCount = (int) $result['count'];

    // Check if current user has liked
    $stmt = $pdo->prepare('SELECT 1 FROM post_likes WHERE post_id = :post_id AND user_id = :user_id LIMIT 1');
    $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $currentUser['id'], PDO::PARAM_INT);
    $stmt->execute();
    $isLiked = $stmt->fetch() !== false;

    echo json_encode([
        'success' => true,
        'likes_count' => $likesCount,
        'is_liked' => $isLiked
    ]);
} catch (Exception $e) {
    error_log('Like toggle error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
