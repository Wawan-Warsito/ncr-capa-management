-- =====================================================
-- NCR CAPA Management System - Excel Data Migration
-- Migrate 216 NCR records from Excel VBA to MySQL
-- PT. Topsystem Asia Base
-- =====================================================

USE ncr_capa_db;

-- =====================================================
-- IMPORTANT NOTES:
-- =====================================================
-- 1. This is a TEMPLATE script for migrating Excel data
-- 2. Actual data should be exported from Excel to CSV first
-- 3. Use LOAD DATA INFILE or manual INSERT statements
-- 4. Verify data mapping before execution
-- 5. Backup database before running migration
-- =====================================================

-- =====================================================
-- STEP 1: Create Temporary Staging Table
-- =====================================================
DROP TABLE IF EXISTS ncr_staging;
CREATE TABLE ncr_staging (
    excel_row_number INT,
    ncr_number_excel VARCHAR(50),
    date_found_excel VARCHAR(50),
    order_number_excel VARCHAR(50),
    project_name_excel VARCHAR(200),
    customer_name_excel VARCHAR(200),
    finder_dept_name VARCHAR(100),
    receiver_dept_name VARCHAR(100),
    defect_category_name VARCHAR(100),
    defect_description_excel TEXT,
    severity_level_name VARCHAR(50),
    disposition_method_name VARCHAR(50),
    status_excel VARCHAR(50),
    created_by_name VARCHAR(100),
    -- Add more columns as needed based on Excel structure
    raw_data TEXT COMMENT 'Full row data for reference'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 2: Load Data from CSV Export
-- =====================================================
-- Option A: Using LOAD DATA INFILE (if CSV available)
-- LOAD DATA INFILE 'C:/path/to/ncr_export.csv'
-- INTO TABLE ncr_staging
-- FIELDS TERMINATED BY ','
-- ENCLOSED BY '"'
-- LINES TERMINATED BY '\n'
-- IGNORE 1 ROWS;

-- Option B: Manual INSERT for sample data
-- This is example data structure - replace with actual data
INSERT INTO ncr_staging (
    excel_row_number,
    ncr_number_excel,
    date_found_excel,
    order_number_excel,
    project_name_excel,
    customer_name_excel,
    finder_dept_name,
    receiver_dept_name,
    defect_category_name,
    defect_description_excel,
    severity_level_name,
    disposition_method_name,
    status_excel,
    created_by_name
) VALUES
-- Sample Record 1 (2020)
(1, '20.P001-QC-001', '2020-01-15', 'ORD-2020-001', 'CIP Tank 5000L', 'PT. Indofood', 
 'Quality Control', 'Production', 'Welding Defect', 
 'Undercut detected on longitudinal weld seam, depth 2mm exceeds acceptance criteria 1mm max per WPS-001',
 'Major', 'Rework', 'Closed', 'Ahmad Wijaya'),

-- Sample Record 2 (2020)
(2, '20.P002-PROD-001', '2020-02-20', 'ORD-2020-002', 'Mixing Tank 3000L', 'PT. Unilever',
 'Production', 'Warehouse', 'Material Non-Conformance',
 'SS316L plate material certificate shows carbon content 0.035% exceeds spec 0.030% max',
 'Critical', 'Return to Supplier', 'Closed', 'Bambang Suryanto'),

-- Sample Record 3 (2021)
(3, '21.P005-ENG-001', '2021-03-10', 'ORD-2021-005', 'Pressure Vessel 10000L', 'PT. Pertamina',
 'Design Engineering', 'Design Engineering', 'Wrong Specification',
 'Drawing DWG-005-Rev1 shows nozzle size DN50 but customer PO specifies DN80',
 'Major', 'Repair', 'Closed', 'Dian Purnama'),

-- Sample Record 4 (2022) - Year with spike (78 NCRs)
(4, '22.P010-QC-015', '2022-05-15', 'ORD-2022-010', 'Storage Tank 8000L', 'PT. Nestle',
 'Quality Control', 'Production', 'Dimensional Out-of-Spec',
 'Tank height measured 2450mm vs drawing requirement 2500mm, deviation -50mm exceeds tolerance ±10mm',
 'Major', 'Rework', 'Closed', 'Ahmad Wijaya'),

-- Sample Record 5 (2022)
(5, '22.P015-WH-003', '2022-06-20', 'ORD-2022-015', 'Fermentation Vessel', 'PT. Biofarma',
 'Warehouse', 'Procurement', 'Material Non-Conformance',
 'Gasket material received is EPDM instead of specified Viton per material requisition',
 'Minor', 'Return to Supplier', 'Closed', 'Yudi Hermawan'),

-- Sample Record 6 (2023)
(6, '23.P020-SALES-001', '2023-07-10', 'ORD-2023-020', 'Heat Exchanger', 'PT. Chandra Asri',
 'Sales', 'Production', 'Customer Complaint',
 'Customer reports surface scratches on polished finish, Ra value exceeds 0.8µm specification',
 'Major', 'Repair', 'Closed', 'Rina Marlina'),

-- Sample Record 7 (2024)
(7, '24.P025-PROD-010', '2024-08-05', 'ORD-2024-025', 'Distillation Column', 'PT. Krakatau Steel',
 'Production', 'Maintenance', 'Equipment Malfunction',
 'Welding machine WM-03 producing inconsistent arc, causing porosity in weld bead',
 'Critical', 'Scrap', 'Closed', 'Agus Setiawan'),

-- Sample Record 8 (2025)
(8, '25.P030-QC-005', '2025-01-15', 'ORD-2025-030', 'Reactor Vessel ASME', 'PT. Petrokimia',
 'Quality Control', 'Production', 'Welding Defect',
 'RT examination reveals linear indication 25mm length in circumferential weld, exceeds ASME Section VIII acceptance',
 'Critical', 'Repair', 'In Progress', 'Dewi Lestari');

-- Add more INSERT statements for remaining 208 records...
-- Total should be 216 records as per BAB I

-- =====================================================
-- STEP 3: Data Validation & Mapping
-- =====================================================

-- Check for unmapped departments
SELECT DISTINCT finder_dept_name, 'Finder' as dept_type
FROM ncr_staging
WHERE finder_dept_name NOT IN (SELECT department_name FROM departments)
UNION
SELECT DISTINCT receiver_dept_name, 'Receiver'
FROM ncr_staging
WHERE receiver_dept_name NOT IN (SELECT department_name FROM departments);

-- Check for unmapped defect categories
SELECT DISTINCT defect_category_name
FROM ncr_staging
WHERE defect_category_name NOT IN (SELECT category_name FROM defect_categories);

-- Check for unmapped severity levels
SELECT DISTINCT severity_level_name
FROM ncr_staging
WHERE severity_level_name NOT IN (SELECT level_name FROM severity_levels);

-- Check for unmapped disposition methods
SELECT DISTINCT disposition_method_name
FROM ncr_staging
WHERE disposition_method_name NOT IN (SELECT method_name FROM disposition_methods);

-- =====================================================
-- STEP 4: Migrate to NCRs Table
-- =====================================================

INSERT INTO ncrs (
    ncr_number,
    date_found,
    order_number,
    project_name,
    customer_name,
    finder_dept_id,
    receiver_dept_id,
    created_by_user_id,
    defect_category_id,
    defect_description,
    severity_level_id,
    disposition_method_id,
    status,
    submitted_at,
    finder_approved_at,
    qc_registered_at,
    closed_at,
    created_at,
    updated_at
)
SELECT 
    s.ncr_number_excel,
    STR_TO_DATE(s.date_found_excel, '%Y-%m-%d'),
    s.order_number_excel,
    s.project_name_excel,
    s.customer_name_excel,
    
    -- Map finder department
    (SELECT id FROM departments WHERE department_name = s.finder_dept_name LIMIT 1),
    
    -- Map receiver department
    (SELECT id FROM departments WHERE department_name = s.receiver_dept_name LIMIT 1),
    
    -- Map creator (default to QC Inspector if not found)
    COALESCE(
        (SELECT id FROM users WHERE name = s.created_by_name LIMIT 1),
        (SELECT id FROM users WHERE role_id = 6 LIMIT 1)
    ),
    
    -- Map defect category
    (SELECT id FROM defect_categories WHERE category_name = s.defect_category_name LIMIT 1),
    
    s.defect_description_excel,
    
    -- Map severity level
    (SELECT id FROM severity_levels WHERE level_name = s.severity_level_name LIMIT 1),
    
    -- Map disposition method
    (SELECT id FROM disposition_methods WHERE method_name = s.disposition_method_name LIMIT 1),
    
    -- Map status
    CASE 
        WHEN s.status_excel = 'Closed' THEN 'Closed'
        WHEN s.status_excel = 'In Progress' THEN 'CAPA_In_Progress'
        WHEN s.status_excel = 'Pending' THEN 'Pending_Verification'
        ELSE 'Closed'
    END,
    
    -- Timestamps (estimate based on date_found)
    STR_TO_DATE(s.date_found_excel, '%Y-%m-%d'),
    DATE_ADD(STR_TO_DATE(s.date_found_excel, '%Y-%m-%d'), INTERVAL 1 DAY),
    DATE_ADD(STR_TO_DATE(s.date_found_excel, '%Y-%m-%d'), INTERVAL 2 DAY),
    CASE 
        WHEN s.status_excel = 'Closed' THEN DATE_ADD(STR_TO_DATE(s.date_found_excel, '%Y-%m-%d'), INTERVAL 30 DAY)
        ELSE NULL
    END,
    STR_TO_DATE(s.date_found_excel, '%Y-%m-%d'),
    NOW()
    
FROM ncr_staging s
WHERE s.ncr_number_excel IS NOT NULL;

-- =====================================================
-- STEP 5: Create Sample CAPAs for Closed NCRs
-- =====================================================

INSERT INTO capas (
    capa_number,
    ncr_id,
    rca_method,
    root_cause_summary,
    corrective_action_plan,
    preventive_action_plan,
    assigned_pic_id,
    assigned_by_user_id,
    target_completion_date,
    actual_completion_date,
    progress_percentage,
    current_status,
    effectiveness_verified,
    created_at,
    updated_at
)
SELECT 
    CONCAT('CAPA-', YEAR(n.date_found), '-', LPAD(n.id, 4, '0')) as capa_number,
    n.id as ncr_id,
    '5_Why' as rca_method,
    'Root cause analysis completed - migrated from Excel system' as root_cause_summary,
    'Corrective action implemented as per historical records' as corrective_action_plan,
    'Preventive measures documented in legacy system' as preventive_action_plan,
    
    -- Assign to production staff as default PIC
    (SELECT id FROM users WHERE department_id = 2 AND role_id = 8 LIMIT 1) as assigned_pic_id,
    
    -- Assigned by QC Manager
    (SELECT id FROM users WHERE role_id = 2 LIMIT 1) as assigned_by_user_id,
    
    DATE_ADD(n.date_found, INTERVAL 14 DAY) as target_completion_date,
    n.closed_at as actual_completion_date,
    100 as progress_percentage,
    'Closed' as current_status,
    TRUE as effectiveness_verified,
    n.created_at,
    n.updated_at
    
FROM ncrs n
WHERE n.status = 'Closed'
AND n.id NOT IN (SELECT ncr_id FROM capas);

-- =====================================================
-- STEP 6: Create Activity Logs for Migrated Data
-- =====================================================

INSERT INTO activity_logs (
    user_id,
    entity_type,
    entity_id,
    action_type,
    action_description,
    performed_at
)
SELECT 
    1 as user_id, -- System admin
    'NCR' as entity_type,
    id as entity_id,
    'Migrated' as action_type,
    CONCAT('NCR migrated from Excel VBA system - Original NCR: ', ncr_number) as action_description,
    created_at as performed_at
FROM ncrs
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY);

