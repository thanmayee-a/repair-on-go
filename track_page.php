 <?php
$request_id = $_GET['request_id'] ?? 0;
if (!$request_id) {
    die("Missing request ID.");
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Track Request #<?php echo $request_id; ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

  <style>
    #map { height: 500px; width: 100%; }
  </style>
</head>

<body class="p-3">

<h3>Tracking Request #<?php echo $request_id; ?></h3>
<p id="statusBox">Loading tracking data...</p>

<div id="map"></div>

<br>
<a href="index.php" class="btn btn-outline-primary btn-sm">Back Home</a>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Initialize map
const map = L.map('map').setView([12.9716, 77.5946], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom:19
}).addTo(map);

let customerMarker = null;
let mechanicMarker = null;
let routeLine = null;

// Fetch tracking info every 4 seconds
function poll() {
  fetch("track_status.php?request_id=<?php echo $request_id; ?>")
    .then(r => r.json())
    .then(data => {

      if (!data || data.error) {
        document.getElementById("statusBox").innerText = "No tracking data found.";
        return;
      }

      // Update status
      document.getElementById("statusBox").innerText =
        `Status: ${data.status} | Mechanic: ${data.mech_name ?? 'Not assigned yet'}`;

      // --- Customer Marker ---
      if (data.lat && data.lng) {
        let cPos = [parseFloat(data.lat), parseFloat(data.lng)];

        if (!customerMarker) {
          customerMarker = L.marker(cPos, {
            icon: L.icon({
              iconUrl: "https://cdn-icons-png.flaticon.com/512/684/684908.png",
              iconSize: [40, 40],
              iconAnchor: [20, 40]
            })
          }).addTo(map).bindPopup("Customer");
        } else {
          customerMarker.setLatLng(cPos);
        }
      }

      // --- Mechanic Marker ---
      if (data.mech_lat && data.mech_lng) {
        let mPos = [parseFloat(data.mech_lat), parseFloat(data.mech_lng)];

        if (!mechanicMarker) {
          mechanicMarker = L.marker(mPos, {
            icon: L.icon({
              iconUrl: "https://cdn-icons-png.flaticon.com/512/3081/3081972.png",
              iconSize: [40, 40],
              iconAnchor: [20, 40]
            })
          }).addTo(map).bindPopup("Mechanic");
        } else {
          mechanicMarker.setLatLng(mPos);
        }

        // Draw line when both markers exist
        if (customerMarker) {
          let points = [
            customerMarker.getLatLng(),
            mechanicMarker.getLatLng()
          ];

          if (!routeLine) {
            routeLine = L.polyline(points, {color:"blue", weight: 4}).addTo(map);
          } else {
            routeLine.setLatLngs(points);
          }

          map.fitBounds(points, { padding: [50,50] });
        }
      }
    })
    .catch(err => {
      console.log("Error:", err);
    });

  setTimeout(poll, 4000);
}

poll();
</script>

</body>
</html>

