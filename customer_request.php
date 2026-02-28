<?php
require 'db.php';

$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$problem = $_POST['problem'] ?? '';
$lat = floatval($_POST['lat'] ?? 0);
$lng = floatval($_POST['lng'] ?? 0);

if(!$name || !$phone || !$problem){
  die("Missing fields. Go back.");
}

// Show user's location (for debugging)
echo "<h3>Your Location</h3>";
echo "<p><strong>Latitude:</strong> $lat<br><strong>Longitude:</strong> $lng</p><hr>";

// 1) Insert customer
$stmt = $conn->prepare("INSERT INTO customers (name, phone) VALUES (?, ?)");
$stmt->bind_param("ss", $name, $phone);
$stmt->execute();
$customer_id = $stmt->insert_id;
$stmt->close();

// 2) Insert service request
$stmt = $conn->prepare("INSERT INTO requests (customer_id, problem_text, lat, lng) 
                        VALUES (?, ?, ?, ?)");
$stmt->bind_param("isdd", $customer_id, $problem, $lat, $lng);
$stmt->execute();
$request_id = $stmt->insert_id;
$stmt->close();

// Reset all mechanics to available (for demo/testing)
$conn->query("UPDATE mechanics SET available = 1 WHERE verified = 1");

// 3) Find nearest verified mechanic (no distance limit)
$q = "SELECT id, name, lat, lng,
(6371 * acos(
    cos(radians(?)) * cos(radians(lat)) *
    cos(radians(lng) - radians(?)) + 
    sin(radians(?)) * sin(radians(lat))
)) AS distance
FROM mechanics
WHERE verified = 1
ORDER BY distance ASC
LIMIT 1";

$stmt = $conn->prepare($q);
$stmt->bind_param("ddd", $lat, $lng, $lat);
$stmt->execute();
$res = $stmt->get_result();

echo "<h3>Request Details</h3>";
echo "<p><strong>Request ID:</strong> $request_id</p>";

if($res->num_rows > 0){
    $row = $res->fetch_assoc();
    $mech_id = $row['id'];

    // Assign mechanic
    $stmt2 = $conn->prepare("UPDATE requests SET mechanic_id = ?, status='assigned' WHERE id = ?");
    $stmt2->bind_param("ii", $mech_id, $request_id);
    $stmt2->execute();
    $stmt2->close();

    // Set mechanic busy
    $stmt3 = $conn->prepare("UPDATE mechanics SET available = 0 WHERE id = ?");
    $stmt3->bind_param("i", $mech_id);
    $stmt3->execute();
    $stmt3->close();

    echo "<h4><span style='color:green;'>Mechanic Assigned Successfully!</span></h4>";
    echo "<p><strong>Mechanic Name:</strong> " . htmlspecialchars($row['name']) . "</p>";
    echo "<p><strong>Distance:</strong> " . round($row['distance'], 2) . " km</p>";
    echo "<p><a class='btn btn-primary' href='track_page.php?request_id=$request_id'>
            Click here to track your mechanic
          </a></p>";

} else {
    echo "<h4 style='color:red;'>No mechanics found!</h4>";
    echo "<p>You can share Request ID with admin for manual help.</p>";
}
?>
