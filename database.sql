CREATE TABLE file_tracking_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pathname VARCHAR(1024) NOT NULL,
    upload_dt INT NOT NULL,
    file_dt INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(pathname(255)),
    INDEX(upload_dt)
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('viewer', 'admin') NOT NULL DEFAULT 'viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password, role) VALUES
('viewer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'viewer'),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

CREATE TABLE log_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer VARCHAR(255) NOT NULL,
    sku VARCHAR(255) NOT NULL,
    log_date DATE NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(1024) NOT NULL UNIQUE,
    file_size INT NOT NULL,
    modification_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(customer),
    INDEX(sku),
    INDEX(log_date)
);
