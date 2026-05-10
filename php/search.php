<?php
require_once 'db_connect.php';
 
$query = trim($_GET['q'] ?? '');
 
if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}
 
$stmt = $pdo->prepare("
    SELECT g.id, g.name, g.price, gi.filename //AS cover_image
    FROM Games g
    LEFT JOIN Game_Images gi ON gi.game_id = g.id AND gi.is_cover = 1
    WHERE g.name LIKE ?
    LIMIT 8
");
$stmt->execute(['%' . $query . '%']);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
header('Content-Type: application/json');
echo json_encode($results);
