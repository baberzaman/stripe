<?php

require_once __DIR__ . '/db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$action = $_GET['action'] ?? '';
$db = support_chat_db();

if ($action === 'create_ticket' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $name = trim($payload['name'] ?? '');
    $email = trim($payload['email'] ?? '');
    $subject = trim($payload['subject'] ?? 'General Support');
    $message = trim($payload['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        support_chat_json(['error' => 'name, email and message are required'], 422);
    }

    $db->beginTransaction();
    $stmt = $db->prepare('INSERT INTO tickets(name,email,subject,status,created_at) VALUES(?,?,?,?,?)');
    $stmt->execute([$name, $email, $subject, 'open', gmdate('c')]);
    $ticketId = (int)$db->lastInsertId();

    $msg = $db->prepare('INSERT INTO messages(ticket_id,sender,message,created_at) VALUES(?,?,?,?)');
    $msg->execute([$ticketId, 'customer', $message, gmdate('c')]);
    $db->commit();

    support_chat_json(['ticket_id' => $ticketId]);
}

if ($action === 'send_message' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $ticketId = (int)($payload['ticket_id'] ?? 0);
    $sender = trim($payload['sender'] ?? 'customer');
    $message = trim($payload['message'] ?? '');

    if ($ticketId <= 0 || $message === '') {
        support_chat_json(['error' => 'ticket_id and message are required'], 422);
    }

    $stmt = $db->prepare('INSERT INTO messages(ticket_id,sender,message,created_at) VALUES(?,?,?,?)');
    $stmt->execute([$ticketId, $sender, $message, gmdate('c')]);

    support_chat_json(['ok' => true]);
}

if ($action === 'get_messages') {
    $ticketId = (int)($_GET['ticket_id'] ?? 0);
    if ($ticketId <= 0) {
        support_chat_json(['error' => 'ticket_id required'], 422);
    }

    $stmt = $db->prepare('SELECT id,sender,message,created_at FROM messages WHERE ticket_id = ? ORDER BY id ASC');
    $stmt->execute([$ticketId]);
    support_chat_json(['messages' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

if ($action === 'list_tickets') {
    $rows = $db->query('SELECT id,name,email,subject,status,created_at FROM tickets ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    support_chat_json(['tickets' => $rows]);
}

support_chat_json(['error' => 'Unsupported action'], 404);
