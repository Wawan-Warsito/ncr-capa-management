-- =====================================================
-- NCR CAPA Management System - Database Creation Script
-- PT. Topsystem Asia Base
-- Based on BAB I, II, III Requirements
-- =====================================================

-- Create Database
DROP DATABASE IF EXISTS ncr_capa_db;
CREATE DATABASE ncr_capa_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ncr_capa_db;

-- =====================================================
-- TABLE 1: departments
-- Represents 12 departments in PT. Topsystem
-- =====================================================
CREATE TABLE departments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL,
    department_code VARCHAR(10) NOT NULL UNIQUE,
    manager_user_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dept_code (department_code),
    INDEX idx_dept_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 2: roles
-- Role-based access control
-- =====================================================
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    permissions JSON NULL,
    level INT DEFAULT 1 COMMENT '1=Staff, 2=Supervisor, 3=Manager, 4=QC Manager, 5=Admin',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 3: users
-- All system users from 12 departments
-- =====================================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    department_id BIGINT UNSIGNED NOT NULL,
    phone VARCHAR(20) NULL,
    employee_id VARCHAR(50) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,
    INDEX idx_user_email (email),
    INDEX idx_user_dept (department_id),
    INDEX idx_user_role (role_id),
    INDEX idx_user_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key for department manager
ALTER TABLE departments 
ADD CONSTRAINT fk_dept_manager 
FOREIGN KEY (manager_user_id) REFERENCES users(id) ON DELETE SET NULL;

-- =====================================================
-- TABLE 4: defect_categories
-- Master data for defect types
-- =====================================================
CREATE TABLE defect_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    category_code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 5: severity_levels
-- Master data for severity classification
-- =====================================================
CREATE TABLE severity_levels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    level_name VARCHAR(50) NOT NULL,
    level_code VARCHAR(20) NOT NULL UNIQUE,
    priority INT NOT NULL,
    color_code VARCHAR(7) NULL COMMENT 'Hex color for UI',
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 6: disposition_methods
-- Master data for NCR disposition
-- =====================================================
CREATE TABLE disposition_methods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    method_name VARCHAR(50) NOT NULL,
    method_code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT NULL,
    requires_approval BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 7: ncrs (Non-Conformance Reports)
