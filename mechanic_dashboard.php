<?php
// mechanic_dashboard.php
require 'db.php';
session_start();
if(!isset($_SESSION['mech_id'])){ header("Location: mechanic_login.php"); exit; }
$mech_id = $_SESSION['mech_id'];
$mech_name = $_SESSION['mech_name'] ?? 'Mechanic';
?>
<!doctype html><html><head><meta charset="utf-8"><title>Mechanic Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="p-3">
<div class="container">
  <h3>Welcome, <?php echo htmlspecialchars($mech_name); ?></h3>
  <p><a href="logout_mech.php" class="btn btn-sm btn-secondary">Logout</a></p>
  <h5>Assigned Requests</h5>
  <?php
    $stmt = $conn->prepare("SELECT * FROM requests WHERE mechanic_id = ? AND status IN ('assigned','onroute')");
    $stmt->bind_param("i", $mech_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows==0) echo "<p>No active requests</p>";
    else {
      while($r = $res->fetch_assoc()){
        echo "<div class='card mb-2'><div class='card-body'>";
        echo "<b>Request #".$r['id']."</b><br>";
        echo "Problem: ".htmlspecialchars($r['problem_text'])."<br>";
        echo "Status: ".$r['status']."<br>";
        echo "<form method='post' action='mechanic_accept.php' class='mt-2'>";
        echo "<input type='hidden' name='request_id' value='".$r['id']."'>";
        echo "<button name='action' value='accept' class='btn btn-success btn-sm'>Accept & On Route</button> ";
        echo "<button name='action' value='complete' class='btn btn-primary btn-sm'>Mark Completed</button>";
        echo "</form>";
        echo "</div></div>";
      }
    }
  ?>
  <h5 class="mt-4">Update Location (manual)</h5>
  <form method="post" action="update_location.php">
    <input type="text" name="lat" class="form-control mb-2" placeholder="Latitude">
    <input type="text" name="lng" class="form-control mb-2" placeholder="Longitude">
    <button class="btn btn-sm btn-outline-primary">Update Location</button>
  </form>
</div>
</body></html>
