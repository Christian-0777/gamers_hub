<?php
/**
 * Database Configuration
 * Gamers Hub Platform
 */

require_once __DIR__ . '/../config/db.php';

// Helper function to get user by username
function getUserByUsername($pdo, $username) {
    $stmt = $pdo->prepare('
        SELECT 
            u.id,
            u.username,
            u.status,
            up.display_name,
            up.bio,
            up.avatar_url,
            up.cover_url,
            up.location,
            up.gaming_style,
            up.preferred_voice_chat,
            up.online_status
        FROM users u
        JOIN user_profiles up ON u.id = up.user_id
        WHERE u.username = ? AND u.status = "active"
        LIMIT 1
    ');
    $stmt->execute([$username]);
    return $stmt->fetch();
}

// Helper function to get user statistics
function getUserStats($pdo, $user_id) {
    $followers = $pdo->prepare('SELECT COUNT(*) as count FROM followers WHERE following_id = ?');
    $followers->execute([$user_id]);
    $followerCount = $followers->fetch()['count'];

    $following = $pdo->prepare('SELECT COUNT(*) as count FROM followers WHERE follower_id = ?');
    $following->execute([$user_id]);
    $followingCount = $following->fetch()['count'];

    return [
        'friends' => 0,
        'followers' => $followerCount,
        'following' => $followingCount
    ];
}

// Helper function to get user games
function getUserGames($pdo, $user_id, $limit = 3) {
    $stmt = $pdo->prepare('
        SELECT 
            g.id,
            g.name,
            g.icon_url,
            ug.rank_name,
            ug.skill_level,
            ug.hours_played,
            ug.status
        FROM user_games ug
        JOIN games g ON ug.game_id = g.id
        WHERE ug.user_id = ? AND ug.status IN ("playing", "favorite")
        ORDER BY ug.updated_at DESC
        LIMIT ?
    ');
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

// Helper function to get user gamer type
function getUserGamerType($pdo, $user_id) {
    $stmt = $pdo->prepare('SELECT gamer_type FROM user_gamer_types WHERE user_id = ?');
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Helper function to get user platforms
function getUserPlatforms($pdo, $user_id) {
    $stmt = $pdo->prepare('SELECT platform FROM user_platforms WHERE user_id = ?');
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Helper function to get user friends
function getUserFriends($pdo, $user_id, $limit = 3) {
    return [];
}

// Helper function to get user posts
if (!function_exists('getUserPosts')) {
    function getUserPosts($pdo, $user_id, $limit = 10) {
        $stmt = $pdo->prepare('
            SELECT 
                p.id,
                p.content,
                p.created_at,
                pm.image_url,
                (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND reaction_type = "like") as like_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count,
                (SELECT COUNT(*) FROM post_shares WHERE post_id = p.id) as share_count
            FROM posts p
            LEFT JOIN (
                SELECT post_id, MIN(media_url) AS image_url
                FROM post_media
                WHERE media_type = "image"
                GROUP BY post_id
            ) pm ON pm.post_id = p.id
            WHERE p.user_id = ? AND p.visibility = "public" AND p.status = "published"
            ORDER BY p.created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$user_id, $limit]);
        return $stmt->fetchAll();
    }
}
?>
