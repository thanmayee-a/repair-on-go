<?php
// webhook_dialogflow.php
require 'db.php';
$input = json_decode(file_get_contents('php://input'), true);
$intent = $input['queryResult']['intent']['displayName'] ?? '';
$params = $input['queryResult']['parameters'] ?? [];

if($intent == 'SendMechanic' || $intent == 'Default Welcome Intent'){
    // extract parameters - change the param names as you used in Dialogflow
    $name = $params['given-name'] ?? ($params['name'] ?? 'Guest');
    $phone = $params['phone-number'] ?? ($params['phone'] ?? '000');
    $problem = $params['vehicle-issue'] ?? ($input['queryResult']['queryText'] ?? 'Not specified');
    // location: try geo-city param or lat/lng param if you configured. For demo, set default coords of Bangalore
    $lat = floatval($params['geo-city-lat'] ?? 12.9716);
    $lng = floatval($params['geo-city-lng'] ?? 77.5946);

    // insert customer
    $stmt = $conn->prepare("INSERT INTO customers (name, phone) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $phone); $stmt->execute(); $customer_id = $stmt->insert_id; $stmt->close();

    // insert request
    $stmt = $conn->prepare("INSERT INTO requests (customer_id, problem_text, lat, lng) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isdd", $customer_id, $problem, $lat, $lng); $stmt->execute(); $request_id = $stmt->insert_id; $stmt->close();

    // find nearest verified available mechanic
    $radius_km = 10;
    $q = "SELECT id, name, lat, lng,
    ( 6371 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) AS distance
    FROM mechanics
    WHERE verified = 1 AND available = 1
    HAVING distance <= ?
    ORDER BY distance LIMIT 1";
    $stmt = $conn->prepare($q);
    $stmt->bind_param("dddi", $lat, $lng, $lat, $radius_km);
    $stmt->execute(); $res = $stmt->get_result();
    if($res->num_rows>0){
        $row = $res->fetch_assoc();
        $mech_id = $row['id'];
        $stmt2 = $conn->prepare("UPDATE requests SET mechanic_id = ?, status='assigned' WHERE id = ?");
        $stmt2->bind_param("ii", $mech_id, $request_id); $stmt2->execute(); $stmt2->close();
        $stmt3 = $conn->prepare("UPDATE mechanics SET available = 0 WHERE id = ?");
        $stmt3->bind_param("i", $mech_id); $stmt3->execute(); $stmt3->close();

        $resp = "Help is on the way. Mechanic: ".$row['name'].". Request ID: ".$request_id;
    } else $resp = "Your request (ID: $request_id) was received but no mechanics are available nearby.";

    header('Content-Type: application/json');
    echo json_encode(['fulfillmentText' => $resp]);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['fulfillmentText' => "Sorry, I couldn't process that."]);
