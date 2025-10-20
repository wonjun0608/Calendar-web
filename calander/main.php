<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Calendar App</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <h1 id="monthYear"></h1>
    <div class="calendar-controls">
        <button id="prevMonth">← Prev</button>
        <button id="nextMonth">Next →</button>
    </div>

    <div id="tagFilters" class="tag-filters"></div>

    <table id="calendar">
        <thead>
            <tr>
            <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
            </tr>
        </thead>
        <tbody id="calendar-body"></tbody>
    </table>

    <label for="eventTag">Tag:</label>
    <select id="eventTag" name="tag_id">
        <option value="">Select Tag</option>
    </select>

    <div id="eventModal" class="modal">
        <div class="modal-content">
            <span id="closeModal">&times;</span>
            <h2 id="modalTitle">Add Event</h2>
            <form id="eventForm">
                <input type="hidden" name="action" value="add" />
                <input type="hidden" id="eventId" name="event_id" />
                <input type="hidden" id="csrfToken" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <input type="date" id="eventDate" name="event_date" required />
                <input type="time" id="eventTime" name="event_time" required />
                <input type="text" id="eventTitle" name="title" placeholder="Event Title" required />
                <textarea id="eventDescription" name="description" placeholder="Description"></textarea>
                <button type="submit">Save</button>
            </form>
        </div>
    </div>
    <div id="shareSection">
        <h3>Share Calendar</h3>
        <input type="text" id="shareUsername" placeholder="Enter username" />
        <label>
            <input type="checkbox" id="canEdit" /> Allow editing
        </label>
        <button id="shareBtn">Share</button>
    </div>

  <script src="js/calendar.js"></script>
</body>
</html>
