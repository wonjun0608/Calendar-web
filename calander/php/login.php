<?php
session_start();
require_once 'utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(["success" => false, "message" => "Invalid request"], 405);
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    send_json(["success" => false, "message" => "Missing username or password"]);
}

$result = login_user($mysqli, $username, $password);

if ($result['success']) {
    $_SESSION['user_id']  = $result['user_id'];
    $_SESSION['username'] = $result['username'];
}
send_json($result);
?>