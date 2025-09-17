<?php
header('Content-Type: application/json');

$payload = json_decode(file_get_contents('php://input'), true);

echo json_encode([
    'success' => true,
    'received_at' => date('Y-m-d H:i:s'),
    'payload' => $payload
]);
