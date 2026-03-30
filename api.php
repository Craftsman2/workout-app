<?php
header('Content-Type: application/json');
$file = 'data.json';

// Обработка загрузки файлов (multipart/form-data)
if (!empty($_FILES['files']) && isset($_POST['action']) && $_POST['action'] === 'upload') {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $uploaded = [];
    $files = $_FILES['files'];

    // Нормализуем структуру $_FILES для multiple
    $count = is_array($files['name']) ? count($files['name']) : 1;
    for ($i = 0; $i < $count; $i++) {
        $name    = is_array($files['name'])     ? $files['name'][$i]     : $files['name'];
        $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
        $error   = is_array($files['error'])    ? $files['error'][$i]    : $files['error'];

        if ($error !== UPLOAD_ERR_OK) continue;

        $ext      = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) continue;

        $filename = time() . '_' . $i . '.' . $ext;
        $dest     = $uploadDir . $filename;

        if (move_uploaded_file($tmpName, $dest)) {
            $uploaded[] = $dest;
        }
    }

    echo json_encode(['status' => 'success', 'paths' => $uploaded]);
    exit;
}

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