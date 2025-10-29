<?php
/**
 * Discussion data access layer for CaraTemple.
 *
 * Manages CRUD operations for discussions, posts and likes using prepared statements.
 *
 * @package CaraTemple\Includes
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/helpers.php';

/**
 * Retrieve the latest discussions with metadata.
 *
 * @param int $limit
 *
 * @return array<int, array<string, mixed>>
 */
function fetch_latest_discussions(int $limit = 12): array
{
    $pdo = getDatabaseConnection();

    $statement = $pdo->prepare(
        'SELECT d.id, d.title, d.category, d.tag_line, d.body, d.views_count, d.created_at, d.updated_at,
                u.username,
                GREATEST((
                    SELECT COUNT(*)
                    FROM discussion_posts p
                    WHERE p.discussion_id = d.id AND p.is_deleted = 0
                ) - 1, 0) AS replies_count
         FROM discussions d
         INNER JOIN users u ON u.id = d.user_id
         ORDER BY COALESCE(d.updated_at, d.created_at) DESC
         LIMIT :limit'
    );
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();

    return $statement->fetchAll();
}

/**
 * Fetch a single discussion with aggregated information.
 *
 * @return array<string, mixed>|null
 */
function fetch_discussion(int $discussionId): ?array
{
    $pdo = getDatabaseConnection();

    $statement = $pdo->prepare(
        'SELECT d.id, d.title, d.category, d.tag_line, d.body, d.views_count, d.created_at, d.updated_at,
                d.user_id, u.username
         FROM discussions d
         INNER JOIN users u ON u.id = d.user_id
         WHERE d.id = :id
         LIMIT 1'
    );
    $statement->bindValue(':id', $discussionId, PDO::PARAM_INT);
    $statement->execute();

    $discussion = $statement->fetch();

    if ($discussion === false) {
        return null;
    }

    return $discussion;
}

/**
 * Retrieve all visible posts for the provided discussion.
 *
 * @param int|null $currentUserId
 *
 * @return array<int, array<string, mixed>>
 */
function fetch_discussion_posts(int $discussionId, ?int $currentUserId = null): array
{
    $pdo = getDatabaseConnection();

    $statement = $pdo->prepare(
        'SELECT p.id, p.body, p.is_root, p.user_id, p.created_at, p.updated_at,
                u.username,
                (
                    SELECT COUNT(*)
                    FROM post_likes pl
                    WHERE pl.post_id = p.id
                ) AS likes_count,
                (
                    SELECT COUNT(*)
                    FROM discussion_posts child
                    WHERE child.discussion_id = p.discussion_id
                      AND child.is_deleted = 0
                ) AS total_replies,
                (
                    SELECT COUNT(*)
                    FROM post_likes pl2
                    WHERE pl2.post_id = p.id AND pl2.user_id = :currentUserId
                ) AS liked_by_user
         FROM discussion_posts p
         INNER JOIN users u ON u.id = p.user_id
         WHERE p.discussion_id = :discussionId
           AND p.is_deleted = 0
         ORDER BY p.created_at ASC'
    );
    $statement->bindValue(':discussionId', $discussionId, PDO::PARAM_INT);
    if ($currentUserId === null) {
        $statement->bindValue(':currentUserId', null, PDO::PARAM_NULL);
    } else {
        $statement->bindValue(':currentUserId', $currentUserId, PDO::PARAM_INT);
    }
    $statement->execute();

    return $statement->fetchAll();
}

/**
 * Create a new discussion.
 *
 * @return array{success: bool, errors: array<string, string>, discussion_id?: int}
 */
