<?php
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

// Return empty array if query is empty
if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT 
            g.id,
            g.name,
            g.price,
            gi.filename AS cover_image
        FROM Games g
        LEFT JOIN Game_Images gi 
            ON gi.game_id = g.id 
            AND gi.is_cover = 1
        WHERE g.name LIKE ?
        LIMIT 8
    ");

    $stmt->execute(["%$query%"]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);

} catch (PDOException $e) {

    echo json_encode([
        'error' => 'Database error'
    ]);
}
?>
