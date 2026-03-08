-- Use existing database
ALTER SESSION SET CURRENT_SCHEMA=resume_builder;

-- ================= USERS TABLE =================
CREATE TABLE IF NOT EXISTS users (
    id INT GENERATED AS IDENTITY PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NULL,
    google_id VARCHAR(255) NULL,
    profile_pic TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ================= RESUMES TABLE =================
CREATE TABLE IF NOT EXISTS resumes (
    id INT GENERATED AS IDENTITY PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100),
    email VARCHAR(150),
    phone VARCHAR(50),
    summary TEXT,
    skills TEXT,
    education TEXT,
    experience TEXT,
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ================= PAYMENTS TABLE (Future Use) =================
CREATE TABLE IF NOT EXISTS payments (
    id INT GENERATED AS IDENTITY PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2),
    payment_status VARCHAR(50),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);