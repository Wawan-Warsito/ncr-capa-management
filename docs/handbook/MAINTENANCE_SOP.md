# Standard Operating Procedure (SOP) - System Maintenance
**Document ID:** SOP-IT-NCR-001
**Version:** 1.0
**Effective Date:** 2026-03-01

## 1. Objective
To ensure the NCR-CAPA Management System operates efficiently, securely, and reliably through regular maintenance activities.

## 2. Scope
This SOP covers the maintenance of the Web Server, Database, and Application Codebase.

## 3. Roles & Responsibilities
- **System Administrator**: Responsible for server updates, backups, and security patches.
- **Application Developer**: Responsible for code updates, bug fixes, and feature enhancements.

## 4. Maintenance Schedule

### 4.1 Daily Activities
- **Log Monitoring**: Check `storage/logs/laravel.log` for any critical errors (Error 500).
- **Queue Check**: Verify that background jobs (emails) are being processed (`php artisan queue:monitor`).
- **Backup Verification**: Confirm that the nightly database backup completed successfully.

### 4.2 Weekly Activities
- **Disk Space Check**: Monitor server disk usage (`df -h`). Clear old log files if necessary.
- **Performance Review**: Check average response times and slow queries.
- **User Audit**: Deactivate accounts of employees who have left the company.

### 4.3 Monthly Activities
- **Security Updates**: Apply OS and Server software patches (Ubuntu, Nginx/Apache, PHP, MySQL).
- **Dependency Updates**: Run `composer update` and `npm update` to patch security vulnerabilities in libraries (Test in Staging first!).
- **Full Restore Test**: Perform a test restoration of the database backup to a staging environment to ensure data integrity.

## 5. Incident Response
In case of system failure:
1. **Identify**: Determine if it's a Server, Database, or Application issue.
2. **Contain**: If security breach, take system offline.
3. **Resolve**: Apply fix or restore from backup.
4. **Notify**: Inform users via email about downtime and expected resolution time.
5. **Post-Mortem**: Document the root cause and preventive measures.

## 6. Backup Policy
- **Frequency**: Daily (Midnight).
- **Retention**: Keep daily backups for 30 days. Keep monthly backups for 1 year.
- **Location**: Store encrypted backups in a separate physical location or Cloud Storage (AWS S3).

## 7. Change Management
All changes to the production environment must follow:
1. **Development**: Code in local environment.
2. **Testing**: Deploy to Staging Server for UAT.
3. **Approval**: Get sign-off from QA Manager.
4. **Deployment**: Deploy to Production during maintenance window (e.g., Friday 22:00).
