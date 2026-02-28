<?php
require 'db.php';
session_start();
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $phone = $_POST['phone'] ?? '';
  $pass = $_POST['password'] ?? '';
  $stmt = $conn->prepare("SELECT id, password, name, verified FROM mechanics WHERE phone = ?");
  $stmt->bind_param("s", $phone);
  $stmt->execute();
  $res = $stmt->get_result();
  if($res->num_rows == 1){
    $row = $res->fetch_assoc();
    if(password_verify($pass, $row['password'])){
      if(!$row['verified']){
        die("Account not verified by admin yet.");
      }
      $_SESSION['mech_id'] = $row['id'];
      $_SESSION['mech_name'] = $row['name'];
      header("Location: mechanic_dashboard.php");
      exit;
    } else die("Invalid credentials");
  } else die("No account");
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Mechanic Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="p-3">
<div class="container">
  <h3>Mechanic Login</h3>
  <form method="post">
    <input name="phone" class="form-control mb-2" placeholder="Phone" required>
    <input name="password" type="password" class="form-control mb-2" placeholder="Password" required>
    <button class="btn btn-primary">Login</button>
  </form>
  <p class="mt-2">No account? <a href="mechanic_register.php">Register here</a></p>
</div>
</body></html>
