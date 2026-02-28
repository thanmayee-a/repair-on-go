<?php
require 'db.php';
session_start();
if(!isset($_SESSION['mech_id'])) die("Login required");
$mech_id = $_SESSION['mech_id'];
$request_id = intval($_POST['request_id'] ?? 0);
$action = $_POST['action'] ?? '';

if($action == 'accept'){
    $stmt = $conn->prepare("UPDATE requests SET status='onroute', mechanic_id=? WHERE id=?");
    $stmt->bind_param("ii", $mech_id, $request_id);
    $stmt->execute();
    $stmt2 = $conn->prepare("UPDATE mechanics SET available=0 WHERE id=?");
    $stmt2->bind_param("i", $mech_id);
    $stmt2->execute();
    echo "Accepted. <a href='mechanic_dashboard.php'>Back</a>";
} elseif($action == 'complete'){
    $stmt = $conn->prepare("UPDATE requests SET status='completed' WHERE id=?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt2 = $conn->prepare("UPDATE mechanics SET available=1 WHERE id=?");
    $stmt2->bind_param("i", $mech_id);
    $stmt2->execute();
    echo "Marked completed. <a href='mechanic_dashboard.php'>Back</a>";
} else {
    echo "Unknown action.";
}
