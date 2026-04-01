# 🧪 API Testing Guide - NCR CAPA Management System

## Quick Start Testing

### Prerequisites
- ✅ Laravel server running at http://127.0.0.1:8000
- ✅ Database seeded with test data
- ✅ Postman or Insomnia installed (or use curl)

---

## 🔐 1. AUTHENTICATION TESTS

### Test 1.1: Login (Admin)
```http
POST http://127.0.0.1:8000/api/auth/login
Content-Type: application/json

{
  "email": "admin@topsystem.com",
  "password": "password"
}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "System Administrator",
      "email": "admin@topsystem.com",
      "role": "Admin",
      "role_display": "System Administrator",
      "department": "Quality Control",
      "department_code": "QC",
      "permissions": [...]
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

**⚠️ IMPORTANT:** Copy the `token` value for next requests!

---

### Test 1.2: Get Current User Info
```http
GET http://127.0.0.1:8000/api/auth/me
Authorization: Bearer {YOUR_TOKEN_HERE}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "System Administrator",
    "email": "admin@topsystem.com",
    "role": {
      "id": 1,
      "name": "Admin",
      "display_name": "System Administrator",
      "level": 5,
      "permissions": [...]
    },
    "department": {
      "id": 1,
      "name": "Quality Control",
      "code": "QC"
    },
    "unread_notifications": 0
  }
}
```

---

### Test 1.3: Login (QC Manager)
```http
POST http://127.0.0.1:8000/api/auth/login
Content-Type: application/json

{
  "email": "qc.manager@topsystem.com",
  "password": "password"
}
```

---

### Test 1.4: Login (Department Manager)
```http
POST http://127.0.0.1:8000/api/auth/login
Content-Type: application/json

{
  "email": "prod.manager@topsystem.com",
  "password": "password"
}
```

---

## 📊 2. DASHBOARD TESTS

### Test 2.1: Personal Dashboard
```http
GET http://127.0.0.1:8000/api/dashboard/personal
Authorization: Bearer {YOUR_TOKEN_HERE}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "my_ncrs": {
      "total": 0,
      "open": 0,
      "pending_approval": 0
    },
    "assigned_ncrs": {
      "total": 0,
      "open": 0,
      "overdue": 0
    },
    "my_capas": {
      "total": 0,
      "in_progress": 0,
      "overdue": 0,
      "pending_verification": 0
    },
    "my_tasks": {
      "ncrs": [],
      "capas": []
    }
  }
}
```

---

### Test 2.2: Company Dashboard (Admin/QC Manager only)
```http
GET http://127.0.0.1:8000/api/dashboard/company
Authorization: Bearer {YOUR_TOKEN_HERE}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "ncr_stats": {
      "total": 0,
      "open": 0,
      "closed": 0,
      "overdue": 0,
      "pending_approval": 0
    },
    "capa_stats": {
      "total": 0,
      "in_progress": 0,
      "completed": 0,
      "overdue": 0
    },
    "ncr_by_department": [],
    "ncr_by_category": [],
    "ncr_trend": []
  }
}
```

---

### Test 2.3: Quick Stats
```http
GET http://127.0.0.1:8000/api/dashboard/quick-stats
Authorization: Bearer {YOUR_TOKEN_HERE}
```

---

## 📝 3. MASTER DATA TESTS

### Test 3.1: Get All Departments
```http
GET http://127.0.0.1:8000/api/master/departments
Authorization: Bearer {YOUR_TOKEN_HERE}
```

**Expected Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "department_name": "Quality Control",
      "department_code": "QC",
      "is_active": true
    },
    {
      "id": 2,
      "department_name": "Production",
      "department_code": "PROD",
      "is_active": true
    }
    // ... 10 more departments
  ]
}
```

---

### Test 3.2: Get All Roles
```http
GET http://127.0.0.1:8000/api/master/roles
Authorization: Bearer {YOUR_TOKEN_HERE}
```

---

### Test 3.3: Get Defect Categories
```http
GET http://127.0.0.1:8000/api/master/defect-categories
Authorization: Bearer {YOUR_TOKEN_HERE}
```

**Expected Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "category_name": "Dimensional Defect",
      "category_code": "DIM",
      "is_active": true
    },
    {
      "id": 2,
      "category_name": "Welding Defect",
      "category_code": "WELD",
      "is_active": true
    }
    // ... more categories
  ]
}
```

---

### Test 3.4: Get Severity Levels
```http
GET http://127.0.0.1:8000/api/master/severity-levels
Authorization: Bearer {YOUR_TOKEN_HERE}
```

**Expected Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "level_name": "Critical",
      "level_code": "CRITICAL",
      "priority": 1,
      "color_code": "#DC2626"
    },
    {
      "id": 2,
      "level_name": "Major",
      "level_code": "MAJOR",
      "priority": 2,
      "color_code": "#F59E0B"
    },
    {
      "id": 3,
      "level_name": "Minor",
      "level_code": "MINOR",
      "priority": 3,
      "color_code": "#10B981"
    }
  ]
}
```

---

