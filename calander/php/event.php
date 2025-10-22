<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'utils.php';

if (!isset($_SESSION['user_id'])) {
    send_json(["success" => false, "message" => "Not logged in"], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(["success" => false, "message" => "Invalid request"], 405);
}

$csrf = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrf)) {
    send_json(["success" => false, "message" => "Invalid CSRF token"], 403);
}

// action what user want
$action = $_POST['action'] ?? '';




switch ($action) {
    case 'add':
        $title = trim($_POST['title'] ?? '');
        $date  = trim($_POST['event_date'] ?? '');
        $time  = trim($_POST['event_time'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $tagId = isset($_POST['tag_id']) && $_POST['tag_id'] !== '' ? (int) $_POST['tag_id'] : null;
        $color = $_POST['color'] ?? '#007bff';

        if ($title === '' || $date === '' || $time === '') {
            send_json(["success" => false, "message" => "Missing fields"]);
        }
        $result = add_event($mysqli, $_SESSION['user_id'], $title, $date, $time, $desc, $tagId, $color);
        send_json($result);
        break;


    case 'edit':
        $id    = intval($_POST['event_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $date  = trim($_POST['event_date'] ?? '');
        $time  = trim($_POST['event_time'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        if ($id === 0 || $title === '' || $date === '' || $time === '') {
            send_json(["success" => false, "message" => "Missing fields"]);
        }
        $result = edit_event($mysqli, $_SESSION['user_id'], $id, $title, $date, $time, $desc, $color);
        send_json($result);
        break;


    case 'delete':
        $id = intval($_POST['event_id'] ?? 0);
        if ($id === 0) {
            send_json(["success" => false, "message" => "Missing event id"]);
        }
        $result = delete_event($mysqli, $_SESSION['user_id'], $id);
        send_json($result);
        break;


    case 'fetch':
        $result = fetch_events($mysqli, $_SESSION['user_id']);
        send_json($result);
        break;
    
    case 'tags':
        $result = fetch_tags($mysqli);
        send_json($result);
        break;
    
    case 'share':
        $username = trim($_POST['username'] ?? '');
        $can_edit = isset($_POST['can_edit']) ? (int)$_POST['can_edit'] : 0;
        if ($username === '') send_json(["success" => false, "message" => "Username required"]);
        $result = share_calendar($mysqli, $_SESSION['user_id'], $username, $can_edit);
        send_json($result);
        break;

    case 'shared_fetch':
        $shared_events = fetch_shared_events($mysqli, $_SESSION['user_id']);
        send_json(["success" => true, "shared_events" => $shared_events]);
        break;
    
    case 'group_add':
    $title = trim($_POST['title'] ?? '');
    $date = trim($_POST['event_date'] ?? '');
    $time = trim($_POST['event_time'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $participants = $_POST['participants'] ?? ''; 

    if ($title === '' || $date === '' || $time === '' || $participants === '') {
        send_json(["success" => false, "message" => "Missing fields"]);
    }

    $result = add_group_event($mysqli, $_SESSION['user_id'], $title, $date, $time, $desc, $participants);
    send_json($result);
    break;


case 'group_fetch':
    $result = fetch_group_events($mysqli, $_SESSION['user_id']);
    send_json($result);
    break;

    default:
        send_json(["success" => false, "message" => "Unknown action"]);
}
?>