function create_discussion(int $userId, array $input): array
{
    $title = trim($input['title'] ?? '');
    $category = trim($input['category'] ?? 'Général');
    $tagLine = trim($input['tag_line'] ?? '');
    $body = trim($input['body'] ?? '');

    $errors = [];

    $allowedCategories = ['Général', 'Stratégie', 'Collection', 'Compétitif', 'Événement'];

    if ($title === '') {
        $errors['title'] = 'Le titre est requis.';
    } elseif (mb_strlen($title) < 6) {
        $errors['title'] = 'Le titre doit comporter au moins 6 caractères.';
    }

    if (!in_array($category, $allowedCategories, true)) {
        $category = 'Général';
    }

    if ($tagLine !== '' && mb_strlen($tagLine) > 120) {
        $errors['tag_line'] = 'Le résumé doit contenir 120 caractères maximum.';
    }

    if ($body === '') {
        $errors['body'] = 'Le contenu est requis.';
    } elseif (mb_strlen($body) < 20) {
        $errors['body'] = 'Développe ta question en au moins 20 caractères.';
    }

    if ($errors !== []) {
        return ['success' => false, 'errors' => $errors];
    }

    $pdo = getDatabaseConnection();
    $pdo->beginTransaction();

    try {
        $insertDiscussion = $pdo->prepare(
            'INSERT INTO discussions (user_id, title, category, tag_line, body) VALUES (:user_id, :title, :category, :tag_line, :body)'
        );
        $insertDiscussion->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $insertDiscussion->bindValue(':title', $title, PDO::PARAM_STR);
        $insertDiscussion->bindValue(':category', $category, PDO::PARAM_STR);
        if ($tagLine === '') {
            $insertDiscussion->bindValue(':tag_line', null, PDO::PARAM_NULL);
        } else {
            $insertDiscussion->bindValue(':tag_line', $tagLine, PDO::PARAM_STR);
        }
        $insertDiscussion->bindValue(':body', $body, PDO::PARAM_STR);
        $insertDiscussion->execute();

        $discussionId = (int) $pdo->lastInsertId();

        $insertRootPost = $pdo->prepare(
            'INSERT INTO discussion_posts (discussion_id, user_id, body, is_root) VALUES (:discussion_id, :user_id, :body, 1)'
        );
        $insertRootPost->bindValue(':discussion_id', $discussionId, PDO::PARAM_INT);
        $insertRootPost->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $insertRootPost->bindValue(':body', $body, PDO::PARAM_STR);
        $insertRootPost->execute();

        $touchDiscussion = $pdo->prepare('UPDATE discussions SET updated_at = NOW() WHERE id = :id');
        $touchDiscussion->bindValue(':id', $discussionId, PDO::PARAM_INT);
        $touchDiscussion->execute();

        $pdo->commit();

        return [
            'success' => true,
            'errors' => [],
            'discussion_id' => $discussionId,
        ];
    } catch (Throwable $exception) {
        $pdo->rollBack();
        if (DISPLAY_ERRORS) {
            error_log($exception->getMessage());
        }

        return ['success' => false, 'errors' => ['general' => 'Une erreur est survenue.']];
    }
}

/**
 * Update the main message of a discussion.
 */
function update_discussion(int $discussionId, int $userId, array $input): array
{
    $title = trim($input['title'] ?? '');
    $category = trim($input['category'] ?? 'Général');
    $tagLine = trim($input['tag_line'] ?? '');
    $body = trim($input['body'] ?? '');
    $errors = [];

    $allowedCategories = ['Général', 'Stratégie', 'Collection', 'Compétitif', 'Événement'];

    if ($title === '') {
        $errors['title'] = 'Le titre est requis.';
    } elseif (mb_strlen($title) < 6) {
        $errors['title'] = 'Le titre doit comporter au moins 6 caractères.';
    }

    if (!in_array($category, $allowedCategories, true)) {
        $category = 'Général';
    }

    if ($tagLine !== '' && mb_strlen($tagLine) > 120) {
        $errors['tag_line'] = 'Le résumé doit contenir 120 caractères maximum.';
    }

    if ($body === '') {
        $errors['body'] = 'Le contenu est requis.';
    } elseif (mb_strlen($body) < 20) {
        $errors['body'] = 'Développe ta question en au moins 20 caractères.';
    }

    if ($errors !== []) {
        return ['success' => false, 'errors' => $errors];
    }

    $pdo = getDatabaseConnection();

    $ownershipStatement = $pdo->prepare(
        'SELECT user_id FROM discussions WHERE id = :id LIMIT 1'
    );
    $ownershipStatement->bindValue(':id', $discussionId, PDO::PARAM_INT);
    $ownershipStatement->execute();
    $discussion = $ownershipStatement->fetch();

    if (!$discussion || (int) $discussion['user_id'] !== $userId) {
        return ['success' => false, 'errors' => ['general' => 'Tu ne peux pas modifier cette discussion.']];
    }

    $pdo->beginTransaction();

    try {
        $updateDiscussion = $pdo->prepare(
            'UPDATE discussions SET title = :title, category = :category, tag_line = :tag_line, body = :body WHERE id = :id'
        );
        $updateDiscussion->bindValue(':title', $title, PDO::PARAM_STR);
        $updateDiscussion->bindValue(':category', $category, PDO::PARAM_STR);
        if ($tagLine === '') {
            $updateDiscussion->bindValue(':tag_line', null, PDO::PARAM_NULL);
        } else {
            $updateDiscussion->bindValue(':tag_line', $tagLine, PDO::PARAM_STR);
        }
        $updateDiscussion->bindValue(':body', $body, PDO::PARAM_STR);
        $updateDiscussion->bindValue(':id', $discussionId, PDO::PARAM_INT);
        $updateDiscussion->execute();

        $updateRootPost = $pdo->prepare(
            'UPDATE discussion_posts SET body = :body WHERE discussion_id = :discussion_id AND is_root = 1'
        );
        $updateRootPost->bindValue(':body', $body, PDO::PARAM_STR);
        $updateRootPost->bindValue(':discussion_id', $discussionId, PDO::PARAM_INT);
        $updateRootPost->execute();

        $pdo->commit();

        return ['success' => true, 'errors' => []];
    } catch (Throwable $exception) {
        $pdo->rollBack();
        if (DISPLAY_ERRORS) {
            error_log($exception->getMessage());
        }
        return ['success' => false, 'errors' => ['general' => 'Impossible de modifier la discussion.']];
    }
}

