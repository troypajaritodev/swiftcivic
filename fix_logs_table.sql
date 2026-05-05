-- Fix logs table structure for InfinityFree
-- Run this SQL in phpMyAdmin

-- Option 1: Drop and recreate (WARNING: Deletes existing logs)
DROP TABLE IF EXISTS logs;

CREATE TABLE logs (
    id INT AUTO_INCREMENT,
    user_id INT,
    request_id INT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Option 2: Just rename column (preserves data)
-- ALTER TABLE logs CHANGE COLUMN user_agent details TEXT;
