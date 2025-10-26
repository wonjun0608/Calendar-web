<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.html");
//     exit();
// }

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
    <link rel="stylesheet" href="css/color.css" />
</head>
<body>
    <div class="auth-controls" style="text-align:right; margin:10px 20px;">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span>Welcome, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b></span>
            <button id="logoutBtn" >
                Logout
            </button>
            <?php else: ?>
                <button 
                id="loginBtn">
                Login
                </button>
            <?php endif; ?>
    </div>
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

    <label for="filterTag"><b>Filter by Tag:</b></label>
    <select id="filterTag" style="padding:6px; width:80%; text-align:center;">
        <option value="all" selected>All</option>
        <option value="1">Work</option>
        <option value="2">Event</option>
        <option value="3">Meeting</option>
        <option value="4">Other</option>
    </select>

    <div id="eventModal" class="modal">
        <div class="modal-content">
            <span id="closeModal">&times;</span>
            <h2 id="modalTitle">Add Event</h2>
            <form id="eventForm" style="text-align: center;">
                <input type="hidden" name="action" value="add" />
                <input type="hidden" id="eventId" name="event_id" />
                <input type="hidden" id="csrfToken" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px; margin-bottom: 15px;">
        <input type="date" id="eventDate" name="event_date" required style="padding:6px; width:80%; text-align:center;" />
        <input type="time" id="eventTime" name="event_time" required style="padding:6px; width:80%; text-align:center;" />
        <input type="text" id="eventTitle" name="title" placeholder="Event Title" required style="padding:6px; width:80%; text-align:center;" />
        <textarea id="eventDescription" name="description" placeholder="Description" style="padding:6px; width:80%; text-align:center; height:60px;"></textarea>

        <label for="eventTag"><b>Tag:</b></label>
        <select id="eventTag" name="tag_id" style="padding:6px; width:80%; text-align:center;">
            <option value="1" selected>Work</option>
            <option value="2">Event</option>
            <option value="3">Meeting</option>
            <option value="4">Other</option>
        </select>
    
        <label for="eventColor"><b>Select Color:</b></label>
        <div id="colorPicker" class="color-picker">
            <div class="color-option" style="background-color: #007bff;" data-color="#007bff"></div>
            <div class="color-option" style="background-color: #28a745;" data-color="#28a745"></div>
            <div class="color-option" style="background-color: #f39c12;" data-color="#f39c12"></div>
            <div class="color-option" style="background-color: #dc3545;" data-color="#dc3545"></div>
            <div class="color-option" style="background-color: #6f42c1;" data-color="#6f42c1"></div>
        </div>
        <input type="hidden" name="color" id="eventColor" value="#007bff">


       
         <div id="shareSection">
        <h3>Share Calendar</h3>
        <input type="text" id="shareUsername" placeholder="Enter username" />
        <label>
            <input type="checkbox" id="canEdit" /> Allow editing
        </label>
        <button id="shareBtn">Share</button>
    </div>

     <div style="text-align: center; margin-top: 10px;">
        <label>
            <input type="checkbox" id="makeGroup" /> Save as Group Event
        </label>
        <br>
        <input 
            type="text" 
            id="participants" 
            name="participants" 
            placeholder="Enter username for group(comma-separated)" 
            style="display: none; margin-top: 5px; text-align: center; width: 80%;"
        />
        </div>

    <button 
        type="submit" 
        style="margin-top:15px; padding:8px 20px; background:#007bff; color:white; border:none; border-radius:6px; cursor:pointer;">
        Save
    </button>

          <button 
        type="button" 
        id="deleteEventBtn"
        style="margin-top:10px; padding:6px 20px; background:#dc3545; color:white; border:none; border-radius:6px; cursor:pointer; display:none;">
        Delete
    </button>
</form>

</div> 
</div> 

<script>
  document.addEventListener('DOMContentLoaded', () => {        
    const loginBtn = document.getElementById('loginBtn');
    const logoutBtn = document.getElementById('logoutBtn');         
    const mk = document.getElementById('makeGroup');                    
    const part = document.getElementById('participants');        
    if (mk && part) {                                                
      mk.addEventListener('change', () => {                            
        part.style.display = mk.checked ? 'block' : 'none';            
      });                                                               
    } 
    
    if (loginBtn) {
        loginBtn.addEventListener('click', () => {
        window.location.href = 'login.html';
        });
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
        const form = new FormData();

        form.append('action', 'logout');
        form.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
        
        const res = await fetch('php/logout.php', { method: 'POST', body: form });
        const data = await res.json();
        if (data.success) {
            window.location.href = 'login.html';
        } else {
            alert(data.message || 'Logout failed');
        }
        });
    }
  });                                                                   
</script>


<script src="js/calendar.js"></script>
</body>
</html>

