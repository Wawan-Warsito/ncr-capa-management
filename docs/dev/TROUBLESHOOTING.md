# Troubleshooting Guide

## Introduction
This guide is intended for System Administrators and IT Support staff to resolve common issues encountered with the NCR-CAPA Management System.

## 1. Login Issues

### Issue: User cannot login ("Invalid credentials")
**Potential Causes:**
- Incorrect password.
- Account is inactive.

**Solution:**
1. Check `users` table in database: `SELECT * FROM users WHERE email = 'user@example.com';`
2. Ensure `is_active` is set to `1`.
3. Reset password via artisan: `php artisan tinker` -> `$u = User::where('email', '...')->first(); $u->password = bcrypt('newpass'); $u->save();`

### Issue: "419 Page Expired"
**Potential Causes:**
- CSRF token mismatch.
- Session timeout.

**Solution:**
1. Clear browser cookies and cache.
2. Ensure `.env` `SESSION_DOMAIN` is correct if using subdomains.
3. Check `storage/framework/sessions` permissions (must be writable).

## 2. Application Errors (500 Internal Server Error)

### Issue: Blank screen or 500 Error
**Solution:**
1. Check Laravel logs: `storage/logs/laravel.log`.
2. Ensure `.env` is correctly configured.
3. Run `php artisan config:clear` and `php artisan cache:clear`.
4. Check folder permissions: `storage` and `bootstrap/cache` must be writable (775).

### Issue: "Class '...' not found"
**Solution:**
1. Run `composer dump-autoload`.
2. Ensure namespaces in new files are correct.

## 3. Database Issues

### Issue: "Connection refused"
**Solution:**
1. Check if MySQL service is running.
2. Verify DB credentials in `.env`.
3. Check `DB_HOST` (use `127.0.0.1` instead of `localhost` sometimes fixes socket issues).

### Issue: "SQLSTATE[HY000]: General error: 1215 Cannot add foreign key constraint"
**Solution:**
1. Ensure referenced tables exist and use the same engine (InnoDB).
2. Ensure referenced columns have the exact same data type (e.g., `unsignedBigInteger`).

## 4. Frontend Issues

### Issue: Assets not loading (404 for JS/CSS)
**Solution:**
1. Run `npm run build` in production.
2. Ensure `APP_URL` in `.env` matches the browser URL.
3. Check if `public/build` directory exists.

### Issue: Changes not reflecting
**Solution:**
1. Browser cache: Hard reload (Ctrl+F5).
2. Run `npm run build` again if in production mode.

## 5. Email Issues

### Issue: Emails not sending
**Solution:**
1. Check `.env` mail configuration (`MAIL_MAILER`, `MAIL_HOST`, etc.).
2. Check `storage/logs/laravel.log` for SMTP errors.
3. If using `log` driver, check logs for email content.
4. Verify queue worker is running: `php artisan queue:work`.

## 6. Performance Issues

### Issue: Slow page load
**Solution:**
1. Enable caching: `php artisan route:cache`, `php artisan config:cache`.
2. Optimize Composer autoloader: `composer install --optimize-autoloader --no-dev`.
3. Check for N+1 queries using Laravel Debugbar (in dev) or telescope.
4. Ensure database indexes are present on foreign keys and frequently searched columns.

## Contact Support
If the issue persists, escalate to the Development Team with:
- Error logs.
- Steps to reproduce.
- Environment details (OS, PHP version, Browser).
