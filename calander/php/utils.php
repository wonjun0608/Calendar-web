<?php
require_once 'database.php';


function send_json($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}


function register_user($mysqli, $username, $email, $password) {

    
    $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        return ["success" => false, "message" => "Username already taken"];
    }
    $stmt->close();

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $mysqli->prepare("INSERT INTO users (username, password_hash, email) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $username, $hash, $email);

    if ($stmt->execute()) {
        $new_id = $stmt->insert_id;
        $stmt->close();
        return ["success" => true, "user_id" => $new_id, "username" => $username];
    } else {
        $stmt->close();
        return ["success" => false, "message" => "Registration failed"];
    }
}


function login_user($mysqli, $username, $password) {
    $stmt = $mysqli->prepare("SELECT user_id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->bind_result($user_id, $hash);
    $stmt->fetch();

    if ($hash && password_verify($password, $hash)) {
        $stmt->close();
        return ["success" => true, "user_id" => $user_id, "username" => $username];
    } else {
        $stmt->close();
        return ["success" => false, "message" => "Invalid credentials"];
    }
}
?>