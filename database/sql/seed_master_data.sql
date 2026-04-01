-- =====================================================
-- NCR CAPA Management System - Master Data Seeding
-- PT. Topsystem Asia Base
-- =====================================================

USE ncr_capa_db;

-- =====================================================
-- SEED ROLES
-- Based on organizational hierarchy
-- =====================================================
INSERT INTO roles (role_name, display_name, description, level, permissions) VALUES
('admin', 'System Administrator', 'Full system access for IT Admin', 5, '["all"]'),
('qc_manager', 'QC Manager', 'Company-wide QC oversight and final approval', 4, '["view_all_ncr", "register_ncr", "close_ncr", "view_company_dashboard", "manage_reports"]'),
('ncr_coordinator', 'NCR Coordinator', 'ASME project coordinator', 4, '["review_asme_ncr", "approve_asme_compliance"]'),
('department_manager', 'Department Manager', 'Department head with full departmental authority', 3, '["approve_ncr", "assign_capa", "view_dept_dashboard", "manage_dept_users"]'),
('supervisor', 'Supervisor', 'Team supervisor with approval authority', 2, '["approve_ncr_level1", "assign_tasks", "view_team_ncr"]'),
('qc_inspector', 'QC Inspector', 'Quality control inspector', 2, '["create_ncr", "verify_capa", "conduct_inspection"]'),
('qc_staff', 'QC Staff', 'QC administrative staff', 1, '["create_ncr", "update_ncr", "view_ncr"]'),
('staff', 'Staff', 'General employee', 1, '["create_ncr", "view_own_ncr", "update_assigned_capa"]');

-- =====================================================
-- SEED DEPARTMENTS
-- 12 departments as per BAB I
-- =====================================================
INSERT INTO departments (department_name, department_code, description, is_active) VALUES
('Quality Control', 'QC', 'Quality assurance and inspection department', TRUE),
('Production', 'PROD', 'Manufacturing and fabrication department', TRUE),
('Design Engineering', 'ENG', 'Design and technical engineering department', TRUE),
('Warehouse', 'WH', 'Material storage and inventory management', TRUE),
('Sales', 'SALES', 'Sales and customer relations', TRUE),
('Procurement', 'PROC', 'Purchasing and supplier management', TRUE),
('Maintenance', 'MAINT', 'Equipment maintenance and repair', TRUE),
('Electrical Engineering', 'ELEC', 'Electrical systems and instrumentation', TRUE),
('Finance', 'FIN', 'Financial management and accounting', TRUE),
('Human Resources', 'HR', 'Human resources and personnel management', TRUE),
('IT & General Affairs', 'IT-GA', 'Information technology and general administration', TRUE),
('Export Import', 'EXIM', 'Export-import and customs documentation', TRUE);

-- =====================================================
-- SEED DEFECT CATEGORIES
-- Based on historical NCR data analysis
-- =====================================================
INSERT INTO defect_categories (category_name, category_code, description, is_active) VALUES
('Welding Defect', 'WELD', 'Welding-related issues: undercut, porosity, lack of fusion, crack', TRUE),
('Dimensional Out-of-Spec', 'DIM', 'Dimensional deviations from drawing specifications', TRUE),
('Material Non-Conformance', 'MAT', 'Material defects or specification mismatch', TRUE),
('Surface Defect', 'SURF', 'Surface finish issues: scratches, dents, contamination', TRUE),
('Wrong Specification', 'SPEC', 'Incorrect specifications or design errors', TRUE),
('Documentation Error', 'DOC', 'Missing or incorrect documentation', TRUE),
('Assembly Issue', 'ASSY', 'Assembly or fit-up problems', TRUE),
('Coating/Painting Defect', 'COAT', 'Coating or painting quality issues', TRUE),
('Leak/Pressure Test Failure', 'LEAK', 'Failed pressure or leak testing', TRUE),
('Customer Complaint', 'CUST', 'Customer-reported quality issues', TRUE),
('Supplier Defect', 'SUPP', 'Supplier-provided material or component defects', TRUE),
('Process Non-Compliance', 'PROC', 'Process procedure not followed', TRUE),
('Equipment Malfunction', 'EQUIP', 'Equipment-related quality issues', TRUE),
('Other', 'OTHER', 'Other defect types not categorized above', TRUE);

-- =====================================================
-- SEED SEVERITY LEVELS
-- Critical, Major, Minor classification
-- =====================================================
INSERT INTO severity_levels (level_name, level_code, priority, color_code, description, is_active) VALUES
('Critical', 'CRIT', 1, '#DC2626', 'Safety hazard or complete functionality failure - immediate action required', TRUE),
('Major', 'MAJ', 2, '#F59E0B', 'Significant deviation requiring correction before delivery', TRUE),
('Minor', 'MIN', 3, '#10B981', 'Cosmetic or documentation issue with minimal impact', TRUE);

