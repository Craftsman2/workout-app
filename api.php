<?php
header('Content-Type: application/json');
$file = 'data.json';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['action'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$action = $input['action'];

if ($action === 'load') {
    if (file_exists($file)) {
        echo file_get_contents($file);
    } else {
        echo json_encode([
            'workouts' => [],
            'library' => [
                ['id' => 1, 'name' => 'Приседания', 'unit' => 'кг'],
                ['id' => 2, 'name' => 'Бег', 'unit' => 'км']
            ]
        ]);
    }
} elseif ($action === 'save') {
    unset($input['action']);
    if (file_put_contents($file, json_encode($input, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['error' => 'Write error']);
    }
}
?>