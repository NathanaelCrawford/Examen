CREATE DATABASE IF NOT EXISTS SchoolDatabase;

USE SchoolDatabase;

-- Create Roles Table
CREATE TABLE IF NOT EXISTS roles (
id INT AUTO_INCREMENT PRIMARY KEY,
role_name VARCHAR(50) UNIQUE NOT NULL
    );

-- Create Users Table
CREATE TABLE IF NOT EXISTS users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(100) UNIQUE NOT NULL,
password VARCHAR(255) NOT NULL,
email VARCHAR(100) UNIQUE NOT NULL,
role_id INT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (role_id) REFERENCES roles(id)
    );

-- Create Permissions Table
CREATE TABLE IF NOT EXISTS permissions (
id INT AUTO_INCREMENT PRIMARY KEY,
permission_name VARCHAR(100) UNIQUE NOT NULL
    );

-- Create Role Permissions Table
CREATE TABLE IF NOT EXISTS role_permissions (
role_id INT,
permission_id INT,
FOREIGN KEY (role_id) REFERENCES roles(id),
FOREIGN KEY (permission_id) REFERENCES permissions(id),
PRIMARY KEY(role_id, permission_id)
    );

-- Create Docenten Table
CREATE TABLE IF NOT EXISTS docenten (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT,
name VARCHAR(100) NOT NULL,
email VARCHAR(100) UNIQUE NOT NULL,
FOREIGN KEY (user_id) REFERENCES users(id)
    );

-- Create Klassen Table
CREATE TABLE IF NOT EXISTS klassen (
id INT AUTO_INCREMENT PRIMARY KEY,
class_name VARCHAR(100) UNIQUE NOT NULL

    );

-- Create Studenten Table
CREATE TABLE IF NOT EXISTS studenten (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT,
name VARCHAR(100) NOT NULL,
email VARCHAR(100) UNIQUE NOT NULL,
class_id INT,
FOREIGN KEY (user_id) REFERENCES users(id),
FOREIGN KEY (class_id) REFERENCES klassen(id)
    );

-- Create Roosters Table
CREATE TABLE IF NOT EXISTS roosters (
id INT AUTO_INCREMENT PRIMARY KEY,
class_id INT,
created_by INT,
week_end DATE NOT NULL,
start_time DATETIME NOT NULL,
end_time DATETIME NOT NULL,
FOREIGN KEY (class_id) REFERENCES klassen(id),
FOREIGN KEY (created_by) REFERENCES users(id)
    );

-- Create MentorGesprekken Table

CREATE TABLE IF NOT EXISTS mentor_gesprekken (
id INT AUTO_INCREMENT PRIMARY KEY,
student_id INT NOT NULL,
mentor_id INT NOT NULL,
conversation TEXT NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (student_id) REFERENCES users(id),
FOREIGN KEY (mentor_id) REFERENCES users(id),
UNIQUE KEY unique_mentoring (student_id, mentor_id)
    );

-- Insert Roles
INSERT INTO roles (role_name) VALUES
('Manager'),
('Roostermaker'),
('Docent'),
('Mentor'),
('Student');

-- Insert Permissions
INSERT INTO permissions (permission_name) VALUES
('login'), ('logout'),
('add_docent'), ('edit_docent'), ('delete_docent'), ('view_docents'),
('add_roster'), ('edit_roster'), ('generate_roster'),
('view_own_info'), ('edit_own_info'),
('add_class'), ('edit_class'), ('delete_class'), ('view_classes'),
('add_student'), ('edit_student'), ('delete_student'), ('view_students'),
('add_subject'), ('edit_subject'), ('delete_subject'), ('view_subjects'),
('view_colleagues'), ('link_self_to_subject'), ('link_self_to_class_as_mentor'),
('add_conversation'), ('view_conversation'), ('edit_conversation'), ('delete_conversation'),
('view_schedule');

-- Assign Permissions to Roles
-- Manager
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
         JOIN permissions p
              ON p.permission_name IN (
                                       'login', 'logout', 'add_docent', 'edit_docent', 'delete_docent',
                                       'view_docents', 'add_roster'
                  )
WHERE r.role_name = 'Manager';

-- Roostermaker
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
         JOIN permissions p
              ON p.permission_name IN (
                                       'login', 'logout', 'view_own_info', 'generate_roster', 'edit_roster'
                  )
WHERE r.role_name = 'Roostermaker';

-- Docent
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
         JOIN permissions p
              ON p.permission_name IN (
                                       'login', 'logout', 'view_own_info', 'edit_own_info',
                                       'add_class', 'edit_class', 'delete_class', 'view_classes',
                                       'add_student', 'edit_student', 'delete_student', 'view_students',
                                       'add_subject', 'edit_subject', 'delete_subject', 'view_subjects',
                                       'view_colleagues', 'link_self_to_subject', 'link_self_to_class_as_mentor'
                  )
WHERE r.role_name = 'Docent';

-- Mentor
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
         JOIN permissions p
              ON p.permission_name IN (
                                       'add_conversation', 'view_conversation', 'edit_conversation', 'delete_conversation'
                  )
WHERE r.role_name = 'Mentor';

-- Student
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p
ON p.permission_name IN ('login', 'logout', 'view_schedule')
WHERE r.role_name = 'Student';
