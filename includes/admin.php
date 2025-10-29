<?php
/**
 * Administration helper functions for CaraTemple.
 *
 * Provides aggregated statistics and moderation helpers for admin dashboard actions.
 *
 * @package CaraTemple\Includes
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

/**
 * Retrieve aggregated counts for the admin dashboard.
 *
 * @return array{users:int, discussions:int, posts:int, likes:int}
 */
function fetch_admin_dashboard_stats(): array
{
    $pdo = getDatabaseConnection();

    $users = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $discussions = (int) $pdo->query('SELECT COUNT(*) FROM discussions')->fetchColumn();
    $posts = (int) $pdo->query('SELECT COUNT(*) FROM discussion_posts WHERE is_deleted = 0')->fetchColumn();
    $likes = (int) $pdo->query('SELECT COUNT(*) FROM post_likes')->fetchColumn();

    return [
        'users' => $users,
        'discussions' => $discussions,
        'posts' => $posts,
        'likes' => $likes,
    ];
}

/**
 * Fetch the most recent registered users.
 *
 * @return array<int, array<string, mixed>>
 */
function fetch_admin_recent_users(int $limit = 8): array
{
    $pdo = getDatabaseConnection();

    $statement = $pdo->prepare(
        'SELECT u.id, u.username, u.email, u.is_admin, u.created_at,
                (
                    SELECT COUNT(*) FROM discussions d WHERE d.user_id = u.id
                ) AS discussions_count,
                (
                    SELECT COUNT(*) FROM discussion_posts p WHERE p.user_id = u.id AND p.is_deleted = 0
                ) AS posts_count
         FROM users u
         ORDER BY u.created_at DESC
         LIMIT :limit'
    );
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();

    return $statement->fetchAll();
}

/**
 * Fetch the latest messages (posts) submitted on the forum.
 *
 * @return array<int, array<string, mixed>>
 */
function fetch_admin_recent_posts(int $limit = 10): array
{
    $pdo = getDatabaseConnection();

    $statement = $pdo->prepare(
        'SELECT p.id, p.discussion_id, p.body, p.is_root, p.is_deleted, p.created_at,
                u.username, d.title
         FROM discussion_posts p
         INNER JOIN users u ON u.id = p.user_id
         INNER JOIN discussions d ON d.id = p.discussion_id
         ORDER BY p.created_at DESC
         LIMIT :limit'
    );
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();

    return $statement->fetchAll();
}

/**
 * Fetch recent discussions for admin listing.
 *
 * @return array<int, array<string, mixed>>
 */
function fetch_admin_recent_discussions(int $limit = 6): array
{
    $pdo = getDatabaseConnection();

    $statement = $pdo->prepare(
        'SELECT d.id, d.title, d.category, d.created_at, d.views_count,
                u.username,
                (
                    SELECT COUNT(*) FROM discussion_posts p WHERE p.discussion_id = d.id AND p.is_deleted = 0
                ) AS posts_count
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
 * Permanently remove a user account.
 */
function admin_delete_user(int $userId): bool
{
    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $statement->bindValue(':id', $userId, PDO::PARAM_INT);

    return $statement->execute() && $statement->rowCount() > 0;
}

/**
 * Soft delete a forum post regardless of ownership.
 */
function admin_delete_post(int $postId): bool
{
    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare('UPDATE discussion_posts SET is_deleted = 1 WHERE id = :id');
    $statement->bindValue(':id', $postId, PDO::PARAM_INT);

    return $statement->execute() && $statement->rowCount() > 0;
}

/**
 * Permanently delete a discussion thread.
 */
function admin_delete_discussion(int $discussionId): bool
{
    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare('DELETE FROM discussions WHERE id = :id');
    $statement->bindValue(':id', $discussionId, PDO::PARAM_INT);

    return $statement->execute() && $statement->rowCount() > 0;
}

/**
 * Count how many administrator accounts exist.
 */
function admin_count_admins(): int
{
    $pdo = getDatabaseConnection();

    return (int) $pdo->query('SELECT COUNT(*) FROM users WHERE is_admin = 1')->fetchColumn();
}

/**
 * Retrieve a user record by identifier.
 *
 * @return array<string, mixed>|null
 */
function admin_find_user(int $userId): ?array
{
    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare('SELECT id, username, email, is_admin FROM users WHERE id = :id LIMIT 1');
    $statement->bindValue(':id', $userId, PDO::PARAM_INT);
    $statement->execute();

    $user = $statement->fetch();

    return $user !== false ? $user : null;
}

/**
 * Retrieve a post record for moderation checks.
 *
 * @return array<string, mixed>|null
 */
function admin_find_post(int $postId): ?array
{
    $pdo = getDatabaseConnection();
    $statement = $pdo->prepare('SELECT id, discussion_id, is_root, is_deleted FROM discussion_posts WHERE id = :id LIMIT 1');
    $statement->bindValue(':id', $postId, PDO::PARAM_INT);
    $statement->execute();

    $post = $statement->fetch();

    return $post !== false ? $post : null;
}
