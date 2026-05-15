<?php
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$ids   = trim($_GET['ids'] ?? '');

// Return by IDs if provided
if ($ids) {
    $idArray = array_filter(array_map('intval', explode(',', $ids)));
    if (empty($idArray)) { echo json_encode([]); exit; }
    
    $placeholders = implode(',', array_fill(0, count($idArray), '?'));
    $stmt = $pdo->prepare("
        SELECT g.id, g.name, g.price, gi.filename AS cover_image
        FROM Games g
        LEFT JOIN Game_Images gi ON gi.game_id = g.id AND gi.is_cover = 1
        WHERE g.id IN ($placeholders)
    ");
    $stmt->execute($idArray);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

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
