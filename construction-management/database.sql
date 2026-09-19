-- =========================================================
-- Construction Project Management System
-- Database: construction_db
-- =========================================================

CREATE DATABASE IF NOT EXISTS construction_db;
USE construction_db;

-- ---------------------------------------------------------
-- USERS (for login)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,   -- stored as hashed password
    full_name VARCHAR(100),
    role VARCHAR(30) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default login: username = admin / password = admin123
-- (hash below corresponds to "admin123")
INSERT INTO users (username, password, full_name, role)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.YeQ8Q9x1nGZ8v3D1s1w0oNqUZ8n3l3gGa', 'Site Administrator', 'admin');

-- ---------------------------------------------------------
-- PROJECTS
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS projects (
    project_id INT AUTO_INCREMENT PRIMARY KEY,
    project_name VARCHAR(150) NOT NULL,
    location VARCHAR(150),
    client_name VARCHAR(100),
    budget DECIMAL(15,2) DEFAULT 0,
    start_date DATE,
    end_date DATE,
    status ENUM('Planned','Active','On Hold','Completed') DEFAULT 'Planned',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- WORKERS
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS workers (
    worker_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    trade VARCHAR(60),          -- Mason, Carpenter, Electrician, Helper...
    daily_wage DECIMAL(10,2) DEFAULT 0,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- MATERIALS (master + running stock)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS materials (
    material_id INT AUTO_INCREMENT PRIMARY KEY,
    material_name VARCHAR(100) NOT NULL,   -- Cement, Steel, Sand, Aggregate, Bricks...
    unit VARCHAR(20) NOT NULL,             -- bag, ton, cft, nos...
    quantity DECIMAL(12,2) DEFAULT 0,      -- current stock (received - used)
    unit_price DECIMAL(10,2) DEFAULT 0,
    supplier VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- MATERIAL USAGE (consumption per project)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS material_usage (
    usage_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    material_id INT NOT NULL,
    quantity_used DECIMAL(12,2) NOT NULL,
    usage_date DATE NOT NULL,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(material_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- ATTENDANCE
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    worker_id INT NOT NULL,
    project_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('Present','Absent','Half Day') DEFAULT 'Present',
    FOREIGN KEY (worker_id) REFERENCES workers(worker_id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- EXPENSES
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS expenses (
    expense_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    category ENUM('Material','Labour','Equipment','Transportation','Other') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    expense_date DATE NOT NULL,
    description VARCHAR(255),
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- PROGRESS
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS progress (
    progress_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    date DATE NOT NULL,
    planned_percentage DECIMAL(5,2) DEFAULT 0,
    actual_percentage DECIMAL(5,2) DEFAULT 0,
    remarks VARCHAR(255),
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
);

-- =========================================================
-- SAMPLE DATA
-- =========================================================
INSERT INTO projects (project_name, location, client_name, budget, start_date, end_date, status) VALUES
('Riverside Apartments', 'Silchar, Assam', 'Riverside Developers Pvt Ltd', 5000000, '2026-01-15', '2026-12-31', 'Active'),
('Green Valley School', 'Guwahati, Assam', 'District Education Board', 3200000, '2026-03-01', '2026-11-30', 'Active'),
('City Mall Renovation', 'Silchar, Assam', 'City Mall Group', 1800000, '2025-09-01', '2026-02-28', 'Completed');

INSERT INTO workers (name, trade, daily_wage, phone) VALUES
('Ramesh Das', 'Mason', 700, '9876500001'),
('Ajit Kumar', 'Carpenter', 650, '9876500002'),
('Suresh Roy', 'Electrician', 750, '9876500003'),
('Manoj Barman', 'Helper', 450, '9876500004'),
('Dilip Nath', 'Plumber', 680, '9876500005');

INSERT INTO materials (material_name, unit, quantity, unit_price, supplier) VALUES
('Cement', 'bag', 500, 380, 'Assam Cement Suppliers'),
('Steel', 'ton', 20, 62000, 'Northeast Steel Co.'),
('Sand', 'cft', 3000, 55, 'Local Sand Depot'),
('Aggregate', 'cft', 2500, 60, 'Local Sand Depot'),
('Bricks', 'nos', 40000, 8, 'Barak Brick Works');

INSERT INTO material_usage (project_id, material_id, quantity_used, usage_date) VALUES
(1, 1, 120, '2026-02-10'),
(1, 3, 500, '2026-02-12'),
(2, 1, 80, '2026-03-15'),
(2, 5, 5000, '2026-03-20');

INSERT INTO attendance (worker_id, project_id, date, status) VALUES
(1, 1, '2026-09-01', 'Present'),
(2, 1, '2026-09-01', 'Present'),
(3, 2, '2026-09-01', 'Absent'),
(4, 1, '2026-09-01', 'Half Day'),
(5, 2, '2026-09-01', 'Present');

INSERT INTO expenses (project_id, category, amount, expense_date, description) VALUES
(1, 'Material', 456000, '2026-02-10', 'Cement and sand purchase'),
(1, 'Labour', 320000, '2026-08-31', 'August wages'),
(1, 'Equipment', 85000, '2026-05-01', 'Concrete mixer rental'),
(2, 'Material', 220000, '2026-03-15', 'Bricks and cement'),
(2, 'Transportation', 40000, '2026-04-01', 'Material transport'),
(3, 'Other', 30000, '2025-12-01', 'Permits and misc');

INSERT INTO progress (project_id, date, planned_percentage, actual_percentage, remarks) VALUES
(1, '2026-09-01', 65, 58, 'Slight delay due to rain'),
(2, '2026-09-01', 40, 42, 'Ahead of schedule'),
(3, '2026-02-28', 100, 100, 'Project completed');
