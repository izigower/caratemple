<?php
/**
 * Ajax endpoint for posting replies.
 *
 * @package CaraTemple\API
 */

declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/discussions.php';
require_once __DIR__ . '/../includes/helpers.php';

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

// Get and validate input
$discussionId = filter_input(INPUT_POST, 'discussion_id', FILTER_VALIDATE_INT);
$message = $_POST['message'] ?? '';
$csrfToken = $_POST['_token'] ?? '';
$csrfKey = $_POST['csrf_key'] ?? '';

if ($discussionId === false || $discussionId === null || $discussionId < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid discussion ID']);
    exit;
}

// Validate CSRF token
if ($csrfKey === '' || !validate_csrf_token($csrfKey, $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Validate message
$message = trim($message);
if ($message === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Le message est requis']);
    exit;
}

if (strlen($message) < 3) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Le message doit contenir au moins 3 caractères']);
    exit;
}

try {
    // Check if discussion exists
    $discussion = fetch_discussion($discussionId);
    if ($discussion === null) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Discussion not found']);
        exit;
    }

    // Create the reply
    $postId = create_discussion_post($discussionId, $currentUser['id'], $message, false);

    if ($postId === null) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to create reply']);
        exit;
    }

    // Get the created post with user info
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare(
        'SELECT p.id, p.body, p.created_at, p.updated_at, u.username
         FROM discussion_posts p
         INNER JOIN users u ON u.id = p.user_id
         WHERE p.id = :post_id
         LIMIT 1'
    );
    $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch();

    if ($post === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to retrieve created post']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Réponse publiée avec succès',
        'post' => [
            'id' => (int) $post['id'],
            'body' => $post['body'],
            'username' => $post['username'],
            'created_at' => $post['created_at'],
            'relative_time' => format_relative_time($post['created_at'])
        ]
    ]);
} catch (Exception $e) {
    error_log('Post reply error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