### Test 3.5: Get Users
```http
GET http://127.0.0.1:8000/api/master/users
Authorization: Bearer {YOUR_TOKEN_HERE}
```

---

### Test 3.6: Get Users by Department
```http
GET http://127.0.0.1:8000/api/master/users?department_id=1
Authorization: Bearer {YOUR_TOKEN_HERE}
```

---

## 📋 4. NCR MANAGEMENT TESTS

### Test 4.1: Get NCR List (Empty initially)
```http
GET http://127.0.0.1:8000/api/ncrs
Authorization: Bearer {YOUR_TOKEN_HERE}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [],
    "total": 0,
    "per_page": 15
  }
}
```

---

### Test 4.2: Create New NCR
```http
POST http://127.0.0.1:8000/api/ncrs
Authorization: Bearer {YOUR_TOKEN_HERE}
Content-Type: application/json

{
  "project_name": "Tank Fabrication Project A",
  "customer_name": "PT. ABC Industries",
  "product_description": "Stainless Steel Tank 5000L",
  "date_found": "2025-01-15",
  "location_found": "Welding Area",
  "quantity_affected": 1,
  "finder_dept_id": 1,
  "receiver_dept_id": 2,
  "defect_category_id": 2,
  "defect_description": "Welding porosity found on main seam",
  "severity_level_id": 2,
  "immediate_action": "Stop welding, isolate affected area",
  "is_asme_project": false
}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "NCR created successfully",
  "data": {
    "id": 1,
    "ncr_number": "25.P00-QC-01",
    "project_name": "Tank Fabrication Project A",
    "status": "Draft",
    "created_at": "2025-01-15T10:30:00.000000Z",
    "finder_department": {
      "id": 1,
      "department_name": "Quality Control"
    },
    "receiver_department": {
      "id": 2,
      "department_name": "Production"
    }
  }
}
```

**⚠️ IMPORTANT:** Copy the NCR `id` for next tests!

---

### Test 4.3: Get NCR Detail
```http
GET http://127.0.0.1:8000/api/ncrs/1
Authorization: Bearer {YOUR_TOKEN_HERE}
```

---

### Test 4.4: Submit NCR for Approval
```http
POST http://127.0.0.1:8000/api/ncrs/1/submit
Authorization: Bearer {YOUR_TOKEN_HERE}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "NCR submitted successfully",
  "data": {
    "id": 1,
    "ncr_number": "25.P00-QC-01",
    "status": "Pending_Finder_Approval",
    "submitted_at": "2025-01-15T10:35:00.000000Z"
  }
}
```

---

### Test 4.5: Approve NCR (as Finder Manager)
First, login as department manager:
```http
POST http://127.0.0.1:8000/api/auth/login
Content-Type: application/json

{
  "email": "qc.manager@topsystem.com",
  "password": "password"
}
```

Then approve:
```http
POST http://127.0.0.1:8000/api/ncrs/1/approve
Authorization: Bearer {MANAGER_TOKEN_HERE}
Content-Type: application/json

{
  "remarks": "Approved. Please proceed with corrective action."
}
```

---

### Test 4.6: Get NCR List with Filters
```http
GET http://127.0.0.1:8000/api/ncrs?status=Pending_QC_Registration
Authorization: Bearer {YOUR_TOKEN_HERE}
```

---

### Test 4.7: Search NCRs
```http
GET http://127.0.0.1:8000/api/ncrs?search=welding
Authorization: Bearer {YOUR_TOKEN_HERE}
```

---

## 🔧 5. CAPA MANAGEMENT TESTS

### Test 5.1: Get CAPA List
```http
GET http://127.0.0.1:8000/api/capas
Authorization: Bearer {YOUR_TOKEN_HERE}
```

---

### Test 5.2: Create CAPA (5 Why Method)
```http
POST http://127.0.0.1:8000/api/capas
Authorization: Bearer {YOUR_TOKEN_HERE}
Content-Type: application/json

{
  "ncr_id": 1,
  "rca_method": "5_Why",
  "root_cause_summary": "Inadequate welding procedure and lack of welder training",
  "why_1": "Why did porosity occur? - Improper welding technique",
  "why_2": "Why improper technique? - Welder not properly trained",
  "why_3": "Why not trained? - Training program outdated",
  "why_4": "Why outdated? - No regular review of procedures",
  "why_5": "Why no review? - Lack of quality management system",
  "corrective_action_plan": "1. Retrain all welders on proper technique\n2. Update welding procedures\n3. Implement regular procedure reviews",
  "preventive_action_plan": "1. Establish quarterly training program\n2. Create procedure review schedule\n3. Implement welder certification tracking",
  "expected_outcome": "Zero welding defects in next 3 months",
  "assigned_pic_id": 10,
  "target_completion_date": "2025-02-15"
}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "CAPA created successfully",
  "data": {
    "id": 1,
    "capa_number": "CAPA-25-0001",
    "ncr_id": 1,
    "current_status": "Planned",
    "progress_percentage": 0,
    "assigned_pic": {
      "id": 10,
      "name": "Production Supervisor"
    }
  }
}
```

---

