-- Meeting platform business schema (MySQL 8+).
-- Laravel migrations in backend/database/migrations remain the source of truth.

CREATE TABLE roles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY roles_name_unique (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY users_email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_role_assignments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP NOT NULL,
    ended_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY user_role_assignments_user_ended_index (user_id, ended_at),
    KEY user_role_assignments_role_ended_index (role_id, ended_at),
    CONSTRAINT user_role_assignments_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT user_role_assignments_role_id_foreign
        FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE meetings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    creator_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    scheduled_at DATETIME NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'scheduled',
    started_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    livekit_room_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY meetings_livekit_room_name_unique (livekit_room_name),
    KEY meetings_status_index (status),
    CONSTRAINT meetings_creator_id_foreign
        FOREIGN KEY (creator_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE meeting_join_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    meeting_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY meeting_join_requests_meeting_user_unique (meeting_id, user_id),
    KEY meeting_join_requests_status_index (status),
    CONSTRAINT meeting_join_requests_meeting_id_foreign
        FOREIGN KEY (meeting_id) REFERENCES meetings (id) ON DELETE CASCADE,
    CONSTRAINT meeting_join_requests_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE departments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY departments_name_unique (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sectors (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY sectors_name_unique (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE grievances (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    sector_id BIGINT UNSIGNED NOT NULL,
    department_id BIGINT UNSIGNED NULL,
    created_by_id BIGINT UNSIGNED NULL,
    description TEXT NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'submitted',
    complained_at DATETIME NOT NULL,
    due_at DATETIME NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY grievances_status_index (status),
    KEY grievances_sector_status_index (sector_id, status),
    KEY grievances_due_at_index (due_at),
    CONSTRAINT grievances_sector_id_foreign FOREIGN KEY (sector_id) REFERENCES sectors (id) ON DELETE RESTRICT,
    CONSTRAINT grievances_department_id_foreign FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE SET NULL,
    CONSTRAINT grievances_created_by_id_foreign FOREIGN KEY (created_by_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE grievance_documents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    grievance_id BIGINT UNSIGNED NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(150) NULL,
    size BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    CONSTRAINT grievance_documents_grievance_id_foreign FOREIGN KEY (grievance_id) REFERENCES grievances (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE grievance_status_histories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    grievance_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(30) NOT NULL,
    changed_by_id BIGINT UNSIGNED NULL,
    changed_at DATETIME NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY grievance_status_histories_grievance_changed_index (grievance_id, changed_at),
    CONSTRAINT grievance_status_histories_grievance_id_foreign FOREIGN KEY (grievance_id) REFERENCES grievances (id) ON DELETE CASCADE,
    CONSTRAINT grievance_status_histories_changed_by_id_foreign FOREIGN KEY (changed_by_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE grievance_assignment_histories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    grievance_id BIGINT UNSIGNED NOT NULL,
    department_id BIGINT UNSIGNED NULL,
    changed_by_id BIGINT UNSIGNED NULL,
    changed_at DATETIME NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY grievance_assignment_histories_grievance_changed_index (grievance_id, changed_at),
    CONSTRAINT grievance_assignment_histories_grievance_id_foreign FOREIGN KEY (grievance_id) REFERENCES grievances (id) ON DELETE CASCADE,
    CONSTRAINT grievance_assignment_histories_department_id_foreign FOREIGN KEY (department_id) REFERENCES departments (id) ON DELETE SET NULL,
    CONSTRAINT grievance_assignment_histories_changed_by_id_foreign FOREIGN KEY (changed_by_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The roles migration seeds the built-in roles: admin and client.
-- A user's current role is the assignment whose ended_at is NULL.
-- Historical assignments are retained when the role changes.
