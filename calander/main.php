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
    <link rel="stylesheet" href="css/color.css" />
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
            <form id="eventForm" style="text-align: center;">
                <input type="hidden" name="action" value="add" />
                <input type="hidden" id="eventId" name="event_id" />
                <input type="hidden" id="csrfToken" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px; margin-bottom: 15px;">
        <input type="date" id="eventDate" name="event_date" required style="padding:6px; width:80%; text-align:center;" />
        <input type="time" id="eventTime" name="event_time" required style="padding:6px; width:80%; text-align:center;" />
        <input type="text" id="eventTitle" name="title" placeholder="Event Title" required style="padding:6px; width:80%; text-align:center;" />
        <textarea id="eventDescription" name="description" placeholder="Description" style="padding:6px; width:80%; text-align:center; height:60px;"></textarea>
    
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
    const mk = document.getElementById('makeGroup');                    
    const part = document.getElementById('participants');        
    if (mk && part) {                                                
      mk.addEventListener('change', () => {                            
        part.style.display = mk.checked ? 'block' : 'none';            
      });                                                               
    }                                                                   
  });                                                                   
</script>


<script src="js/calendar.js"></script>
</body>
</html>

