-- ==========================================
-- Cardiac Rehab Database - 5 Tables Setup
-- ==========================================
-- สคริปต์นี้สำหรับการสร้างตารางใหม่ทั้งหมด 5 ตารางตาม requirements
-- 
-- ⚠️ คำเตือน: การรันสคริปต์นี้จะลบข้อมูลเก่าทั้งหมด (DROP TABLES)
-- ==========================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS exercise_sessions;
DROP TABLE IF EXISTS patient_medical_history;
DROP TABLE IF EXISTS patient_info;
DROP TABLE IF EXISTS patient_auth;
DROP TABLE IF EXISTS patients; -- ลบตารางเก่าถ้ามี
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================
-- 1. Table: users (เจ้าหน้าที่)
-- ==========================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'bcrypt hashed',
    email VARCHAR(100) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    role ENUM('doctor', 'physical_therapist') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 2. Table: patient_auth (ข้อมูล Login ผู้ป่วย)
-- ==========================================
CREATE TABLE patient_auth (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL UNIQUE COMMENT 'Username for login',
    password VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_patient_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 3. Table: patient_info (ข้อมูลส่วนตัวผู้ป่วย)
-- ==========================================
CREATE TABLE patient_info (
    info_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL UNIQUE,
    national_id VARCHAR(13) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    created_by INT NOT NULL COMMENT 'Doctor who created this patient',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_auth(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id),
    INDEX idx_patient_name (first_name, last_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 4. Table: patient_medical_history (ประวัติการรักษา)
-- ==========================================
CREATE TABLE patient_medical_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    symptoms TEXT COMMENT 'อาการ',
    procedure_history TEXT COMMENT 'ประวัติหัตถการ',
    weight DECIMAL(5,2) COMMENT 'น้ำหนัก (kg)',
    height DECIMAL(5,2) COMMENT 'ส่วนสูง (cm)',
    age INT,
    cpet_completed BOOLEAN DEFAULT FALSE,
    heart_rate_resting INT COMMENT 'HR ขณะพัก',
    bp_resting_systolic INT,
    bp_resting_diastolic INT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_auth(patient_id) ON DELETE CASCADE,
    INDEX idx_history_patient (patient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 5. Table: exercise_sessions (บันทึกการออกกำลังกาย)
-- ==========================================
CREATE TABLE exercise_sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    session_date DATETIME NOT NULL,
    
    -- Vital Signs during session
    heart_rate INT,
    bp_systolic INT,
    bp_diastolic INT,
    mets DECIMAL(4,1),
    
    -- Exercise Details
    exercise_method TEXT,
    duration_minutes INT,
    intensity_level VARCHAR(20),
    
    -- Staff
    doctor_id INT,
    therapist_id INT,
    
    -- Evidence
    ekg_image_path VARCHAR(255),
    notes TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_auth(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(user_id),
    FOREIGN KEY (therapist_id) REFERENCES users(user_id),
    INDEX idx_session_date (session_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- Insert Test Data
-- ==========================================

-- 1. Users (password: password123)
INSERT INTO users (username, password, email, first_name, last_name, role) VALUES 
('doc_somchai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'somchai@hospital.com', 'Somchai', 'Dee', 'doctor'),
('pt_somsri', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'somsri@hospital.com', 'Somsri', 'Ngam', 'physical_therapist');

-- 2. Patient Auth (phone: 0812345678, password: password123)
INSERT INTO patient_auth (phone, password) VALUES 
('0812345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

SET @pid = LAST_INSERT_ID();
SET @doc_id = (SELECT user_id FROM users WHERE username = 'doc_somchai');

-- 3. Patient Info
INSERT INTO patient_info (patient_id, national_id, first_name, last_name, gender, created_by) VALUES 
(@pid, '1100012345678', 'Manee', 'Mejai', 'female', @doc_id);

-- 4. Medical History
INSERT INTO patient_medical_history (patient_id, symptoms, weight, height, age) VALUES 
(@pid, 'Tired easily', 65.5, 160.0, 55);

-- 5. Exercise Session
INSERT INTO exercise_sessions (patient_id, session_date, heart_rate, duration_minutes, doctor_id) VALUES 
(@pid, NOW(), 85, 30, @doc_id);

-- Check Data
SELECT 'Users' as 'Table', COUNT(*) as Count FROM users
UNION ALL
SELECT 'Patient Auth', COUNT(*) FROM patient_auth
UNION ALL
SELECT 'Patient Info', COUNT(*) FROM patient_info
UNION ALL
SELECT 'Medical History', COUNT(*) FROM patient_medical_history
UNION ALL
SELECT 'Exercise Sessions', COUNT(*) FROM exercise_sessions;
