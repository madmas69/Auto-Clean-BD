# AutoClean BD - Setup Guide

## Prerequisites
1. **XAMPP** installed and running
2. **Apache** and **MySQL** services started
3. Project folder placed in `C:\xampp\htdocs\web\`

## Setup Steps

### 1. Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Verify both services are running (green status)

### 2. Create Database
1. Open your browser and go to `http://localhost/phpmyadmin`
2. Click "New" to create a new database
3. Enter database name: `autoclean_bd`
4. Click "Create"

### 3. Initialize Database Tables
1. The database tables will be automatically created when you first access the website
2. Or manually run the initialization by visiting: `http://localhost/web/api/config.php`

### 4. Test the System
1. Open your browser and go to `http://localhost/web/`
2. Click the "Test" button in the navigation to verify backend connection
3. Try registering a new account
4. Try logging in with the created account

## Default Admin Account
- **Email**: admin@autocleanbd.com
- **Password**: admin123
- **User Type**: Admin

## Troubleshooting

### If registration/login doesn't work:

1. **Check XAMPP Status**
   - Ensure Apache and MySQL are running
   - Check XAMPP error logs if services won't start

2. **Check Database Connection**
   - Verify database `autoclean_bd` exists
   - Check if tables were created automatically

3. **Check File Permissions**
   - Ensure PHP files are readable by Apache
   - Check if the `api` folder is accessible

4. **Check Browser Console**
   - Open Developer Tools (F12)
   - Look for JavaScript errors
   - Check Network tab for failed requests

5. **Test Backend Connection**
   - Click the "Test" button in navigation
   - Check the response in browser console

### Common Issues:

1. **404 Errors**: Make sure the project is in the correct folder (`C:\xampp\htdocs\web\`)
2. **Database Connection Failed**: Check if MySQL is running and database exists
3. **CORS Errors**: The API includes CORS headers, but check if Apache is configured correctly
4. **Permission Denied**: Check file permissions and Apache configuration

## File Structure
```
web/
├── index.html              # Main website
├── assets/
│   └── js/
│       └── main.js         # JavaScript functionality
├── api/
│   ├── config.php          # Database configuration
│   ├── register.php        # Registration API
│   ├── login.php           # Login API
│   └── test_connection.php # Connection test
└── customer/
    └── dashboard.html      # Customer dashboard
```

## API Endpoints
- `POST /api/register.php` - User registration
- `POST /api/login.php` - User login
- `GET /api/test_connection.php` - Test backend connection

## Support
If you continue to have issues:
1. Check the browser console for errors
2. Check XAMPP error logs
3. Verify all files are in the correct location
4. Ensure database and tables exist 