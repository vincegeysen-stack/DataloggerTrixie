<?php
/**
 * get_data.php
 * Haalt temperatuur en luchtvochtigheid data uit MySQL database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Haal limit parameter op (standaard 96 voor 1 dag)
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 96;

// Valideer limit (max 1000000 voor alle data)
if ($limit < 1 || $limit > 1000000) {
    echo json_encode(['error' => 'Invalid limit parameter']);
    exit;
}

// Verbind met database
$conn = new mysqli("localhost", "logger", "paswoord", "temperatures");

// Check connectie
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Stel charset in
$conn->set_charset("utf8");

// Query voor data - als limit zeer hoog is, haal alle data op
if ($limit >= 100000) {
    $query = "SELECT dateandtime, temperature, humidity
              FROM temperaturedata
              ORDER BY dateandtime DESC";

    // Geen prepared statement nodig zonder parameter
    $result = $conn->query($query);

    if (!$result) {
        echo json_encode(['error' => 'Query execution failed: ' . $conn->error]);
        $conn->close();
        exit;
    }
} else {
    $query = "SELECT dateandtime, temperature, humidity
              FROM temperaturedata
              ORDER BY dateandtime DESC
              LIMIT ?";

    // Gebruik prepared statement voor veiligheid
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['error' => 'Query preparation failed: ' . $conn->error]);
        $conn->close();
        exit;
    }

    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Verzamel data
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'dateandtime' => $row['dateandtime'],
        'temperature' => floatval($row['temperature']),
        'humidity' => floatval($row['humidity'])
    ];
}

// Sluit connecties
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();

// Keer data om (van DESC naar ASC voor chronologische weergave)
$data = array_reverse($data);

// Return JSON
echo json_encode($data);
?>
