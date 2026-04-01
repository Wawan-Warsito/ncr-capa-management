# User Guide

## Introduction
Welcome to the NCR-CAPA Management System. This guide will help you navigate the system, report Non-Conformance Reports (NCR), and manage Corrective and Preventive Actions (CAPA).

## Getting Started

### Login
1. Navigate to the login page (e.g., `http://localhost:8000/login`).
2. Enter your email address and password.
3. Click "Sign In".

### Dashboard
After login, you will see the Dashboard, which provides:
- **Overview**: Statistics of open/closed NCRs and CAPAs.
- **Recent Activity**: Latest updates on reports you follow.
- **Pending Tasks**: Items requiring your immediate attention.

## Managing NCRs

### Reporting a New NCR
1. Click "New NCR" in the navigation menu.
2. Fill in the required fields:
   - **NCR Number**: Auto-generated or manual input (if enabled).
   - **Project Name**: Select or enter project.
   - **Department**: Identify the Finder and Receiver departments.
   - **Defect Details**: Describe the issue clearly.
   - **Severity**: Choose the impact level (Minor, Major, Critical).
   - **Attachments**: Upload photos or documents (optional).
3. Click "Submit". The NCR will be sent to the Finder Department Manager for approval.

### Reviewing & Approving NCRs (Managers)
1. Go to "My Approvals".
2. Select an NCR with status "Pending Approval".
3. Review the details.
4. Click "Approve" to proceed or "Reject" to send back for revision.

### NCR Workflow Steps
1. **Submission**: User submits NCR -> Sent to Finder Dept Manager.
2. **Finder Approval**: Finder Manager approves -> Sent to QC Manager for Registration.
3. **QC Registration**: QC Manager reviews and registers -> Sent to Receiver Dept.
4. **Disposition**: Receiver Manager assigns disposition.

### Assigning Disposition
1. Once registered by QC Manager, the Receiver Department is notified.
2. Receiver Manager assigns a disposition (e.g., Rework, Scrap).
3. If necessary, a CAPA is initiated.

## Managing CAPAs

### Creating a CAPA
1. From an NCR detail page, click "Create CAPA".
2. Assign a Person In Charge (PIC).
3. Define the Root Cause Analysis (RCA).
4. Outline the Corrective Action Plan.
5. Set target completion dates.

### Updating Progress (PIC)
1. Go to "My Tasks".
2. Open the assigned CAPA.
3. Update progress percentage and add comments.
4. Mark as "Completed" when finished.

### Verification (QC Manager)
1. Review completed CAPAs.
2. Verify effectiveness of the action taken.
3. Close the CAPA if satisfied, or reopen if ineffective.

## Account Management
- **Profile**: Update your password and contact information.
- **Logout**: Securely sign out from the system.

## Support
For technical issues, please contact the IT Department or submit a ticket via the Helpdesk portal.
