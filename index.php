<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>On-Road Mechanic Assistance</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

  <style>
    body {
      background: #f8f9fa;
    }

    #map { 
      height: 420px; 
      width: 100%; 
      border-radius: 10px;
    }

    /* Floating Chatbot Button */
    .chatbot-button {
      position: fixed;
      bottom: 25px;
      right: 25px;
      width: 65px;
      height: 65px;
      border-radius: 50%;
      background: #0d6efd;
      color: white;
      font-size: 30px;
      border: none;
      display: flex;
      justify-content: center;
      align-items: center;
      box-shadow: 0px 4px 10px rgba(0,0,0,0.3);
      cursor: pointer;
      z-index: 99999;
    }

    /* Popup Chat Window */
    .chat-popup {
      position: fixed;
      bottom: 100px;
      right: 25px;
      width: 370px;
      height: 520px;
      background: white;
      border-radius: 12px;
      box-shadow: 0px 4px 20px rgba(0,0,0,0.25);
      display: none;
      overflow: hidden;
      z-index: 9999;
    }

    .chat-header {
      background: #0d6efd;
      color: white;
      padding: 10px;
      text-align: center;
      font-weight: 600;
      font-size: 18px;
      position: relative;
    }

    .close-chat {
      position: absolute;
      right: 15px;
      top: 8px;
      font-size: 22px;
      cursor: pointer;
      color: white;
    }

    /* Dialogflow Messenger Styling */
    df-messenger {
      --df-messenger-bot-message: #0d6efd;
      --df-messenger-button-titlebar-color: #0d6efd;
      --df-messenger-chat-background-color: #ffffff;
      --df-messenger-font-color: #000000;
      --df-messenger-send-icon: #0d6efd;
      width: 100%;
      height: 100%;
    }
  </style>
</head>

<body class="p-4">

<div class="container">
  <h2 class="mb-3 text-primary">On-Road Mechanic Assistance</h2>

  <div class="row">

    <!-- LEFT SIDE -->
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body">

          <h5 class="mb-3">Request Help</h5>

          <form id="requestForm" action="customer_request.php" method="post">

            <div class="mb-2">
              <input name="name" placeholder="Your Name" class="form-control" required>
            </div>

            <div class="mb-2">
              <input name="phone" placeholder="Phone Number" class="form-control" required>
            </div>

            <div class="mb-2">
              <textarea name="problem" placeholder="Describe your vehicle problem" class="form-control" required></textarea>
            </div>

            <!-- Hidden GPS fields -->
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="lng" id="lng">

            <button class="btn btn-primary w-100" type="submit">Request Mechanic</button>

          </form>

          <hr>

          <h6>Status</h6>
          <div id="statusBox" class="text-muted">Submit request to track your mechanic.</div>

        </div>
      </div>
    </div>

    <!-- RIGHT SIDE -->
    <div class="col-md-7">
      <div id="map" class="shadow-sm"></div>
    </div>

  </div>
</div>

<!-- Floating Chat Button -->
<button class="chatbot-button" onclick="toggleChat()">💬</button>

<!-- Chat Popup Box -->
<div class="chat-popup" id="chatWindow">
  <div class="chat-header">
    RideOnGo Bot
    <span class="close-chat" onclick="toggleChat()">×</span>
  </div>

  <!-- Dialogflow Messenger Chatbot -->
  <df-messenger
      intent="WELCOME"
      chat-title="RideOnGoBot"
      agent-id="18f96ed3-fe8d-4ac0-bdcc-bce58ca20555"
      language-code="en">
  </df-messenger>
</div>

<!-- Dialogflow Messenger JS -->
<script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Map initialization
let map = L.map('map').setView([12.9716, 77.5946], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

let userMarker;

// Get Current GPS Location
if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition(pos => {
    const lat = pos.coords.latitude;
    const lng = pos.coords.longitude;

    document.getElementById('lat').value = lat;
    document.getElementById('lng').value = lng;

    map.setView([lat, lng], 15);

    userMarker = L.marker([lat, lng]).addTo(map)
      .bindPopup("Your Location")
      .openPopup();

  }, () => {
    alert("Could not get GPS location.");
  });
}

// Chat window toggle
function toggleChat() {
  const box = document.getElementById("chatWindow");
  box.style.display = box.style.display === "block" ? "none" : "block";
}
</script>

</body>
</html>
