<?php
require 'db.php';
header('Content-Type: application/json');
$lat = floatval($_GET['lat'] ?? 0);
$lng = floatval($_GET['lng'] ?? 0);
$radius_km = floatval($_GET['radius'] ?? 5);

$q = "SELECT id, name, phone, lat, lng,
( 6371 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance
FROM mechanics
WHERE verified = 1 AND available = 1
HAVING distance <= ?
ORDER BY distance";

$stmt = $conn->prepare($q);
$stmt->bind_param("dddi",$lat,$lng,$lat,$radius_km);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while($r = $res->fetch_assoc()) $out[] = $r;
echo json_encode($out);
