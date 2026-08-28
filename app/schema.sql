-- Workshop Registration - database schema
-- Run this once to set up the database before testing.
--   mysql -u root -p < schema.sql

CREATE DATABASE IF NOT EXISTS workshop_app;
USE workshop_app;

CREATE TABLE IF NOT EXISTS workshops (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    name      VARCHAR(150) NOT NULL,
    capacity  INT NOT NULL
);

CREATE TABLE IF NOT EXISTS registrations (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    workshop_id  INT NOT NULL,
    full_name    VARCHAR(150) NOT NULL,
    email        VARCHAR(150) NOT NULL,
    phone        VARCHAR(30)  NOT NULL,
    seats        INT NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id)
);

INSERT INTO workshops (name, capacity) VALUES
    ('Intro to Web Security', 30),
    ('Advanced PHP',          20),
    ('MySQL Performance',     25);