-- =====================================================
-- STEP 7: Verification Queries
-- =====================================================

-- Count migrated NCRs by year
SELECT 
    YEAR(date_found) as year,
    COUNT(*) as ncr_count,
    SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) as closed_count,
    SUM(CASE WHEN status != 'Closed' THEN 1 ELSE 0 END) as open_count
FROM ncrs
GROUP BY YEAR(date_found)
ORDER BY year;

-- Count by defect category
SELECT 
    dc.category_name,
    COUNT(n.id) as ncr_count,
    ROUND(COUNT(n.id) * 100.0 / (SELECT COUNT(*) FROM ncrs), 1) as percentage
FROM ncrs n
JOIN defect_categories dc ON n.defect_category_id = dc.id
GROUP BY dc.category_name
ORDER BY ncr_count DESC;

-- Count by finder department
SELECT 
    d.department_name,
    COUNT(n.id) as ncr_found
FROM ncrs n
JOIN departments d ON n.finder_dept_id = d.id
GROUP BY d.department_name
ORDER BY ncr_found DESC;

-- Count by receiver department
SELECT 
    d.department_name,
    COUNT(n.id) as ncr_received
FROM ncrs n
JOIN departments d ON n.receiver_dept_id = d.id
GROUP BY d.department_name
ORDER BY ncr_received DESC;

