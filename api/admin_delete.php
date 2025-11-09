<?php
/**
 * Ajax endpoint for admin deletion actions.
 *
 * @package CaraTemple\API
 */

declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin.php';
require_once __DIR__ . '/../includes/helpers.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check admin authentication
$adminUser = current_user();
if ($adminUser === null || $adminUser['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Admin access required']);
    exit;
}

// Get action and validate CSRF
$action = $_POST['action'] ?? '';
$csrfKey = $_POST['csrf_key'] ?? '';
$csrfToken = $_POST['csrf_token'] ?? '';

if ($csrfKey === '' || !validate_csrf_token($csrfKey, $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

try {
    switch ($action) {
        case 'delete_user':
            $userId = (int) ($_POST['user_id'] ?? 0);
            $targetUser = admin_find_user($userId);

            if ($userId < 1 || $targetUser === null) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'User not found']);
                exit;
            }

            if ($userId === $adminUser['id']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Cannot delete your own admin account']);
                exit;
            }

            if ((int) $targetUser['is_admin'] === 1 && admin_count_admins() <= 1) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Cannot delete the last administrator']);
                exit;
            }

            if (admin_delete_user($userId)) {
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to delete user']);
            }
            break;

        case 'delete_discussion':
            $discussionId = (int) ($_POST['discussion_id'] ?? 0);

            if ($discussionId < 1) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Discussion not found']);
                exit;
            }

            if (admin_delete_discussion($discussionId)) {
                echo json_encode(['success' => true, 'message' => 'Discussion deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to delete discussion']);
            }
            break;

        case 'delete_post':
            $postId = (int) ($_POST['post_id'] ?? 0);
            $post = admin_find_post($postId);

            if ($postId < 1 || $post === null) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Post not found']);
                exit;
            }

            if ((int) $post['is_root'] === 1) {
                if (admin_delete_discussion((int) $post['discussion_id'])) {
                    echo json_encode(['success' => true, 'message' => 'Discussion deleted (root post)', 'redirect' => BASE_URL . '/admin/index.php']);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Failed to delete discussion']);
                }
                exit;
            }

            if ((int) $post['is_deleted'] === 1) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Post already deleted']);
                exit;
            }

            if (admin_delete_post($postId)) {
                echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to delete post']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log('Admin delete error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
