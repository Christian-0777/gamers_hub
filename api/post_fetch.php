<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function getPostMediaImages(PDO $pdo, int $postId): array
{
    $stmt = $pdo->prepare('
        SELECT media_url
        FROM post_media
        WHERE post_id = :post_id AND media_type = \'image\'
        ORDER BY sort_order ASC, id ASC
    ');
    $stmt->execute(['post_id' => $postId]);

    return array_values(array_filter(array_map(static fn ($row) => $row['media_url'] ?? null, $stmt->fetchAll(PDO::FETCH_ASSOC))));
}

function getLatestPostComments(PDO $pdo, int $postId, int $limit = 2): array
{
    $stmt = $pdo->prepare('
        SELECT c.id, c.content, c.created_at, u.username, up.display_name, up.avatar_url
        FROM comments c
        INNER JOIN users u ON u.id = c.user_id
        LEFT JOIN user_profiles up ON up.user_id = u.id
        WHERE c.post_id = :post_id AND c.status = \'published\'
        ORDER BY c.created_at DESC
        LIMIT :limit
    ');
    $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (!function_exists('getUserPosts')) {
    function getUserPosts(PDO $pdo, int $userId, int $limit = 10): array
    {
        $stmt = $pdo->prepare('
            SELECT
                p.id,
                p.user_id,
                p.content,
                p.created_at,
                (
                    SELECT COUNT(*)
                    FROM post_reactions pr
                    WHERE pr.post_id = p.id AND pr.reaction_type = \'like\'
                ) AS like_count,
                (
                    SELECT COUNT(*)
                    FROM comments c
                    WHERE c.post_id = p.id AND c.status = \'published\'
                ) AS comment_count,
                (
                    SELECT COUNT(*)
                    FROM post_shares ps
                    WHERE ps.post_id = p.id
                ) AS share_count
            FROM posts p
            WHERE p.user_id = :user_id
              AND p.visibility = \'public\'
              AND p.status = \'published\'
            ORDER BY p.created_at DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($posts as $index => $post) {
            $posts[$index]['images'] = getPostMediaImages($pdo, (int) $post['id']);
            $posts[$index]['comments'] = getLatestPostComments($pdo, (int) $post['id']);
        }

        return $posts;
    }
}
