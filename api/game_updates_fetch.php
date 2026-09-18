<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/env.php';

const SCS_FEED_URL = 'https://blog.scssoft.com/feeds/posts/default?alt=rss';
const FETCH_INTERVAL_MINUTES = 1;

function fetchFeed(string $url): string
{
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'GamersHUB Game Updates/1.0',
        CURLOPT_HTTPHEADER => ['Accept: application/rss+xml, application/xml, text/xml'],
    ]);
    $body = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if ($body === false || $status < 200 || $status >= 300) {
        throw new RuntimeException($error !== '' ? $error : 'The feed returned HTTP ' . $status . '.');
    }

    return $body;
}

function feedText(SimpleXMLElement $item): string
{
    $description = (string) ($item->description ?? '');
    return trim((string) $item->title . ' ' . strip_tags(html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}

function updateType(string $text): string
{
    $lower = strtolower($text);
    if (str_contains($lower, 'open beta')) return 'open_beta';
    if (str_contains($lower, 'experimental beta')) return 'experimental_beta';
    if (str_contains($lower, 'dlc')) return 'dlc';
    if (preg_match('/\b(update|patch|hotfix|version)\b/i', $text)) return 'patch';
    if (preg_match('/\b(release|released|launch)\b/i', $text)) return 'release';
    return 'news';
}

function publishedAt(SimpleXMLElement $item): ?string
{
    $value = trim((string) ($item->pubDate ?? $item->published ?? ''));
    if ($value === '') return null;
    $timestamp = strtotime($value);
    return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
}

function parseValorantUpdates(string $html, string $baseUrl): array
{
    $document = new DOMDocument();
    @$document->loadHTML($html);
    $xpath = new DOMXPath($document);
    $items = [];
    $seen = [];

    foreach ($xpath->query('//a[contains(@href, "/en-us/news/game-updates/")]') as $link) {
        $href = trim((string) $link->getAttribute('href'));
        $title = trim(preg_replace('/\s+/', ' ', $link->textContent));
        if ($href === '' || $title === '' || $href === '/en-us/news/game-updates/' || isset($seen[$href])) continue;
        $seen[$href] = true;
        if (!str_starts_with($href, 'http')) $href = 'https://playvalorant.com' . $href;
        $items[] = [
            'title' => $title,
            'description' => null,
            'link' => $href,
            'guid' => $href,
            'published_at' => null,
        ];
    }

    return $items;
}

function runFetcher(PDO $database, bool $force = false): array
{
    $sourceStatement = $database->query(
                "SELECT s.id, s.game_id, s.source_name, s.source_type, s.feed_url, g.name AS game_name, g.slug
         FROM game_update_sources s
         INNER JOIN game_catalog g ON g.id = s.game_id
                 WHERE s.enabled = 1 AND g.is_active = 1"
    );
    $sources = $sourceStatement->fetchAll();
    $summary = ['sources' => 0, 'skipped' => 0, 'inserted' => 0, 'duplicates' => 0, 'errors' => []];
    $feeds = [];

    foreach ($sources as $source) {
        $summary['sources']++;
        if (!$force && !empty($source['last_checked_at']) && strtotime((string) $source['last_checked_at']) > time() - (FETCH_INTERVAL_MINUTES * 60)) {
            $summary['skipped']++;
            continue;
        }
        $feeds[$source['feed_url']][] = $source;
    }

    foreach ($feeds as $feedUrl => $feedSources) {
        $checkedAt = date('Y-m-d H:i:s');
        try {
            $sourceType = (string) ($feedSources[0]['source_type'] ?? 'rss');
            if ($sourceType === 'html') {
                $items = parseValorantUpdates(fetchFeed($feedUrl), $feedUrl);
            } else {
                $xml = @simplexml_load_string(fetchFeed($feedUrl));
                if (!$xml || !isset($xml->channel->item)) throw new RuntimeException('Invalid RSS feed.');
                $items = array_map(static function (SimpleXMLElement $item): array {
                    return [
                        'title' => trim((string) $item->title),
                        'description' => trim((string) ($item->description ?? '')) ?: null,
                        'link' => trim((string) ($item->link ?? '')),
                        'guid' => trim((string) ($item->guid ?? '')),
                        'published_at' => publishedAt($item),
                    ];
                }, iterator_to_array($xml->channel->item));
            }
            $database->beginTransaction();

            foreach ($feedSources as $source) {
                $database->prepare('UPDATE game_update_sources SET last_checked_at = :checked_at WHERE id = :id')
                    ->execute(['checked_at' => $checkedAt, 'id' => $source['id']]);
            }

            $insert = $database->prepare(
                'INSERT INTO game_updates
                    (game_id, source_id, version, update_type, title, description, source_url, source_guid, source_hash, published_at)
                 VALUES
                    (:game_id, :source_id, :version, :update_type, :title, :description, :source_url, :source_guid, :source_hash, :published_at)'
            );
            $exists = $database->prepare('SELECT id FROM game_updates WHERE source_hash = :source_hash LIMIT 1');
            $successAt = date('Y-m-d H:i:s');

            foreach ($items as $item) {
                $title = (string) $item['title'];
                $text = trim($title . ' ' . strip_tags((string) ($item['description'] ?? '')));
                $link = (string) $item['link'];
                $guid = (string) $item['guid'];
                $identity = $guid !== '' ? $guid : ($link !== '' ? $link : $title);
                $hash = hash('sha256', $identity);
                $exists->execute(['source_hash' => $hash]);
                if ($exists->fetchColumn()) {
                    $summary['duplicates']++;
                    continue;
                }

                foreach ($feedSources as $source) {
                    $gameName = strtolower((string) $source['game_name']);
                    $gameMatches = $source['source_type'] === 'html' && $source['slug'] === 'valorant'
                        || str_contains(strtolower($text), 'euro truck simulator 2') && $gameName === 'euro truck simulator 2'
                        || str_contains($text, 'american truck simulator') && $gameName === 'american truck simulator';
                    if (!$gameMatches) continue;
                    $insert->execute([
                        'game_id' => $source['game_id'],
                        'source_id' => $source['id'],
                        'version' => null,
                        'update_type' => updateType($text),
                        'title' => $title !== '' ? $title : 'SCS Software update',
                        'description' => trim((string) ($item['description'] ?? '')) ?: null,
                        'source_url' => $link !== '' ? $link : $feedUrl,
                        'source_guid' => $guid !== '' ? $guid : null,
                        'source_hash' => $hash,
                        'published_at' => $item['published_at'],
                    ]);
                    $summary['inserted']++;
                    break;
                }
            }

            foreach ($feedSources as $source) {
                $database->prepare('UPDATE game_update_sources SET last_success_at = :success_at WHERE id = :id')
                    ->execute(['success_at' => $successAt, 'id' => $source['id']]);
            }
            $database->commit();
        } catch (Throwable $exception) {
            if ($database->inTransaction()) $database->rollBack();
            $summary['errors'][] = $exception->getMessage();
        }
    }

    return $summary;
}

$isCli = PHP_SAPI === 'cli';
$token = (string) ($_SERVER['HTTP_X_GAMERSHUB_FETCH_TOKEN'] ?? $_GET['token'] ?? '');
$expectedToken = (string) env('GAME_UPDATES_FETCH_TOKEN', '');
$authorized = $isCli || ($expectedToken !== '' && hash_equals($expectedToken, $token));
if (!$authorized) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

try {
    $force = isset($_GET['force']) && $_GET['force'] === '1';
    $summary = runFetcher(db(), $force);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => empty($summary['errors']), 'summary' => $summary], JSON_UNESCAPED_SLASHES);
} catch (Throwable $exception) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => $exception->getMessage()]);
}
