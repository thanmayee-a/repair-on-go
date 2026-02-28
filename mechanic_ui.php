<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>On-Road Mechanic Assistance</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

  <style>
    body {
      background: #f4f0fa; /* light lavender background */
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    h2 {
      font-weight: 700;
      color: #7b5fc5; /* deep lavender */
      text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
    }

    #map { 
      height: 450px; 
      width: 100%; 
      border-radius: 15px;
      border: 2px solid #a186f0;
      box-shadow: 0px 5px 20px rgba(0,0,0,0.15);
    }

    .card {
      border-radius: 15px;
      border: none;
      box-shadow: 0 6px 18px rgba(0,0,0,0.1);
      background: linear-gradient(135deg, #f8f5fc, #e5dffb);
    }

    .card h5 {
      color: #7b5fc5;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .form-control {
      border-radius: 10px;
      border: 1px solid #b8a6f7;
      padding: 12px;
      transition: all 0.3s ease;
    }

    .form-control:focus {
      border-color: #7b5fc5;
      box-shadow: 0 0 8px rgba(123,95,197,0.2);
      outline: none;
    }

    button.btn-primary {
      background: linear-gradient(to right, #a186f0, #7b5fc5);
      border: none;
      font-weight: 600;
      font-size: 16px;
      transition: all 0.3s ease;
      border-radius: 12px;
      padding: 12px;
    }

    button.btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(123,95,197,0.4);
    }

    #statusBox {
      background: #f1ebfc;
      border-left: 4px solid #7b5fc5;
      padding: 10px;
      border-radius: 8px;
    }

    .chatbot-button {
      position: fixed;
      bottom: 25px;
      right: 25px;
      width: 70px;
      height: 70px;
      border-radius: 50%;
      background: linear-gradient(45deg, #a186f0, #7b5fc5);
      color: white;
      font-size: 32px;
      border: none;
      display: flex;
      justify-content: center;
      align-items: center;
      box-shadow: 0px 6px 18px rgba(0,0,0,0.2);
      cursor: pointer;
      z-index: 99999;
      transition: all 0.3s ease;
    }

    .chatbot-button:hover {
      transform: scale(1.1);
      box-shadow: 0px 10px 25px rgba(123,95,197,0.4);
    }

    .chat-popup {
      position: fixed;
      bottom: 100px;
      right: 25px;
      width: 380px;
      height: 520px;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0px 8px 28px rgba(0,0,0,0.2);
      display: none;
      overflow: hidden;
      z-index: 9999;
    }

    .chat-header {
      background: linear-gradient(45deg, #a186f0, #7b5fc5);
      color: white;
      padding: 12px;
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

    df-messenger {
      --df-messenger-bot-message: #7b5fc5;
      --df-messenger-button-titlebar-color: #7b5fc5;
      --df-messenger-chat-background-color: #ffffff;
      --df-messenger-font-color: #000000;
      --df-messenger-send-icon: #a186f0;
      width: 100%;
      height: 100%;
    }
  </style>
</head>

<body class="p-4">

<div class="container">
  <h2 class="mb-3">On-Road Mechanic Assistance</h2>

  <div class="row g-4">

    <!-- LEFT SIDE -->
    <div class="col-md-5">
      <div class="card shadow-sm p-3">

        <h5>Request Help</h5>

        <form id="requestForm">
          <div class="mb-2">
            <input name="name" placeholder="Your Name" class="form-control" required>
          </div>

          <div class="mb-2">
            <input name="phone" placeholder="Phone Number" class="form-control" required>
          </div>

          <div class="mb-2">
            <textarea name="problem" placeholder="Describe your vehicle problem" class="form-control" required></textarea>
          </div>

          <!-- Hidden GPS -->
          <input type="hidden" name="lat" id="lat">
          <input type="hidden" name="lng" id="lng">

          <button class="btn btn-primary w-100" type="submit">Request Mechanic</button>
        </form>

        <hr>

        <h6>Status</h6>
        <div id="statusBox" class="text-muted">Submit request to track your mechanic.</div>

      </div>
    </div>

    <!-- RIGHT SIDE -->
    <div class="col-md-7">
      <div id="map"></div>
    </div>

  </div>
</div>

<!-- Chat -->
<button class="chatbot-button" onclick="toggleChat()">💬</button>

<div class="chat-popup" id="chatWindow">
  <div class="chat-header">
    RideOnGo Bot
    <span class="close-chat" onclick="toggleChat()">×</span>
  </div>

  <df-messenger
      intent="WELCOME"
      chat-title="RideOnGoBot"
      agent-id="18f96ed3-fe8d-4ac0-bdcc-bce58ca20555"
      language-code="en">
  </df-messenger>
</div>

<script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Map initialization
let map = L.map('map').setView([12.9716, 77.5946], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

let userMarker;
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

  }, () => { alert("Could not get GPS location."); });
}

function toggleChat() {
  const box = document.getElementById("chatWindow");
  box.style.display = box.style.display === "block" ? "none" : "block";
}
</script>

</body>
</html>
