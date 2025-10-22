
let currentDate = new Date();
let isEditing = false;
let tags = [];
let activeTags = new Set();

document.addEventListener('DOMContentLoaded', () => {
    loadTags();
    renderCalendar();
    document.getElementById('prevMonth').onclick = () => changeMonth(-1);
    document.getElementById('nextMonth').onclick = () => changeMonth(1);
    document.getElementById('closeModal').onclick = closeModal;
    document.getElementById('eventForm').onsubmit = submitEvent;
    document.getElementById('shareBtn').onclick = shareCalendar;

    const delBtn = document.getElementById('deleteEventBtn');
    if (delBtn) {
        delBtn.addEventListener('click', () => {
            const eventId = document.getElementById('eventId').value;
            if (!eventId) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('event_id', eventId);
            formData.append('csrf_token', document.getElementById('csrfToken').value);

            fetch('./php/event.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        closeModal();
                        renderCalendar(); 
                    }
                })
                .catch(err => console.error('Error while deleting event:', err));
        });
    }
});


function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
    document.getElementById('monthYear').textContent = `${monthNames[month]} ${year}`;

    const tbody = document.getElementById('calendar-body');
    tbody.innerHTML = '';
    let date = 1;

    for (let i = 0; i < 6; i++) {
        const row = document.createElement('tr');
        for (let j = 0; j < 7; j++) {
        const cell = document.createElement('td');
        if (i === 0 && j < firstDay || date > daysInMonth) {
            cell.textContent = '';
        } else {
            cell.textContent = date;
            cell.onclick = () => openModal(year, month, date);
            cell.id = `day-${year}-${month + 1}-${date}`;
            date++;
        }
        row.appendChild(cell);
        }
        tbody.appendChild(row);
    }
    loadEvents(year, month + 1);
}

function changeMonth(step) {
    currentDate.setMonth(currentDate.getMonth() + step);
    renderCalendar();
}

// adding
function openModal(year, month, day) {
    const modal = document.getElementById('eventModal');
    const form = document.getElementById('eventForm');
    isEditing = false;

    document.getElementById('modalTitle').textContent = 'Add Event';
    form.reset();
    form.action.value = 'add';

    const makeGroup = document.getElementById('makeGroup');        
    const participants = document.getElementById('participants');   

    if (makeGroup) makeGroup.checked = false;                        
    if (participants) {                           
        participants.value = '';                 
        participants.style.display = 'none';      
    }                                                           

    const correctedMonth = month + 1;
    const maxDay = new Date(year, correctedMonth, 0).getDate();
    const correctedDay = Math.min(day, maxDay);

    
    document.getElementById('eventDate').value =
        `${year}-${String(correctedMonth).padStart(2, '0')}-${String(correctedDay).padStart(2, '0')}`;

    modal.style.display = 'block';

    
    const delBtn = document.getElementById('deleteEventBtn');
    if (delBtn) delBtn.style.display = 'none';
}

// edit
function openEditModal(ev) {
    const modal = document.getElementById('eventModal');
    const form = document.getElementById('eventForm');
    isEditing = true;

    document.getElementById('modalTitle').textContent = 'Edit Event';
    form.action.value = 'edit';

    
    document.getElementById('eventId').value = ev.event_id;
    document.getElementById('eventDate').value = ev.event_date;
    document.getElementById('eventTime').value = ev.event_time;
    document.getElementById('eventTitle').value = ev.title;
    document.getElementById('eventDescription').value = ev.description;

    const makeGroup = document.getElementById('makeGroup');        
    const participants = document.getElementById('participants');   

    if (makeGroup) makeGroup.checked = false;       
    if (participants) {                             
        participants.value = '';                    
        participants.style.display = 'none';        
    } 

    modal.style.display = 'block';

    
    const delBtn = document.getElementById('deleteEventBtn');
    if (delBtn) delBtn.style.display = 'inline-block';
}


function closeModal() { 
    document.getElementById('eventModal').style.display = 'none'; 
}


function submitEvent(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    formData.append('csrf_token', document.getElementById('csrfToken').value);

    const makeGroup = document.getElementById('makeGroup');
    const participants = document.getElementById('participants');

    if (makeGroup && makeGroup.checked) {
        form.querySelector('input[name="action"]').value = 'group_add';
        formData.set('action', 'group_add');

        const usernames = participants.value.trim();
        if (!usernames) {
            alert("Please enter at least one username for group event.");
            return;
        }
        formData.append('participants', usernames);
    }

    fetch('./php/event.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                closeModal();
                renderCalendar();
            } else {
                alert(res.message || "Failed to save event.");
            }
        })
        .catch(err => console.error('Event submission failed:', err));
}



// Fetch all events for the current user
function loadEvents() {
    const formData = new FormData();
    formData.append('action', 'fetch');
    formData.append('csrf_token', document.getElementById('csrfToken').value);

    fetch('./php/event.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                renderEvents(res.events);
            } else {
                alert(res.message);
            }
        })
        .catch(err => console.error('Error loading events:', err));
}


