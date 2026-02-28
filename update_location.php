<?php
require 'db.php';
session_start();
if(!isset($_SESSION['mech_id'])) exit;
$mech_id = $_SESSION['mech_id'];
$lat = floatval($_POST['lat'] ?? 0);
$lng = floatval($_POST['lng'] ?? 0);
$stmt = $conn->prepare("UPDATE mechanics SET lat=?, lng=? WHERE id=?");
$stmt->bind_param("ddi", $lat, $lng, $mech_id);
$stmt->execute();
echo "Location updated. <a href='mechanic_dashboard.php'>Back</a>";
