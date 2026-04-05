-- calendar.sql
-- Create events table and insert some sample events
CREATE DATABASE IF NOT EXISTS calendar_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE calendar_db;

DROP TABLE IF EXISTS events;
CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  color VARCHAR(50) DEFAULT 'default',
  recurrence ENUM('none','daily','weekly','monthly','yearly') DEFAULT 'none',
  recurrence_end DATE NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample events (single day, long event, recurring)
INSERT INTO events (title, start_date, end_date, color, recurrence, recurrence_end) VALUES
('Team Meeting', CURDATE() + INTERVAL 2 DAY, CURDATE() + INTERVAL 2 DAY, 'blue', 'none', NULL),
('Project Deadline', CURDATE() + INTERVAL 9 DAY, CURDATE() + INTERVAL 9 DAY, 'red', 'none', NULL),
('Conference', CURDATE() + INTERVAL 17 DAY, CURDATE() + INTERVAL 19 DAY, 'green', 'none', NULL),
('Daily Standup', CURDATE() - INTERVAL 7 DAY, CURDATE() + INTERVAL 30 DAY, 'teal', 'daily', CURDATE() + INTERVAL 30 DAY),
('Sprint', CURDATE() + INTERVAL 25 DAY, CURDATE() + INTERVAL 31 DAY, 'orange', 'none', NULL);

