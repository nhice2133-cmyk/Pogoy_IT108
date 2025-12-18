# Installation Guide

## Quick Start (XAMPP)

### Step 1: Start Services
1. Open XAMPP Control Panel
2. Start **Apache** service
3. Start **MySQL** service

### Step 2: Import Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click on "New" to create a database (or use existing)
3. Click "Import" tab
4. Choose file: `database/schema.sql`
5. Click "Go"

Alternatively, you can run the SQL file directly:
```sql
-- The schema.sql file will create the database automatically
```

### Step 3: Verify Configuration
Check `config/database.php`:
- Default XAMPP settings should work:
  - Host: `localhost`
  - User: `root`
  - Password: (empty)
  - Database: `fisheries_management`

### Step 4: Access Application
Open browser: http://localhost/IT108_system/

### Step 5: Login
- Username: `admin`
- Password: `admin123`

## Manual Installation

### Requirements Check
```bash
php -v  # Should be 7.4 or higher
mysql --version  # Should be 5.7 or higher
```

### Database Setup
```sql
-- Connect to MySQL
mysql -u root -p

-- Create database
CREATE DATABASE fisheries_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import schema
USE fisheries_management;
SOURCE /path/to/database/schema.sql;
```

### File Permissions (Linux)
```bash
chmod 755 uploads/
chmod 644 config/*.php
chmod 644 api/*.php
```

### Apache Configuration
Ensure mod_rewrite is enabled:
```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

## Post-Installation

1. **Change Admin Password**
   - Login as admin
   - Update password through user management (if implemented)
   - Or update directly in database:
     ```sql
     UPDATE users SET password_hash = '$2y$10$...' WHERE username = 'admin';
     ```

2. **Configure System Settings**
   - Access system settings through admin panel
   - Update city name, limits, etc.

3. **Add Initial Data** (Optional)
   - Add more fishing zones
   - Add more fish species
   - Add fishing gear types

## Verification

After installation, verify:
- [ ] Can access login page
- [ ] Can login with admin credentials
- [ ] Dashboard loads without errors
- [ ] Can create a catch record
- [ ] Can register a vessel
- [ ] Statistics display correctly

## Common Issues

### Issue: "Database connection failed"
**Solution**: 
- Check MySQL is running
- Verify credentials in `config/database.php`
- Ensure database exists

### Issue: "404 Not Found" on API calls
**Solution**:
- Check `.htaccess` file exists
- Enable mod_rewrite in Apache
- Check Apache error logs

### Issue: "Session not working"
**Solution**:
- Check PHP session directory is writable
- Verify `session_start()` in config.php
- Clear browser cookies

### Issue: "Permission denied"
**Solution** (Linux):
```bash
chmod -R 755 .
chown -R www-data:www-data .
```

## Next Steps

1. Register fishers
2. Register vessels
3. Start recording catches
4. Generate reports

For more information, see README.md