-- CAPA statistics
SELECT 
    COUNT(*) as total_capas,
    SUM(CASE WHEN current_status = 'Closed' THEN 1 ELSE 0 END) as closed_capas,
    SUM(CASE WHEN effectiveness_verified = TRUE THEN 1 ELSE 0 END) as verified_capas,
    ROUND(AVG(progress_percentage), 1) as avg_progress
FROM capas;

-- =====================================================
-- STEP 8: Cleanup Staging Table (Optional)
-- =====================================================
-- DROP TABLE IF EXISTS ncr_staging;

-- =====================================================
-- MIGRATION SUMMARY
-- =====================================================
SELECT 
    'Migration Summary' as info,
    (SELECT COUNT(*) FROM ncrs) as total_ncrs_migrated,
    (SELECT COUNT(*) FROM capas) as total_capas_created,
    (SELECT COUNT(*) FROM activity_logs WHERE action_type = 'Migrated') as migration_logs,
    NOW() as migration_completed_at;

-- =====================================================
-- IMPORTANT POST-MIGRATION STEPS:
-- =====================================================
-- 1. Verify data accuracy by sampling random records
-- 2. Update NCR number sequence for new records
-- 3. Recalculate department statistics
-- 4. Generate initial dashboard metrics
-- 5. Notify users about data migration completion
-- 6. Archive original Excel files as backup
-- 7. Update system settings with migration date
-- =====================================================

-- Update settings to record migration
INSERT INTO settings (setting_key, setting_value, setting_type, category, description)
VALUES ('data_migration_date', NOW(), 'string', 'System', 'Date when Excel data was migrated to database')
ON DUPLICATE KEY UPDATE setting_value = NOW();

-- =====================================================
-- END OF MIGRATION SCRIPT
-- =====================================================