-- Core table with 45+ fields as per BAB III
-- =====================================================
CREATE TABLE ncrs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ncr_number VARCHAR(50) NOT NULL UNIQUE COMMENT 'Format: YY.PXX-DDD-NN',
    
    -- Project Information
    order_number VARCHAR(50) NULL,
    project_name VARCHAR(200) NULL,
    customer_name VARCHAR(200) NULL,
    product_description TEXT NULL,
    drawing_number VARCHAR(100) NULL,
    material_specification VARCHAR(200) NULL,
    
    -- NCR Basic Info
    date_found DATE NOT NULL,
    location_found VARCHAR(200) NULL,
    quantity_affected INT NULL,
    
    -- Department Information
    finder_dept_id BIGINT UNSIGNED NOT NULL,
    receiver_dept_id BIGINT UNSIGNED NOT NULL,
    created_by_user_id BIGINT UNSIGNED NOT NULL,
    
    -- Defect Information
    defect_category_id BIGINT UNSIGNED NOT NULL,
    defect_description TEXT NOT NULL,
    defect_location TEXT NULL,
    severity_level_id BIGINT UNSIGNED NOT NULL,
    
    -- Disposition
    disposition_method_id BIGINT UNSIGNED NULL,
    disposition_details TEXT NULL,
    disposition_approved_by BIGINT UNSIGNED NULL,
    disposition_approved_at TIMESTAMP NULL,
    
    -- Immediate Actions
    immediate_action TEXT NULL,
    containment_action TEXT NULL,
    
    -- Cost Impact
    estimated_cost DECIMAL(15,2) NULL,
    actual_cost DECIMAL(15,2) NULL,
    
    -- Status & Workflow
    status ENUM(
        'Draft',
        'Submitted',
        'Pending_Finder_Approval',
        'Finder_Approved',
        'Pending_QC_Registration',
        'QC_Registered',
        'Pending_ASME_Review',
        'ASME_Approved',
        'Sent_To_Receiver',
        'Assigned_To_PIC',
        'CAPA_In_Progress',
        'Pending_Verification',
        'Verified',
        'Closed',
        'Rejected',
        'Cancelled'
    ) DEFAULT 'Draft',
    
    -- Approval Tracking
    finder_manager_id BIGINT UNSIGNED NULL,
    finder_approved_at TIMESTAMP NULL,
    finder_approval_remarks TEXT NULL,
    
    qc_manager_id BIGINT UNSIGNED NULL,
    qc_registered_at TIMESTAMP NULL,
    qc_registration_remarks TEXT NULL,
    
    receiver_manager_id BIGINT UNSIGNED NULL,
    receiver_assigned_at TIMESTAMP NULL,
    receiver_assignment_remarks TEXT NULL,
    
    assigned_pic_id BIGINT UNSIGNED NULL,
    pic_assigned_at TIMESTAMP NULL,
    
    -- ASME Specific
    is_asme_project BOOLEAN DEFAULT FALSE,
    asme_code_reference VARCHAR(100) NULL,
    ncr_coordinator_id BIGINT UNSIGNED NULL,
    asme_reviewed_at TIMESTAMP NULL,
    asme_review_remarks TEXT NULL,
    
    -- Verification
    verified_by_user_id BIGINT UNSIGNED NULL,
    verified_at TIMESTAMP NULL,
    verification_remarks TEXT NULL,
    effectiveness_verified BOOLEAN DEFAULT FALSE,
    
    -- Closure
    closed_by_user_id BIGINT UNSIGNED NULL,
    closed_at TIMESTAMP NULL,
    closure_remarks TEXT NULL,
    
    -- Recurrence Tracking
    is_recurring BOOLEAN DEFAULT FALSE,
    parent_ncr_id BIGINT UNSIGNED NULL,
    recurrence_count INT DEFAULT 0,
    
    -- Timestamps
    submitted_at TIMESTAMP NULL,
    target_closure_date DATE NULL,
    actual_closure_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Foreign Keys
    FOREIGN KEY (finder_dept_id) REFERENCES departments(id) ON DELETE RESTRICT,
    FOREIGN KEY (receiver_dept_id) REFERENCES departments(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (defect_category_id) REFERENCES defect_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (severity_level_id) REFERENCES severity_levels(id) ON DELETE RESTRICT,
    FOREIGN KEY (disposition_method_id) REFERENCES disposition_methods(id) ON DELETE SET NULL,
    FOREIGN KEY (finder_manager_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (qc_manager_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (receiver_manager_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_pic_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (ncr_coordinator_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (verified_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (closed_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_ncr_id) REFERENCES ncrs(id) ON DELETE SET NULL,
    
    -- Indexes for Performance
    INDEX idx_ncr_number (ncr_number),
    INDEX idx_ncr_status (status),
    INDEX idx_ncr_finder_dept (finder_dept_id, status),
    INDEX idx_ncr_receiver_dept (receiver_dept_id, status),
    INDEX idx_ncr_date_found (date_found),
    INDEX idx_ncr_created_by (created_by_user_id),
    INDEX idx_ncr_assigned_pic (assigned_pic_id),
    INDEX idx_ncr_defect_category (defect_category_id),
    INDEX idx_ncr_severity (severity_level_id),
    INDEX idx_ncr_asme (is_asme_project),
    INDEX idx_ncr_recurring (is_recurring),
    FULLTEXT INDEX idx_ncr_fulltext (defect_description, product_description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 8: capas (Corrective and Preventive Actions)
-- =====================================================
CREATE TABLE capas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    capa_number VARCHAR(50) NOT NULL UNIQUE,
    ncr_id BIGINT UNSIGNED NOT NULL,
    
    -- Root Cause Analysis
    rca_method ENUM('5_Why', 'Fishbone', 'Other') DEFAULT '5_Why',
    root_cause_summary TEXT NOT NULL,
    
    -- 5 Why Analysis
    why_1 TEXT NULL,
    why_2 TEXT NULL,
    why_3 TEXT NULL,
    why_4 TEXT NULL,
    why_5 TEXT NULL,
    
    -- Fishbone Analysis
    fishbone_people TEXT NULL,
    fishbone_process TEXT NULL,
    fishbone_material TEXT NULL,
    fishbone_equipment TEXT NULL,
    fishbone_environment TEXT NULL,
    fishbone_measurement TEXT NULL,
    
    -- Action Plans
    corrective_action_plan TEXT NOT NULL,
    preventive_action_plan TEXT NULL,
    expected_outcome TEXT NULL,
    
    -- Assignment
    assigned_pic_id BIGINT UNSIGNED NOT NULL,
    assigned_by_user_id BIGINT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP NULL,
    target_completion_date DATE NOT NULL,
    actual_completion_date DATE NULL,
    
    -- Progress Tracking
    progress_percentage INT DEFAULT 0 CHECK (progress_percentage >= 0 AND progress_percentage <= 100),
    current_status ENUM(
        'Draft',
        'Planned',
        'In_Progress',
        'Pending_Verification',
        'Verified',
        'Closed',
        'Rejected'
    ) DEFAULT 'Draft',
    
    -- Verification
    effectiveness_verified BOOLEAN DEFAULT FALSE,
    verified_by_user_id BIGINT UNSIGNED NULL,
    verified_at TIMESTAMP NULL,
    verification_method TEXT NULL,
    verification_results TEXT NULL,
    
    -- Monitoring Period
    monitoring_start_date DATE NULL,
    monitoring_end_date DATE NULL,
    monitoring_notes TEXT NULL,
    
    -- Closure
    closed_by_user_id BIGINT UNSIGNED NULL,
    closed_at TIMESTAMP NULL,
    closure_remarks TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Foreign Keys
    FOREIGN KEY (ncr_id) REFERENCES ncrs(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_pic_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (assigned_by_user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (verified_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (closed_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_capa_number (capa_number),
    INDEX idx_capa_ncr (ncr_id),
    INDEX idx_capa_pic (assigned_pic_id),
    INDEX idx_capa_status (current_status),
    INDEX idx_capa_target_date (target_completion_date),
    INDEX idx_capa_progress (progress_percentage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 9: ncr_attachments
-- File uploads for NCR evidence
-- =====================================================
CREATE TABLE ncr_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ncr_id BIGINT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL COMMENT 'Size in bytes',
    file_type VARCHAR(100) NOT NULL,
    mime_type VARCHAR(100) NULL,
    attachment_type ENUM('Evidence', 'Drawing', 'Photo', 'Document', 'Other') DEFAULT 'Evidence',
    description TEXT NULL,
    uploaded_by_user_id BIGINT UNSIGNED NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (ncr_id) REFERENCES ncrs(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by_user_id) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_attachment_ncr (ncr_id),
    INDEX idx_attachment_type (attachment_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 10: capa_attachments
-- File uploads for CAPA evidence
-- =====================================================
CREATE TABLE capa_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    capa_id BIGINT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    mime_type VARCHAR(100) NULL,
    attachment_type ENUM('Implementation_Evidence', 'Verification_Evidence', 'Document', 'Other') DEFAULT 'Implementation_Evidence',
    description TEXT NULL,
    uploaded_by_user_id BIGINT UNSIGNED NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (capa_id) REFERENCES capas(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by_user_id) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_capa_attachment (capa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 11: capa_progress_logs
-- Milestone tracking for CAPA execution
-- =====================================================
CREATE TABLE capa_progress_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    capa_id BIGINT UNSIGNED NOT NULL,
    progress_percentage INT NOT NULL CHECK (progress_percentage >= 0 AND progress_percentage <= 100),
    milestone_description TEXT NOT NULL,
    activities_completed TEXT NULL,
    challenges_encountered TEXT NULL,
    next_steps TEXT NULL,
    logged_by_user_id BIGINT UNSIGNED NOT NULL,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (capa_id) REFERENCES capas(id) ON DELETE CASCADE,
    FOREIGN KEY (logged_by_user_id) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_progress_capa (capa_id),
    INDEX idx_progress_date (logged_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 12: comments
-- Discussion threads for NCR and CAPA
-- =====================================================
CREATE TABLE comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    commentable_type VARCHAR(50) NOT NULL COMMENT 'NCR or CAPA',
    commentable_id BIGINT UNSIGNED NOT NULL,
    parent_comment_id BIGINT UNSIGNED NULL,
    comment_text TEXT NOT NULL,
    is_internal BOOLEAN DEFAULT FALSE COMMENT 'Internal notes vs external communication',
    commented_by_user_id BIGINT UNSIGNED NOT NULL,
    commented_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_edited BOOLEAN DEFAULT FALSE,
    edited_at TIMESTAMP NULL,
    is_deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (parent_comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    FOREIGN KEY (commented_by_user_id) REFERENCES users(id) ON DELETE RESTRICT,
    
    INDEX idx_comment_type_id (commentable_type, commentable_id),
    INDEX idx_comment_user (commented_by_user_id),
    INDEX idx_comment_date (commented_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 13: activity_logs
-- Complete audit trail for compliance
-- =====================================================
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    entity_type VARCHAR(50) NOT NULL COMMENT 'NCR, CAPA, User, etc',
    entity_id BIGINT UNSIGNED NOT NULL,
    action_type VARCHAR(50) NOT NULL COMMENT 'Create, Update, Delete, Approve, Reject, etc',
    action_description TEXT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_log_entity (entity_type, entity_id),
    INDEX idx_log_user (user_id),
    INDEX idx_log_action (action_type),
    INDEX idx_log_date (performed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 14: notifications
-- Email and in-app notifications
-- =====================================================
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipient_user_id BIGINT UNSIGNED NOT NULL,
    notification_type VARCHAR(50) NOT NULL COMMENT 'NCR_Created, Approval_Required, CAPA_Assigned, etc',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    related_entity_type VARCHAR(50) NULL,
    related_entity_id BIGINT UNSIGNED NULL,
    action_url VARCHAR(500) NULL,
    priority ENUM('Low', 'Normal', 'High', 'Urgent') DEFAULT 'Normal',
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    is_email_sent BOOLEAN DEFAULT FALSE,
    email_sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (recipient_user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_notif_recipient (recipient_user_id, is_read),
    INDEX idx_notif_type (notification_type),
    INDEX idx_notif_entity (related_entity_type, related_entity_id),
    INDEX idx_notif_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 15: settings
-- System configuration
-- =====================================================
CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    setting_type VARCHAR(50) DEFAULT 'string' COMMENT 'string, integer, boolean, json',
    category VARCHAR(50) NULL COMMENT 'General, Email, Notification, etc',
    description TEXT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    updated_by_user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (updated_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_setting_key (setting_key),
    INDEX idx_setting_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 16: password_reset_tokens
-- Laravel default for password reset
-- =====================================================
CREATE TABLE password_reset_tokens (
    email VARCHAR(100) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 17: failed_jobs
-- Laravel default for queue management
-- =====================================================
CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLE 18: personal_access_tokens
-- Laravel Sanctum for API authentication
-- =====================================================
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_tokenable (tokenable_type, tokenable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- END OF DATABASE SCHEMA
-- Total Tables: 18
-- Core Tables: 15 (NCR-CAPA specific)
-- Laravel Default: 3 (password_reset, failed_jobs, personal_access_tokens)
-- =====================================================
