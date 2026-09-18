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
            up.social_discord,
            up.social_tiktok,
            up.social_facebook,
            up.social_twitch,
            up.social_kick,
            up.social_github,
            up.social_spotify,
            up.social_youtube,
            up.social_apple_music,
            up.social_steam,
            up.social_x,
            up.avatar_url,
            up.cover_url,
            up.location,
            up.gaming_style,
            up.preferred_voice_chat,
            up.online_status
        FROM users u
        JOIN user_profiles up ON u.id = up.user_id
        WHERE u.username = ? AND u.status = \'active\'
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

    $friends = $pdo->prepare(
        'SELECT COUNT(*) AS count
         FROM followers f1
         INNER JOIN followers f2
            ON f2.follower_id = f1.following_id
           AND f2.following_id = f1.follower_id
         WHERE f1.follower_id = ?'
    );
    $friends->execute([$user_id]);
    $friendCount = (int) $friends->fetch()['count'];

    return [
        'friends' => $friendCount,
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
        JOIN game_catalog g ON ug.game_id = g.id
        WHERE ug.user_id = ? AND ug.status IN (\'playing\', \'favorite\')
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
                (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND reaction_type = \'like\') as like_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count,
                (SELECT COUNT(*) FROM post_shares WHERE post_id = p.id) as share_count
            FROM posts p
            WHERE p.user_id = ? AND p.visibility = \'public\' AND p.status = \'published\'
            ORDER BY p.created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$user_id, $limit]);
        $posts = $stmt->fetchAll();

        $mediaStatement = $pdo->prepare(
            'SELECT media_url
             FROM post_media
             WHERE post_id = :post_id AND media_type = \'image\'
             ORDER BY sort_order ASC, id ASC
             LIMIT 4'
        );

        $commentsStatement = $pdo->prepare(
            'SELECT
                c.content,
                u.username,
                up.display_name,
                up.avatar_url
             FROM comments c
             INNER JOIN users u ON u.id = c.user_id
             INNER JOIN user_profiles up ON up.user_id = u.id
             WHERE c.post_id = :post_id AND c.status = \'published\'
             ORDER BY c.created_at ASC
             LIMIT 2'
        );

        foreach ($posts as &$post) {
            $mediaStatement->execute(['post_id' => $post['id']]);
            $post['images'] = $mediaStatement->fetchAll(PDO::FETCH_COLUMN);

            $commentsStatement->execute(['post_id' => $post['id']]);
            $post['comments'] = $commentsStatement->fetchAll();
        }
        unset($post);

        return $posts;
    }
}
?>
