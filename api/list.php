<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
	$type = (string) ($_GET['type'] ?? 'games');
	$query = trim((string) ($_GET['q'] ?? ''));
	$limit = $query === '' ? 8 : 20;

	if ($type === 'developers') {
		$statement = db()->prepare(
			'SELECT DISTINCT developer
			 FROM game_catalog
			 WHERE is_active = 1
			   AND developer IS NOT NULL
			   AND developer <> \'\'
			   AND developer LIKE :query
			 ORDER BY developer ASC
			 LIMIT ' . $limit
		);
		$statement->execute(['query' => '%' . $query . '%']);
		$results = array_map(
			static fn (array $row): array => ['name' => $row['developer']],
			$statement->fetchAll()
		);
	} elseif ($type === 'games') {
		$statement = db()->prepare(
			'SELECT id, name, developer
			 FROM game_catalog
			 WHERE is_active = 1
			   AND (:query = \'\' OR name LIKE :name_query OR developer LIKE :developer_query)
			 ORDER BY CASE WHEN :exact_query <> \'\' AND LOWER(name) = LOWER(:exact_name) THEN 0 ELSE 1 END, name ASC
			 LIMIT ' . $limit
		);
		$statement->execute([
			'query' => $query,
			'name_query' => '%' . $query . '%',
			'developer_query' => '%' . $query . '%',
			'exact_query' => $query,
			'exact_name' => $query,
		]);
		$results = $statement->fetchAll();
	} else {
		http_response_code(400);
		echo json_encode(['success' => false, 'error' => 'Invalid list type']);
		exit;
	}

	echo json_encode([
		'success' => true,
		'type' => $type,
		'results' => $results,
	], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
	error_log($exception->getMessage());
	http_response_code(500);
	echo json_encode(['success' => false, 'error' => 'Unable to load results']);
}
