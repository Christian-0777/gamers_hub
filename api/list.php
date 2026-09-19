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
						'SELECT DISTINCT cc.name
						 FROM company_catalog cc
						 INNER JOIN game_companies gc ON gc.company_id = cc.id AND gc.role = \'developer\'
						 INNER JOIN game_catalog g ON g.id = gc.game_id AND g.is_active = 1
						 WHERE cc.name LIKE :query
						 ORDER BY cc.name ASC
			 LIMIT ' . $limit
		);
		$statement->execute(['query' => '%' . $query . '%']);
		$results = array_map(
			static fn (array $row): array => ['name' => $row['name']],
			$statement->fetchAll()
		);
	} elseif ($type === 'games') {
		$statement = db()->prepare(
						'SELECT g.id, g.name,
										GROUP_CONCAT(DISTINCT cc.name ORDER BY cc.name SEPARATOR \', \') AS developer
						 FROM game_catalog g
						 LEFT JOIN game_companies gc ON gc.game_id = g.id AND gc.role = \'developer\'
						 LEFT JOIN company_catalog cc ON cc.id = gc.company_id
			 WHERE is_active = 1
							 AND (:query = \'\' OR g.name LIKE :name_query OR cc.name LIKE :developer_query)
						 GROUP BY g.id, g.name
						 ORDER BY CASE WHEN :exact_query <> \'\' AND LOWER(g.name) = LOWER(:exact_name) THEN 0 ELSE 1 END, g.name ASC
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
