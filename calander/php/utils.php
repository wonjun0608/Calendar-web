<?php
require_once __DIR__ . '/database.php';

function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}


function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

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
        $id = $stmt->insert_id;
        $stmt->close();
        return ["success" => true, "user_id" => $id, "username" => $username];
    }
    $stmt->close();
    return ["success" => false, "message" => "Registration failed"];
}

function login_user($mysqli, $username, $password) {
    $stmt = $mysqli->prepare("SELECT user_id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->bind_result($user_id, $hash);
    $stmt->fetch();
    $stmt->close();

    if ($hash && password_verify($password, $hash)) {
        return ["success" => true, "user_id" => $user_id, "username" => $username];
    }
    return ["success" => false, "message" => "Invalid credentials"];
}



function add_event($mysqli, $user_id, $title, $event_date, $event_time, $description, $tag_id = null) {
    $stmt = $mysqli->prepare(
        "INSERT INTO events (user_id, title, event_date, event_time, description)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('issss', $user_id, $title, $event_date, $event_time, $description);
    if ($stmt->execute()) {
        $event_id = $stmt->insert_id;
        $stmt->close();

        if ($tag_id) add_tag_to_event($mysqli, $event_id, $tag_id);
        return ["success" => true, "event_id" => $event_id];
    }
    $stmt->close();
    return ["success" => false, "message" => "Failed to add event"];
}

function edit_event($mysqli, $user_id, $event_id, $title, $event_date, $event_time, $description) {
    $stmt = $mysqli->prepare(
        "UPDATE events
         SET title=?, event_date=?, event_time=?, description=?
         WHERE event_id=? AND user_id=?"
    );
    $stmt->bind_param('ssssii', $title, $event_date, $event_time, $description, $event_id, $user_id);
    $success = $stmt->execute();
    $stmt->close();

    return $success
        ? ["success" => true]
        : ["success" => false, "message" => "Update failed"];
}

function delete_event($mysqli, $user_id, $event_id) {
    $stmt = $mysqli->prepare("DELETE FROM events WHERE event_id=? AND user_id=?");
    $stmt->bind_param('ii', $event_id, $user_id);
    $success = $stmt->execute();
    $stmt->close();

    return $success
        ? ["success" => true]
        : ["success" => false, "message" => "Delete failed"];
}

function fetch_events($mysqli, $user_id) {
    $stmt = $mysqli->prepare(
        "SELECT e.event_id, e.title, e.event_date, e.event_time, e.description,
                m.tag_id
        FROM events e
        LEFT JOIN event_tag_map m ON m.event_id = e.event_id
        WHERE e.user_id = ?
        ORDER BY e.event_date, e.event_time"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $stmt->close();

    return ["success" => true, "events" => $events];
}


// tag functions
function fetch_tags($mysqli) {
    $result = $mysqli->query("SELECT tag_id, tag_name, color FROM event_tags ORDER BY tag_name ASC");
    $tags = [];
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }
    return ["success" => true, "tags" => $tags];
}

function add_tag_to_event($mysqli, $event_id, $tag_id) {
    $stmt = $mysqli->prepare("INSERT INTO event_tag_map (event_id, tag_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $event_id, $tag_id);
    $stmt->execute();
    $stmt->close();
}

function fetch_event_tags($mysqli, $event_id) {
    $stmt = $mysqli->prepare("
        SELECT t.tag_id, t.tag_name, t.color
        FROM event_tags t
        JOIN event_tag_map m ON t.tag_id = m.tag_id
        WHERE m.event_id = ?
    ");
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tags = [];
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }
    $stmt->close();
    return $tags;
}

function share_calendar($mysqli, $owner_id, $shared_with_username, $can_edit = false) {
    // Get shared user's ID
    $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param('s', $shared_with_username);
    $stmt->execute();
    $stmt->bind_result($shared_with_id);
    $stmt->fetch();
    $stmt->close();

    if (!$shared_with_id) {
        return ["success" => false, "message" => "User not found"];
    }

    // Prevent self-sharing
    if ($shared_with_id == $owner_id) {
        return ["success" => false, "message" => "You cannot share with yourself"];
    }

    // Insert or update permission
    $stmt = $mysqli->prepare("
        INSERT INTO shared_calendars (owner_id, shared_with_id, can_edit)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE can_edit = VALUES(can_edit)
    ");
    $stmt->bind_param('iii', $owner_id, $shared_with_id, $can_edit);
    $success = $stmt->execute();
    $stmt->close();

    return $success
        ? ["success" => true, "message" => "Calendar shared with $shared_with_username"]
        : ["success" => false, "message" => "Failed to share calendar"];
}

function fetch_shared_events($mysqli, $user_id) {
    $stmt = $mysqli->prepare("
        SELECT e.*, u.username
        FROM events e
        JOIN shared_calendars s ON e.user_id = s.owner_id
        JOIN users u ON s.owner_id = u.user_id
        WHERE s.shared_with_id = ?
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $stmt->close();
    return $events;
}


?>