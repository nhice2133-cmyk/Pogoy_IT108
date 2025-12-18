# Smart Fisheries Management and Catch Monitoring System

A comprehensive web-based system for managing fisheries and monitoring catch records for local fishers in Cabadbaran City.

## Features

- **User Management**: Registration and authentication for fishers, officers, and administrators
- **Catch Monitoring**: Record and track fishing catches with detailed information
- **Vessel Management**: Register and manage fishing vessels
- **Fishing Zones**: Track fishing locations and zones
- **Analytics Dashboard**: View statistics and monthly catch reports
- **Role-Based Access**: Different access levels for fishers, officers, and admins

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server (XAMPP recommended)
- Modern web browser

## Installation

1. **Clone or extract the project** to your web server directory:
   ```
   C:\xampp\htdocs\IT108_system
   ```

2. **Create the database**:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the database schema from `database/schema.sql`
   - Or run the SQL file directly in MySQL

3. **Configure database connection**:
   - Edit `config/database.php` if needed (default settings work with XAMPP)
   - Update database credentials if necessary:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'fisheries_management');
     ```

4. **Set up file permissions** (if on Linux):
   - Ensure the `uploads` directory is writable:
     ```bash
     chmod 755 uploads
     ```

5. **Access the application**:
   - Open your browser and navigate to:
     ```
     http://localhost/IT108_system/
     ```

## Default Login Credentials

- **Username**: `admin`
- **Password**: `admin123`

**⚠️ Important**: Change the default admin password after first login!

## User Roles

### Fisher
- Register and record catches
- Manage own vessels
- View own catch records and statistics

### Officer
- View all catch records
- Verify catch records
- Monitor fishing activities

### Admin
- Full system access
- Manage users
- View all statistics and reports
- System configuration

## System Structure

```
IT108_system/
├── api/                 # Backend API endpoints
│   ├── auth.php        # Authentication
│   ├── catch.php       # Catch records
│   ├── vessels.php     # Vessel management
│   ├── gear.php        # Fishing gear
│   ├── zones.php       # Fishing zones
│   ├── species.php     # Fish species
│   └── users.php       # User management
├── assets/
│   ├── css/
│   │   └── style.css   # Main stylesheet
│   └── js/
│       └── app.js      # Frontend JavaScript
├── config/
│   ├── config.php      # Application configuration
│   └── database.php    # Database connection
├── database/
│   └── schema.sql      # Database schema
├── index.php           # Main application entry point
├── .htaccess          # Apache configuration
└── README.md          # This file
```

## API Endpoints

### Authentication
- `POST /api/auth.php?action=login` - User login
- `POST /api/auth.php?action=register` - User registration
- `GET /api/auth.php?action=logout` - User logout
- `GET /api/auth.php?action=check` - Check authentication status

### Catch Records
- `GET /api/catch.php?action=list` - List catch records
- `GET /api/catch.php?action=get&id={id}` - Get single record
- `GET /api/catch.php?action=stats` - Get statistics
- `POST /api/catch.php?action=create` - Create catch record
- `PUT /api/catch.php?action=update&id={id}` - Update record
- `PUT /api/catch.php?action=verify&id={id}` - Verify record (admin)
- `DELETE /api/catch.php?action=delete&id={id}` - Delete record

### Vessels
- `GET /api/vessels.php?action=list` - List vessels
- `GET /api/vessels.php?action=get&id={id}` - Get vessel details
- `POST /api/vessels.php?action=create` - Register vessel
- `PUT /api/vessels.php?action=update&id={id}` - Update vessel
- `DELETE /api/vessels.php?action=delete&id={id}` - Delete vessel

## Database Schema

The system uses the following main tables:
- `users` - User accounts and information
- `vessels` - Fishing vessel registration
- `catch_records` - Catch monitoring records
- `fishing_gear` - Types of fishing gear
- `fishing_zones` - Designated fishing areas
- `fish_species` - Catalog of fish species

## Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Session-based authentication
- Role-based access control

## Development

### Adding New Features

1. Create API endpoint in `api/` directory
2. Add frontend view in `index.php`
3. Add JavaScript handlers in `assets/js/app.js`
4. Update navigation if needed

### Database Changes

1. Update `database/schema.sql`
2. Create migration script if needed
3. Update affected API endpoints

## Troubleshooting

### Database Connection Error
- Check MySQL service is running
- Verify database credentials in `config/database.php`
- Ensure database exists and is imported

### Session Issues
- Check PHP session configuration
- Ensure `session_start()` is called
- Clear browser cookies

### API Not Working
- Check Apache mod_rewrite is enabled
- Verify `.htaccess` file is present
- Check PHP error logs

## Support

For issues or questions, please contact the system administrator.

## License

This system is developed for Cabadbaran City Fisheries Management.

## Version

Version 1.0.0 - Initial Release


