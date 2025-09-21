<?php
// --- Настройки ---
$access_token = 'vk1.a.XH_FJKKmd0eK95jheE0hVwMqDciU4AkMLivWTeumbihpKjNnYVxxqODBkBhgdBsArZ6OIp_egIul21fHEjLc9RF0zyzHuV50UfQAZYBplEmVfQIFFT5JTd9-7gljL5HOwPcuXGZV82SDALlGYzMZPVcuNJXsNAp6r4PzMV82WQPquKGcTgHAaI4hr1GusrdicWOnlPeLKSh1l_ZgWDFI4g';
$api_version  = '5.131';
$confirmation_token = '11051824';
$log_file = __DIR__ . '/vk_log.txt';
// -----------------

// Функция для логов (можно отключить, если замедляет)
function logToFile($file, $text) {
    $date = date('[Y-m-d H:i:s]');
    file_put_contents($file, $date . " " . $text . "\n", FILE_APPEND | LOCK_EX);
}

// Получаем данные от VK
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// --- Подтверждение сервера ---
if ($data && isset($data['type']) && $data['type'] === 'confirmation') {
    exit($confirmation_token); // строго строка подтверждения
}

// --- Новое сообщение ---
if ($data && isset($data['type']) && $data['type'] === 'message_new') {
    $message = $data['object']['message'] ?? [];
    $text = trim($message['text'] ?? '');
    $peer_id = $message['peer_id'] ?? null;

    // --- Мгновенный ответ VK ---
    echo 'ok';
    flush(); // VK сразу получает ответ, без задержек

    // --- Обработка /ping ---
    if ($peer_id && mb_strtolower($text, 'UTF-8') === '/ping') {
        sendVkMessage($peer_id, 'Работаю', $access_token, $api_version, rand(1000000,9999999));
        logToFile($log_file, "Sent '/ping' response to $peer_id");
    }

    exit;
}

// --- По умолчанию ---
echo 'ok';
exit;

// --- Функция отправки сообщений VK ---
function sendVkMessage($peer_id, $message, $access_token, $version = '5.131', $random_id) {
    $params = [
        'peer_id' => $peer_id,
        'message' => $message,
        'random_id' => $random_id,
        'access_token' => $access_token,
        'v' => $version
    ];

    $ch = curl_init('https://api.vk.com/method/messages.send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_exec($ch); // не ждём результата
    curl_close($ch);
}