-- =====================================================
-- SEED DISPOSITION METHODS
-- How to handle non-conforming items
-- =====================================================
INSERT INTO disposition_methods (method_name, method_code, description, requires_approval, is_active) VALUES
('Rework', 'REWORK', 'Repair the defect to meet specifications', FALSE, TRUE),
('Repair', 'REPAIR', 'Fix the defect with documented repair procedure', TRUE, TRUE),
('Use As Is', 'USE_AS_IS', 'Accept with concession - requires special approval', TRUE, TRUE),
('Scrap', 'SCRAP', 'Discard the non-conforming item', TRUE, TRUE),
('Return to Supplier', 'RTS', 'Return defective material to supplier', FALSE, TRUE),
('Regrading', 'REGRADE', 'Downgrade to lower specification', TRUE, TRUE),
('Sorting', 'SORT', 'Sort and segregate conforming from non-conforming', FALSE, TRUE);

-- =====================================================
-- SEED DEFAULT USERS
-- Initial users for system setup
-- Note: Password is 'password' hashed with bcrypt
-- =====================================================

-- First, create admin user without department constraint
INSERT INTO users (name, email, password, role_id, department_id, employee_id, is_active) VALUES
('System Administrator', 'admin@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 11, 'ADM001', TRUE);

-- QC Department Users
INSERT INTO users (name, email, password, role_id, department_id, employee_id, is_active) VALUES
('Budi Santoso', 'qc.manager@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 1, 'QC001', TRUE),
('Siti Nurhaliza', 'qc.supervisor@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, 1, 'QC002', TRUE),
('Ahmad Wijaya', 'qc.inspector1@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 1, 'QC003', TRUE),
('Dewi Lestari', 'qc.inspector2@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 1, 'QC004', TRUE),
('Rina Kusuma', 'qc.staff1@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 7, 1, 'QC005', TRUE),
('Andi Pratama', 'qc.staff2@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 7, 1, 'QC006', TRUE),
('Hendra Gunawan', 'ncr.coordinator@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1, 'QC007', TRUE);

-- Production Department Users
INSERT INTO users (name, email, password, role_id, department_id, employee_id, is_active) VALUES
('Joko Widodo', 'prod.manager@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 2, 'PROD001', TRUE),
('Bambang Suryanto', 'prod.supervisor@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, 2, 'PROD002', TRUE),
('Agus Setiawan', 'prod.staff1@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8, 2, 'PROD003', TRUE),
('Rudi Hartono', 'prod.staff2@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8, 2, 'PROD004', TRUE);

-- Engineering Department Users
INSERT INTO users (name, email, password, role_id, department_id, employee_id, is_active) VALUES
('Ir. Sutanto', 'eng.manager@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 3, 'ENG001', TRUE),
('Dian Purnama', 'eng.supervisor@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, 3, 'ENG002', TRUE),
('Eko Prasetyo', 'eng.staff@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8, 3, 'ENG003', TRUE);

-- Warehouse Department Users
INSERT INTO users (name, email, password, role_id, department_id, employee_id, is_active) VALUES
('Wawan Setiawan', 'wh.manager@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 4, 'WH001', TRUE),
('Yudi Hermawan', 'wh.supervisor@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, 4, 'WH002', TRUE),
('Tono Sugiarto', 'wh.staff@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8, 4, 'WH003', TRUE);

-- Sales Department Users
INSERT INTO users (name, email, password, role_id, department_id, employee_id, is_active) VALUES
('Linda Wijaya', 'sales.manager@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 5, 'SALES001', TRUE),
('Rina Marlina', 'sales.staff@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8, 5, 'SALES002', TRUE);

-- Procurement Department Users
INSERT INTO users (name, email, password, role_id, department_id, employee_id, is_active) VALUES
('Hadi Susanto', 'proc.manager@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 6, 'PROC001', TRUE),
('Sari Indah', 'proc.staff@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8, 6, 'PROC002', TRUE);

-- Maintenance Department Users
INSERT INTO users (name, email, password, role_id, department_id, employee_id, is_active) VALUES
('Teguh Santoso', 'maint.manager@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 7, 'MAINT001', TRUE),
('Budi Cahyono', 'maint.staff@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8, 7, 'MAINT002', TRUE);

-- Electrical Department Users
INSERT INTO users (name, email, password, role_id, department_id, employee_id, is_active) VALUES
('Ir. Andi Wijaya', 'elec.manager@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 8, 'ELEC001', TRUE),
('Dedi Kurniawan', 'elec.staff@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8, 8, 'ELEC002', TRUE);

-- Finance Department Users
INSERT INTO users (name, email, password, role_id, department_id, employee_id, is_active) VALUES
('Susi Rahayu', 'fin.manager@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 9, 'FIN001', TRUE),
('Ani Suryani', 'fin.staff@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8, 9, 'FIN002', TRUE);

-- HR Department Users
INSERT INTO users (name, email, password, role_id, department_id, employee_id, is_active) VALUES
('Ratna Sari', 'hr.manager@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 10, 'HR001', TRUE),
('Maya Kusuma', 'hr.staff@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8, 10, 'HR002', TRUE);

-- IT-GA Department Users
INSERT INTO users (name, email, password, role_id, department_id, employee_id, is_active) VALUES
('Fikri Rahman', 'it.manager@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 11, 'IT001', TRUE),
('Doni Saputra', 'it.staff@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8, 11, 'IT002', TRUE);

-- EXIM Department Users
INSERT INTO users (name, email, password, role_id, department_id, employee_id, is_active) VALUES
('Indra Gunawan', 'exim.manager@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 12, 'EXIM001', TRUE),
('Lina Marlina', 'exim.staff@topsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8, 12, 'EXIM002', TRUE);

-- Update department managers
UPDATE departments SET manager_user_id = 2 WHERE department_code = 'QC';
UPDATE departments SET manager_user_id = 9 WHERE department_code = 'PROD';
UPDATE departments SET manager_user_id = 13 WHERE department_code = 'ENG';
UPDATE departments SET manager_user_id = 16 WHERE department_code = 'WH';
UPDATE departments SET manager_user_id = 19 WHERE department_code = 'SALES';
UPDATE departments SET manager_user_id = 21 WHERE department_code = 'PROC';
UPDATE departments SET manager_user_id = 23 WHERE department_code = 'MAINT';
UPDATE departments SET manager_user_id = 25 WHERE department_code = 'ELEC';
UPDATE departments SET manager_user_id = 27 WHERE department_code = 'FIN';
UPDATE departments SET manager_user_id = 29 WHERE department_code = 'HR';
UPDATE departments SET manager_user_id = 31 WHERE department_code = 'IT-GA';
UPDATE departments SET manager_user_id = 33 WHERE department_code = 'EXIM';

-- =====================================================
-- SEED SYSTEM SETTINGS
-- =====================================================
INSERT INTO settings (setting_key, setting_value, setting_type, category, description, is_public) VALUES
('company_name', 'PT. Topsystem Asia Base', 'string', 'General', 'Company name', TRUE),
('company_address', 'Kawasan Industri Bekasi, Indonesia', 'string', 'General', 'Company address', TRUE),
('ncr_number_format', 'YY.PXX-DDD-NN', 'string', 'NCR', 'NCR number format template', FALSE),
('capa_number_format', 'CAPA-YY-NNNN', 'string', 'CAPA', 'CAPA number format template', FALSE),
('approval_timeout_hours', '24', 'integer', 'Workflow', 'Hours before approval reminder sent', FALSE),
('capa_deadline_warning_days', '3', 'integer', 'CAPA', 'Days before deadline to send warning', FALSE),
('max_file_upload_size_mb', '5', 'integer', 'Upload', 'Maximum file size in MB', FALSE),
('allowed_file_types', 'jpg,jpeg,png,pdf,doc,docx,xls,xlsx', 'string', 'Upload', 'Allowed file extensions', FALSE),
('email_notifications_enabled', 'true', 'boolean', 'Email', 'Enable email notifications', FALSE),
('dashboard_refresh_interval', '300', 'integer', 'Dashboard', 'Dashboard auto-refresh in seconds', FALSE),
('iso_certification', 'ISO 9001:2015', 'string', 'Compliance', 'ISO certification standard', TRUE),
('asme_certification', 'ASME Section VIII & IX', 'string', 'Compliance', 'ASME certification', TRUE);

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

-- Count records per table
SELECT 'Roles' as TableName, COUNT(*) as RecordCount FROM roles
UNION ALL
SELECT 'Departments', COUNT(*) FROM departments
UNION ALL
SELECT 'Users', COUNT(*) FROM users
UNION ALL
SELECT 'Defect Categories', COUNT(*) FROM defect_categories
UNION ALL
SELECT 'Severity Levels', COUNT(*) FROM severity_levels
UNION ALL
SELECT 'Disposition Methods', COUNT(*) FROM disposition_methods
UNION ALL
SELECT 'Settings', COUNT(*) FROM settings;

-- Display department structure
SELECT 
    d.department_name,
    d.department_code,
    u.name as manager_name,
    u.email as manager_email,
    (SELECT COUNT(*) FROM users WHERE department_id = d.id) as total_users
FROM departments d
LEFT JOIN users u ON d.manager_user_id = u.id
ORDER BY d.id;

-- =====================================================
-- END OF MASTER DATA SEEDING
-- =====================================================