// Add new event
function addEvent(title, date, time, description) {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('title', title);
    formData.append('event_date', date);
    formData.append('event_time', time);
    formData.append('description', description);
    formData.append('csrf_token', document.getElementById('csrfToken').value);

    fetch('./php/event.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
        if (res.success) loadEvents();
        else alert(res.message);
        });
}

// Edit event
function editEvent(id, title, date, time, description) {
    const formData = new FormData();
    formData.append('action', 'edit');
    formData.append('event_id', id);
    formData.append('title', title);
    formData.append('event_date', date);
    formData.append('event_time', time);
    formData.append('description', description);
    formData.append('csrf_token', document.getElementById('csrfToken').value);

    fetch('./php/event.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
        if (res.success) loadEvents();
        else alert(res.message);
        });
}

// Delete event
function deleteEvent(id) {
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('event_id', id);
    formData.append('csrf_token', document.getElementById('csrfToken').value);

    fetch('./php/event.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
        if (res.success) loadEvents();
        else alert(res.message);
        });
}


function renderEvents(events) {
    document.querySelectorAll('.event').forEach(e => e.remove());

    events.forEach(ev => {
        const [y, m, d] = ev.event_date.split('-').map(Number);
        if (y === currentDate.getFullYear() && m === currentDate.getMonth() + 1) {
        const cell = document.getElementById(`day-${y}-${m}-${d}`);
        if (!cell) return;

        // Use tag_id if present; show event if no tag (don’t filter it out)
        const tagId = ev.tag_id ?? (ev.tags?.[0]?.tag_id ?? null);
        if (tagId && !activeTags.has(String(tagId))) return;

        const tag = tagId ? tags.find(t => String(t.tag_id) === String(tagId)) : null;

        const div = document.createElement('div');
        div.className = 'event';
        if (ev.is_group === 1 || ev.group === true) {
                div.textContent = `[Group] ${ev.title}`;
                div.style.backgroundColor = '#f39c12';
            } else {
                div.textContent = ev.title;
                div.style.backgroundColor = tag?.color || '#007bff';
            }
        div.onclick = (event) => {
            event.stopPropagation();
            openEditModal(ev);
        };
        cell.appendChild(div);
        }
    });
}

function loadTags() {
    const formData = new FormData();
    formData.append('action', 'tags');
    formData.append('csrf_token', document.getElementById('csrfToken').value);

    fetch('./php/event.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
        if (res.success) {
            tags = res.tags;
            populateTagDropdown();
            renderTagFilters();
        }
        });
}

function populateTagDropdown() {
    const select = document.getElementById('eventTag');
    select.innerHTML = '<option value="">-- Select Tag --</option>';
    tags.forEach(tag => {
        const opt = document.createElement('option');
        opt.value = tag.tag_id;
        opt.textContent = tag.tag_name;
        opt.style.color = tag.color;
        select.appendChild(opt);
    });
}

function renderTagFilters() {
    const container = document.getElementById('tagFilters');
    container.innerHTML = '';
    tags.forEach(tag => {
        const label = document.createElement('label');
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.value = tag.tag_id;
        checkbox.checked = true;
        activeTags.add(tag.tag_id);

        checkbox.onchange = () => {
        if (checkbox.checked) activeTags.add(tag.tag_id);
        else activeTags.delete(tag.tag_id);
        renderCalendar();
        };

        label.appendChild(checkbox);
        label.append(` ${tag.tag_name}`);
        label.style.color = tag.color;
        container.appendChild(label);
    });
}


function shareCalendar() {
    const username = document.getElementById('shareUsername').value.trim();
    const canEdit = document.getElementById('canEdit').checked ? 1 : 0;
    if (!username) return alert('Enter a username');

    const formData = new FormData();
    formData.append('action', 'share');
    formData.append('username', username);
    formData.append('can_edit', canEdit);
    formData.append('csrf_token', document.getElementById('csrfToken').value);

    fetch('./php/event.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => alert(res.message));
}



function loadSharedEvents() {
    const formData = new FormData();
    formData.append('action', 'shared_fetch');
    formData.append('csrf_token', document.getElementById('csrfToken').value);

    fetch('./php/event.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
        if (res.success) renderSharedEvents(res.shared_events);
        });
}

function renderSharedEvents(events) {
    events.forEach(ev => {
        const [y, m, d] = ev.event_date.split('-').map(Number);
        if (y === currentDate.getFullYear() && m === currentDate.getMonth() + 1) {
        const cell = document.getElementById(`day-${y}-${m}-${d}`);
        if (cell) {
            const div = document.createElement('div');
            div.className = 'event';
            div.textContent = `${ev.username}: ${ev.title}`;
            div.style.backgroundColor = '#28a745'; 
            cell.appendChild(div);
        }
        }
    });
}



