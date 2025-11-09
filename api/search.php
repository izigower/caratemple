<?php
/**
 * Ajax endpoint for instant search.
 *
 * @package CaraTemple\API
 */

declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get search query
$query = $_GET['q'] ?? '';
$query = trim($query);

if ($query === '') {
    echo json_encode(['success' => true, 'results' => []]);
    exit;
}

if (strlen($query) < 2) {
    echo json_encode(['success' => true, 'results' => [], 'message' => 'Tape au moins 2 caractères']);
    exit;
}

try {
    $pdo = getDatabaseConnection();
    
    // Search in discussions (title and body)
    $stmt = $pdo->prepare(
        'SELECT d.id, d.title, d.category, d.views_count, d.created_at,
                u.username,
                (
                    SELECT COUNT(*)
                    FROM discussion_posts p
                    WHERE p.discussion_id = d.id AND p.is_deleted = 0
                ) - 1 AS replies_count
         FROM discussions d
         INNER JOIN users u ON u.id = d.user_id
         WHERE d.title LIKE :query OR d.body LIKE :query
         ORDER BY d.created_at DESC
         LIMIT 10'
    );
    
    $searchPattern = '%' . $query . '%';
    $stmt->bindValue(':query', $searchPattern, PDO::PARAM_STR);
    $stmt->execute();
    
    $results = $stmt->fetchAll();
    
    // Format results
    $formattedResults = [];
    foreach ($results as $result) {
        $formattedResults[] = [
            'id' => (int) $result['id'],
            'title' => $result['title'],
            'category' => $result['category'],
            'username' => $result['username'],
            'replies_count' => (int) $result['replies_count'],
            'views_count' => (int) $result['views_count'],
            'created_at' => $result['created_at'],
            'url' => BASE_URL . '/views/discussion.php?id=' . (int) $result['id']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'results' => $formattedResults,
        'count' => count($formattedResults)
    ]);
} catch (Exception $e) {
    error_log('Search error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
