<?php
require 'db.php';
if($_SERVER['REQUEST_METHOD']=='POST'){
  if(isset($_POST['verify'])){
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("UPDATE mechanics SET verified=1 WHERE id=?");
    $stmt->bind_param("i",$id); $stmt->execute();
  } elseif(isset($_POST['unverify'])){
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("UPDATE mechanics SET verified=0 WHERE id=?");
    $stmt->bind_param("i",$id); $stmt->execute();
  }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="p-3">
<div class="container">
  <h3>Admin - Verify Mechanics</h3>
  <table class="table">
    <thead><tr><th>Name</th><th>Phone</th><th>Verified</th><th>Action</th></tr></thead>
    <tbody>
    <?php
      $res = $conn->query("SELECT * FROM mechanics ORDER BY created_at DESC");
      while($r = $res->fetch_assoc()){
        echo "<tr>";
        echo "<td>".htmlspecialchars($r['name'])."</td>";
        echo "<td>".htmlspecialchars($r['phone'])."</td>";
        echo "<td>".($r['verified']? "Yes":"No")."</td>";
        echo "<td><form method='post' style='display:inline'>";
        echo "<input type='hidden' name='id' value='".$r['id']."'>";
        if(!$r['verified']) echo "<button name='verify' class='btn btn-sm btn-success'>Verify</button>";
        else echo "<button name='unverify' class='btn btn-sm btn-warning'>Unverify</button>";
        echo "</form></td>";
        echo "</tr>";
      }
    ?>
    </tbody>
  </table>
</div>
</body></html>