/**
 * Delete a discussion owned by the given user.
 */
function delete_discussion(int $discussionId, int $userId): bool
{
    $pdo = getDatabaseConnection();

    $statement = $pdo->prepare(
        'DELETE FROM discussions WHERE id = :id AND user_id = :user_id'
    );
    $statement->bindValue(':id', $discussionId, PDO::PARAM_INT);
    $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);

    $statement->execute();

    return $statement->rowCount() > 0;
}

/**
 * Create a reply inside a discussion.
 */
function create_post(int $discussionId, int $userId, string $content): array
{
    $body = trim($content);
    if ($body === '') {
        return ['success' => false, 'error' => 'Le message est requis.'];
    }
    if (mb_strlen($body) < 3) {
        return ['success' => false, 'error' => 'Le message doit contenir au moins 3 caractères.'];
    }

    $pdo = getDatabaseConnection();

    $discussion = fetch_discussion($discussionId);
    if ($discussion === null) {
        return ['success' => false, 'error' => 'Discussion introuvable.'];
    }

    $statement = $pdo->prepare(
        'INSERT INTO discussion_posts (discussion_id, user_id, body) VALUES (:discussion_id, :user_id, :body)'
    );
    $statement->bindValue(':discussion_id', $discussionId, PDO::PARAM_INT);
    $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $statement->bindValue(':body', $body, PDO::PARAM_STR);
    $statement->execute();

    $touch = $pdo->prepare('UPDATE discussions SET updated_at = NOW() WHERE id = :id');
    $touch->bindValue(':id', $discussionId, PDO::PARAM_INT);
    $touch->execute();

    return ['success' => true];
}

/**
 * Soft delete a post owned by the user.
 */
function delete_post(int $postId, int $userId): bool
{
    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare(
        'UPDATE discussion_posts SET is_deleted = 1 WHERE id = :id AND user_id = :user_id'
    );
    $statement->bindValue(':id', $postId, PDO::PARAM_INT);
    $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);

    return $statement->execute() && $statement->rowCount() > 0;
}

/**
 * Toggle like for a given post.
 */
function toggle_post_like(int $postId, int $userId): void
{
    $pdo = getDatabaseConnection();

    $check = $pdo->prepare(
        'SELECT 1 FROM post_likes WHERE post_id = :post_id AND user_id = :user_id LIMIT 1'
    );
    $check->bindValue(':post_id', $postId, PDO::PARAM_INT);
    $check->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $check->execute();

    if ($check->fetch()) {
        $delete = $pdo->prepare('DELETE FROM post_likes WHERE post_id = :post_id AND user_id = :user_id');
        $delete->bindValue(':post_id', $postId, PDO::PARAM_INT);
        $delete->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $delete->execute();

        return;
    }

    $insert = $pdo->prepare('INSERT INTO post_likes (post_id, user_id) VALUES (:post_id, :user_id)');
    $insert->bindValue(':post_id', $postId, PDO::PARAM_INT);
    $insert->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $insert->execute();
}

/**
 * Increment the view counter for the discussion, limited per session.
 */
function register_discussion_view(int $discussionId): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $lastView = $_SESSION['discussion_views'][$discussionId] ?? 0;
    $now = time();

    if ($now - $lastView < 3600) {
        return;
    }

    $_SESSION['discussion_views'][$discussionId] = $now;

    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare('UPDATE discussions SET views_count = views_count + 1 WHERE id = :id');
    $statement->bindValue(':id', $discussionId, PDO::PARAM_INT);
    $statement->execute();
}
