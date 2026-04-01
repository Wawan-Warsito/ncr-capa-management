# Installation Guide

## Prerequisites

### Server Requirements
- **PHP**: 8.2 or higher
- **Composer**: 2.5 or higher
- **Node.js**: 18.0 or higher
- **NPM**: 9.0 or higher
- **Database**: SQLite (local) atau MySQL/MariaDB (opsional)
- **Web Server**: Apache or Nginx

### PHP Extensions Required
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML

## Step-by-Step Installation

### 1. Clone the Repository
```bash
git clone https://github.com/your-repo/ncr-capa-management.git
cd ncr-capa-management
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies
npm install
```

### 3. Environment Configuration
1. Copy the example environment file:
   ```bash
   cp .env.example .env
   ```
2. Open `.env` and configure your database settings.

   SQLite (recommended for local):
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```

   MySQL/MariaDB (optional):
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ncr_capa_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Database Migration & Seeding
Run migrations to create database tables and seed initial data:
```bash
php artisan migrate --seed
```

### 6. Build Frontend Assets
For production environment:
```bash
npm run build
```

### 7. File Permissions
Ensure the storage and bootstrap/cache directories are writable:
```bash
chmod -R 775 storage bootstrap/cache
```

### 8. Web Server Configuration (Apache)
Point your virtual host document root to the `public` directory:
```apache
<VirtualHost *:80>
    ServerName ncr-capa.local
    DocumentRoot "C:/xampp/htdocs/ncr-capa-management/public"
    <Directory "C:/xampp/htdocs/ncr-capa-management/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 9. Accessing the Application
Open your browser and navigate to `http://ncr-capa.local` (or your configured domain).

## Troubleshooting
- **500 Server Error**: Check `storage/logs/laravel.log` for details.
- **Permission Denied**: Verify folder permissions on `storage`.
- **Database Connection Error**: Double-check `.env` credentials.
