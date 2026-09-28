SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role ENUM('teacher', 'student') NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL DEFAULT '',
    login VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(11) NULL,
    parent_name VARCHAR(100) NULL,
    parent_phone VARCHAR(50) NULL,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role_deleted (role, deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recurring_lessons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    teacher_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,

    weekday TINYINT NOT NULL,
    start_time TIME NOT NULL,

    duration_minutes INT NOT NULL DEFAULT 60,

    active TINYINT(1) NOT NULL DEFAULT 1,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_recurring_teacher
        FOREIGN KEY (teacher_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_recurring_student
        FOREIGN KEY (student_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_recurring_student (student_id),
    INDEX idx_recurring_teacher (teacher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS lessons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL,
    schedule_slot_id BIGINT UNSIGNED NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    status ENUM('scheduled', 'completed', 'student_cancelled', 'teacher_cancelled') NOT NULL DEFAULT 'scheduled',
    meeting_url VARCHAR(1000) NULL,
    meeting_comment TEXT NULL,
    payment_status ENUM('unpaid', 'paid') NOT NULL DEFAULT 'unpaid',
    booked_at DATETIME NULL,
    cancelled_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_lessons_student FOREIGN KEY (student_id) REFERENCES users(id),
    CONSTRAINT fk_lessons_teacher FOREIGN KEY (teacher_id) REFERENCES users(id),
    CONSTRAINT fk_lessons_slot FOREIGN KEY (schedule_slot_id) REFERENCES schedule_slots(id) ON DELETE SET NULL,
    UNIQUE KEY uniq_active_slot (schedule_slot_id),
    INDEX idx_lessons_student_start (student_id, starts_at),
    INDEX idx_lessons_teacher_start (teacher_id, starts_at),
    INDEX idx_lessons_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS student_availability (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    schedule_slot_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_availability_student FOREIGN KEY (student_id) REFERENCES users(id),
    CONSTRAINT fk_availability_slot FOREIGN KEY (schedule_slot_id) REFERENCES schedule_slots(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_student_slot (student_id, schedule_slot_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homework (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lesson_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    student_comment TEXT NULL,
    teacher_feedback TEXT NULL,
    status ENUM('assigned', 'submitted', 'checked') NOT NULL DEFAULT 'assigned',
    submitted_at DATETIME NULL,
    checked_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_homework_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id),
    CONSTRAINT fk_homework_student FOREIGN KEY (student_id) REFERENCES users(id),
    INDEX idx_homework_student (student_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homework_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    homework_id BIGINT UNSIGNED NOT NULL,
    uploaded_by ENUM('teacher', 'student') NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL UNIQUE,
    mime_type VARCHAR(150) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_homework_files_homework FOREIGN KEY (homework_id) REFERENCES homework(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS teacher_notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    lesson_id BIGINT UNSIGNED NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_notes_student FOREIGN KEY (student_id) REFERENCES users(id),
    CONSTRAINT fk_notes_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
    INDEX idx_notes_student (student_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(500) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_notifications_user_read (user_id, is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lesson_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lesson_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    old_starts_at DATETIME NULL,
    new_starts_at DATETIME NULL,
    comment VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lesson_history_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    INDEX idx_lesson_history_lesson (lesson_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (role, first_name, last_name, login, password_hash, phone, parent_name, parent_phone)
SELECT 'teacher', 'Катя', '', 'Katy1009', '$2y$12$kvhi2aNjwfnwjHWkgcX.feFJCXChVWTsf4DFsoAIrzY46QgLlMa0i', NULL, NULL, NULL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE login = 'Katy1009');
