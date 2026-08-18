-- Plus International School — complete database schema (MySQL / MariaDB)
-- Tunga, Minna, Niger State.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------- users ----
CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    phone           VARCHAR(30) DEFAULT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('superadmin','subadmin','cashier','teacher','student','parent') NOT NULL,
    admission_no    VARCHAR(40) DEFAULT NULL UNIQUE,
    staff_no        VARCHAR(40) DEFAULT NULL UNIQUE,
    class_id        INT DEFAULT NULL,
    gender          ENUM('male','female') DEFAULT NULL,
    date_of_birth   DATE DEFAULT NULL,
    student_status  ENUM('new','returning') DEFAULT NULL,
    address         VARCHAR(255) DEFAULT NULL,
    photo           VARCHAR(255) DEFAULT NULL,
    status          ENUM('active','suspended') NOT NULL DEFAULT 'active',
    created_by      INT DEFAULT NULL,
    last_login      DATETIME DEFAULT NULL,
    reset_token     VARCHAR(100) DEFAULT NULL,
    reset_expires   DATETIME DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_class (class_id)
) ENGINE=InnoDB;

-- Links a parent account to one or more student accounts.
CREATE TABLE IF NOT EXISTS parent_students (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    parent_id   INT NOT NULL,
    student_id  INT NOT NULL,
    UNIQUE KEY uniq_parent_student (parent_id, student_id),
    FOREIGN KEY (parent_id)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------ academics ----
CREATE TABLE IF NOT EXISTS school_classes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(60) NOT NULL UNIQUE,
    section     ENUM('nursery','primary','secondary') NOT NULL,
    level_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS subjects (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(80) NOT NULL UNIQUE,
    code    VARCHAR(20) DEFAULT NULL,
    section ENUM('nursery','primary','secondary','all') NOT NULL DEFAULT 'all'
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS class_subjects (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    class_id    INT NOT NULL,
    subject_id  INT NOT NULL,
    teacher_id  INT DEFAULT NULL,
    UNIQUE KEY uniq_class_subject (class_id, subject_id),
    FOREIGN KEY (class_id)   REFERENCES school_classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS academic_sessions (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(20) NOT NULL UNIQUE,   -- e.g. 2025/2026
    term       ENUM('First','Second','Third') NOT NULL,
    is_current TINYINT(1) NOT NULL DEFAULT 0,
    starts_on  DATE DEFAULT NULL,
    ends_on    DATE DEFAULT NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------------- results ----
-- Permanent academic record: one row per student / subject / term / session.
CREATE TABLE IF NOT EXISTS results (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    student_id     INT NOT NULL,
    class_id       INT NOT NULL,
    subject_id     INT NOT NULL,
    session_name   VARCHAR(20) NOT NULL,
    term           ENUM('First','Second','Third') NOT NULL,
    ca1            DECIMAL(5,2) NOT NULL DEFAULT 0,   -- max 10
    ca2            DECIMAL(5,2) NOT NULL DEFAULT 0,   -- max 10
    assignment     DECIMAL(5,2) NOT NULL DEFAULT 0,   -- max 10
    exam           DECIMAL(5,2) NOT NULL DEFAULT 0,   -- max 70
    total          DECIMAL(5,2) NOT NULL DEFAULT 0,
    grade          VARCHAR(3) NOT NULL DEFAULT 'F9',
    remark         VARCHAR(40) NOT NULL DEFAULT 'Fail',
    subject_position INT DEFAULT NULL,
    entered_by     INT DEFAULT NULL,
    published      TINYINT(1) NOT NULL DEFAULT 0,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_result (student_id, subject_id, session_name, term),
    INDEX idx_result_lookup (class_id, session_name, term),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id)   REFERENCES school_classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Per-term summary (computed by ResultCalculator).
CREATE TABLE IF NOT EXISTS result_summaries (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    student_id     INT NOT NULL,
    class_id       INT NOT NULL,
    session_name   VARCHAR(20) NOT NULL,
    term           ENUM('First','Second','Third') NOT NULL,
    subjects_count INT NOT NULL DEFAULT 0,
    total_score    DECIMAL(8,2) NOT NULL DEFAULT 0,
    average        DECIMAL(5,2) NOT NULL DEFAULT 0,
    class_average  DECIMAL(5,2) NOT NULL DEFAULT 0,
    position       INT DEFAULT NULL,
    class_size     INT NOT NULL DEFAULT 0,
    overall_grade  VARCHAR(3) DEFAULT NULL,
    teacher_remark VARCHAR(255) DEFAULT NULL,
    principal_remark VARCHAR(255) DEFAULT NULL,
    published      TINYINT(1) NOT NULL DEFAULT 0,
    computed_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_summary (student_id, session_name, term),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Permanent student record store (files, from inception till date).
CREATE TABLE IF NOT EXISTS student_records (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    student_id   INT NOT NULL,
    title        VARCHAR(150) NOT NULL,
    record_type  VARCHAR(40) NOT NULL DEFAULT 'other',
    description  TEXT,
    file_path    VARCHAR(255) DEFAULT NULL,
    session_name VARCHAR(20) DEFAULT NULL,
    uploaded_by  INT DEFAULT NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------- payments ----
CREATE TABLE IF NOT EXISTS fee_structure (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    class_id     INT NOT NULL,
    term         ENUM('First','Second','Third') NOT NULL,
    session_name VARCHAR(20) NOT NULL,
    amount       DECIMAL(12,2) NOT NULL,
    description  VARCHAR(255) DEFAULT NULL,
    UNIQUE KEY uniq_fee (class_id, term, session_name),
    FOREIGN KEY (class_id) REFERENCES school_classes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS school_bank_accounts (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    bank_name    VARCHAR(100) NOT NULL,
    account_name VARCHAR(150) NOT NULL,
    account_number VARCHAR(30) NOT NULL,
    is_active    TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- status flow: submitted -> verified -> approved (cashier approves) / rejected
CREATE TABLE IF NOT EXISTS payment_transactions (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    reference       VARCHAR(60) NOT NULL UNIQUE,
    student_id      INT NOT NULL,
    payer_id        INT DEFAULT NULL,          -- student or parent who submitted
    student_name    VARCHAR(150) NOT NULL,
    class_id        INT NOT NULL,
    term            ENUM('First','Second','Third') NOT NULL,
    session_name    VARCHAR(20) NOT NULL,
    amount          DECIMAL(12,2) NOT NULL,
    amount_expected DECIMAL(12,2) NOT NULL DEFAULT 0,
    channel         ENUM('paystack','remita','bank-transfer','cash') NOT NULL DEFAULT 'paystack',
    gateway_ref     VARCHAR(100) DEFAULT NULL,
    status          ENUM('pending','submitted','verified','approved','rejected') NOT NULL DEFAULT 'pending',
    student_status  ENUM('new','returning') DEFAULT NULL,
    proof_path      VARCHAR(255) DEFAULT NULL,
    receipt_number  VARCHAR(40) DEFAULT NULL UNIQUE,
    verifier_id     INT DEFAULT NULL,
    approver_id     INT DEFAULT NULL,
    rejection_note  VARCHAR(255) DEFAULT NULL,
    paid_at         DATETIME DEFAULT NULL,
    approved_at     DATETIME DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pay_status (status),
    INDEX idx_pay_class (class_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id)   REFERENCES school_classes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------ timetable ----
CREATE TABLE IF NOT EXISTS timetable_slots (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    class_id      INT NOT NULL,
    subject_id    INT NOT NULL,
    teacher_id    INT DEFAULT NULL,
    day_of_week   ENUM('Monday','Tuesday','Wednesday','Thursday','Friday') NOT NULL,
    period        INT NOT NULL,
    starts_at     TIME NOT NULL,
    ends_at       TIME NOT NULL,
    room          VARCHAR(60) DEFAULT NULL,
    session_name  VARCHAR(20) NOT NULL,
    term          ENUM('First','Second','Third') NOT NULL,
    UNIQUE KEY uniq_slot (class_id, day_of_week, period, session_name, term),
    FOREIGN KEY (class_id)   REFERENCES school_classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------- chat ----
CREATE TABLE IF NOT EXISTS chat_users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT DEFAULT NULL,          -- optional link to portal account
    full_name     VARCHAR(150) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    phone         VARCHAR(30) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('student','teacher') NOT NULL,
    admission_no  VARCHAR(40) DEFAULT NULL,
    staff_no      VARCHAR(40) DEFAULT NULL,
    gender        ENUM('male','female') DEFAULT NULL,
    age           INT DEFAULT NULL,
    class_id      INT DEFAULT NULL,
    chat_status   ENUM('online','offline','away') NOT NULL DEFAULT 'offline',
    last_seen     DATETIME DEFAULT NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES school_classes(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chat_messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    sender_id   INT NOT NULL,
    receiver_id INT NOT NULL,
    body        TEXT NOT NULL,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_conversation (sender_id, receiver_id, id),
    FOREIGN KEY (sender_id)   REFERENCES chat_users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES chat_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------- access & auditing ----
CREATE TABLE IF NOT EXISTS user_permissions (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    permission   VARCHAR(60) NOT NULL,
    granted_by   INT DEFAULT NULL,
    expires_at   DATETIME DEFAULT NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_permission (user_id, permission),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT DEFAULT NULL,
    actor_name  VARCHAR(150) DEFAULT NULL,
    action      VARCHAR(80) NOT NULL,
    entity      VARCHAR(60) DEFAULT NULL,
    entity_id   VARCHAR(60) DEFAULT NULL,
    details     TEXT,
    ip_address  VARCHAR(45) DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT DEFAULT NULL,        -- NULL = broadcast
    audience   ENUM('all','student','teacher','parent','staff') DEFAULT NULL,
    title      VARCHAR(150) NOT NULL,
    body       TEXT,
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS announcements (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(180) NOT NULL,
    body        TEXT NOT NULL,
    audience    ENUM('public','students','parents','staff','all') NOT NULL DEFAULT 'public',
    created_by  INT DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attendance (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id   INT NOT NULL,
    attendance_date DATE NOT NULL,
    status     ENUM('present','absent','late','excused') NOT NULL DEFAULT 'present',
    marked_by  INT DEFAULT NULL,
    UNIQUE KEY uniq_attendance (student_id, attendance_date),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS assignments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    class_id    INT NOT NULL,
    subject_id  INT NOT NULL,
    teacher_id  INT DEFAULT NULL,
    title       VARCHAR(180) NOT NULL,
    description TEXT,
    due_date    DATE DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id)   REFERENCES school_classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admission_applications (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    reference     VARCHAR(40) NOT NULL UNIQUE,
    child_name    VARCHAR(150) NOT NULL,
    parent_name   VARCHAR(150) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    phone         VARCHAR(30) NOT NULL,
    class_applied VARCHAR(60) NOT NULL,
    date_of_birth DATE DEFAULT NULL,
    gender        ENUM('male','female') DEFAULT NULL,
    previous_school VARCHAR(150) DEFAULT NULL,
    address       TEXT,
    status        ENUM('pending','shortlisted','admitted','rejected') NOT NULL DEFAULT 'pending',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contact_messages (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(150) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    phone      VARCHAR(30) DEFAULT NULL,
    subject    VARCHAR(180) DEFAULT NULL,
    message    TEXT NOT NULL,
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS settings (
    setting_key   VARCHAR(60) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