### Test 5.3: Update CAPA Progress
```http
POST http://127.0.0.1:8000/api/capas/1/progress
Authorization: Bearer {PIC_TOKEN_HERE}
Content-Type: application/json

{
  "progress_percentage": 25,
  "milestone_description": "Completed welder retraining program",
  "activities_completed": "- Conducted 3-day training for 10 welders\n- Updated welding procedures document",
  "challenges_encountered": "Some welders had scheduling conflicts",
  "next_steps": "- Implement procedure review schedule\n- Start certification tracking system"
}
```

---

### Test 5.4: Verify CAPA Effectiveness
```http
POST http://127.0.0.1:8000/api/capas/1/verify
Authorization: Bearer {QC_MANAGER_TOKEN}
Content-Type: application/json

{
  "verification_method": "Visual inspection and weld testing",
  "verification_results": "No porosity defects found in 50 welds inspected",
  "effectiveness_verified": true,
  "monitoring_start_date": "2025-02-15",
  "monitoring_end_date": "2025-05-15",
  "monitoring_notes": "Continue monitoring for 3 months"
}
```

---

## 🔔 6. NOTIFICATION TESTS

### Test 6.1: Get All Notifications
```http
GET http://127.0.0.1:8000/api/notifications
Authorization: Bearer {YOUR_TOKEN_HERE}
```

---

### Test 6.2: Get Unread Notifications
```http
GET http://127.0.0.1:8000/api/notifications/unread
Authorization: Bearer {YOUR_TOKEN_HERE}
```

---

### Test 6.3: Mark Notification as Read
```http
POST http://127.0.0.1:8000/api/notifications/1/read
Authorization: Bearer {YOUR_TOKEN_HERE}
```

---

### Test 6.4: Mark All as Read
```http
POST http://127.0.0.1:8000/api/notifications/mark-all-read
Authorization: Bearer {YOUR_TOKEN_HERE}
```

---

## 🧪 COMPLETE TEST SEQUENCE

### Scenario: Complete NCR-CAPA Workflow

**Step 1:** Login as QC Inspector
```http
POST /api/auth/login
{ "email": "qc.inspector1@topsystem.com", "password": "password" }
```

**Step 2:** Create NCR
```http
POST /api/ncrs
{ ... ncr data ... }
```

**Step 3:** Submit NCR
```http
POST /api/ncrs/1/submit
```

**Step 4:** Login as QC Manager
```http
POST /api/auth/login
{ "email": "qc.manager@topsystem.com", "password": "password" }
```

**Step 5:** Approve NCR
```http
POST /api/ncrs/1/approve
{ "remarks": "Approved" }
```

**Step 6:** Create CAPA
```http
POST /api/capas
{ ... capa data ... }
```

**Step 7:** Login as Assigned PIC
```http
POST /api/auth/login
{ "email": "prod.staff1@topsystem.com", "password": "password" }
```

**Step 8:** Update Progress
```http
POST /api/capas/1/progress
{ "progress_percentage": 50, ... }
```

**Step 9:** Complete Progress
```http
POST /api/capas/1/progress
{ "progress_percentage": 100, ... }
```

**Step 10:** Login as QC Manager
```http
POST /api/auth/login
{ "email": "qc.manager@topsystem.com", "password": "password" }
```

**Step 11:** Verify CAPA
```http
POST /api/capas/1/verify
{ "effectiveness_verified": true, ... }
```

**Step 12:** Close CAPA
```http
POST /api/capas/1/close
{ "closure_remarks": "CAPA completed successfully" }
```

---

## 📝 TEST USERS REFERENCE

| Email | Password | Role | Department |
|-------|----------|------|------------|
| admin@topsystem.com | password | Admin | QC |
| qc.manager@topsystem.com | password | QC Manager | QC |
| qc.inspector1@topsystem.com | password | QC Inspector | QC |
| prod.manager@topsystem.com | password | Dept Manager | Production |
| prod.staff1@topsystem.com | password | Staff | Production |
| eng.manager@topsystem.com | password | Dept Manager | Engineering |

---

## ✅ SUCCESS CRITERIA

Your backend is working correctly if:
- ✅ Login returns a valid token
- ✅ Protected endpoints require authentication
- ✅ Dashboard returns data (even if empty)
- ✅ Master data endpoints return seeded data
- ✅ NCR can be created and submitted
- ✅ CAPA can be created and tracked
- ✅ Notifications are created automatically
- ✅ Proper error messages for invalid requests

---

## 🐛 TROUBLESHOOTING

### Error: "Unauthenticated"
- Check if token is included in Authorization header
- Format: `Authorization: Bearer {token}`
- Token might have expired, login again

### Error: "SQLSTATE[HY000] [2002]"
- MySQL server not running
- Start XAMPP MySQL service

### Error: "404 Not Found"
- Check URL spelling
- Ensure Laravel server is running
- Check route exists in routes/api.php

### Error: "500 Internal Server Error"
- Check Laravel logs: storage/logs/laravel.log
- Database connection issue
- Missing required fields in request

---

**Happy Testing! 🎉**

Your backend is ready and waiting for the frontend!
