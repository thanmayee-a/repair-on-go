<?php
require 'db.php';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $name = $_POST['name'] ?? '';
  $phone = $_POST['phone'] ?? '';
  $email = $_POST['email'] ?? '';
  $pass = $_POST['password'] ?? '';
  $lat = floatval($_POST['lat'] ?? 0);
  $lng = floatval($_POST['lng'] ?? 0);

  if(!$name || !$phone || !$pass){ die("Missing fields"); }

  $hash = password_hash($pass, PASSWORD_DEFAULT);
  $stmt = $conn->prepare("INSERT INTO mechanics (name, phone, email, password, lat, lng, verified, available) VALUES (?, ?, ?, ?, ?, ?, 0, 1)");
  $stmt->bind_param("sssd d", $name, $phone, $email, $hash, $lat, $lng); // note spacing issue fixed below
  // Correction for bind_param types: s s s s d d
  $stmt = $conn->prepare("INSERT INTO mechanics (name, phone, email, password, lat, lng, verified, available) VALUES (?, ?, ?, ?, ?, ?, 0, 1)");
  $stmt->bind_param("ssssdd", $name, $phone, $email, $hash, $lat, $lng);
  $stmt->execute();
  echo "Registered. Await admin verification.";
  exit;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Mechanic Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="p-3">
<div class="container">
  <h3>Mechanic Registration</h3>
  <form method="post">
    <input name="name" class="form-control mb-2" placeholder="Name" required>
    <input name="phone" class="form-control mb-2" placeholder="Phone" required>
    <input name="email" class="form-control mb-2" placeholder="Email">
    <input name="password" class="form-control mb-2" placeholder="Password" required>
    <input name="lat" class="form-control mb-2" placeholder="Lat (optional)">
    <input name="lng" class="form-control mb-2" placeholder="Lng (optional)">
    <button class="btn btn-primary">Register</button>
  </form>
</div>
</body>
</html>
