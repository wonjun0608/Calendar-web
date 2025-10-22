CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    description TEXT,
    color VARCHAR(10) NOT NULL DEFAULT '#007bff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE event_tags (
    tag_id INT AUTO_INCREMENT PRIMARY KEY,
    tag_name VARCHAR(50) UNIQUE NOT NULL,
    color VARCHAR(20) DEFAULT '#007bff'
);

CREATE TABLE event_tag_map (
    event_id INT,
    tag_id INT,
    PRIMARY KEY (event_id, tag_id),
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES event_tags(tag_id) ON DELETE CASCADE
);

CREATE TABLE shared_calendars (
    owner_id INT NOT NULL,
    shared_with_id INT NOT NULL,
    can_edit BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (owner_id, shared_with_id),
    FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (shared_with_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE group_events (
    event_id INT NOT NULL,
    participant_id INT NOT NULL,
    PRIMARY KEY (event_id, participant_id),
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES users(user_id) ON DELETE CASCADE
);