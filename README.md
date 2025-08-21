# AutoClean - Car Wash Booking System

A modern web-based car wash booking system built with PHP, MySQL, and vanilla JavaScript. The system supports multiple user types (customers, admins, workers) with role-based dashboards and booking management.

## 🚀 Features

### Customer Features
- **User Registration & Login**: Secure authentication system
- **Service Booking**: Book car/bike wash services with multiple packages
- **Dashboard**: View booking history, status, and account details
- **Real-time Updates**: Live booking status tracking

### Admin Features
- **Dashboard Overview**: Statistics, recent bookings, and system metrics
- **Booking Management**: View, update, and manage all bookings
- **Worker Assignment**: Assign workers to specific bookings
- **Customer Management**: View customer information and booking history
- **Service Package Management**: Manage available service packages

### Worker Features
- **Task Dashboard**: View assigned bookings and work schedule
- **Status Updates**: Update booking status (pending → in-progress → completed)
- **Work History**: Track completed jobs and earnings

## 🛠️ Technology Stack

- **Frontend**: HTML5, Tailwind CSS, Vanilla JavaScript
- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Server**: Apache (XAMPP)
- **Icons**: Font Awesome
- **Fonts**: Google Fonts

## 📁 Project Structure

```
web/
├── admin/
│   └── dashboard.html          # Admin dashboard
├── api/                        # PHP API endpoints
│   ├── config.php             # Database configuration
│   ├── login.php              # User authentication
│   ├── register.php           # User registration
│   ├── book_service.php       # Booking creation
│   ├── get_admin_dashboard.php # Admin dashboard data
│   ├── get_dashboard_data.php # Customer dashboard data
│   └── ...                    # Other API endpoints
├── assets/
│   ├── css/                   # Stylesheets
│   ├── js/                    # JavaScript files (merged into HTML)
│   └── images/                # Static images
├── customer/
│   └── dashboard.html         # Customer dashboard
├── worker/
│   └── dashboard.html         # Worker dashboard
├── database/
│   └── autoclean_bd.sql       # Database schema
├── index.html                 # Landing page
├── login.html                 # Login page
├── register.html              # Registration page
└── README.md                  # This file
```

## 🗄️ Database Schema

### Core Tables
- **users**: Customer, admin, and worker accounts
- **service_packages**: Available wash packages and pricing
- **bookings**: All booking records with status tracking
- **workers**: Worker profiles and availability
- **reviews**: Customer feedback and ratings
- **contact_messages**: Customer support messages

### Key Relationships
- Bookings link to users (customers) and workers
- Service packages define pricing for different vehicle types
- Reviews are linked to bookings and customers

## 🚀 Installation & Setup

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- PHP 8.0 or higher
- MySQL 8.0 or higher

### Installation Steps

1. **Clone/Download the Project**
   ```bash
   # Place the project in your XAMPP htdocs folder
   C:\xampp\htdocs\web\
   ```

2. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

3. **Database Setup**
   ```bash
   # Access MySQL CLI
   C:\xampp\mysql\bin\mysql.exe -u root
   
   # Create database
   CREATE DATABASE autoclean_bd;
   USE autoclean_bd;
   
   # Import schema (if you have the SQL file)
   SOURCE C:/xampp/htdocs/web/database/autoclean_bd.sql;
   ```

4. **Database Configuration**
   - The system will automatically create tables on first run
   - Default admin credentials: `admin@autoclean.com` / `admin123`

5. **Access the Application**
   ```
   http://localhost/web/
   ```

## 👥 User Types & Access

### Default Admin Account
- **Email**: admin@autoclean.com
- **Password**: admin123
- **Access**: Full system administration

### Customer Registration
- Public registration available
- Email verification (optional)
- Dashboard access after login

### Worker Accounts
- Created by admin
- Assigned to specific bookings
- Limited dashboard access

## 🔧 API Endpoints

### Authentication
- `POST /api/login.php` - User login
- `POST /api/register.php` - User registration
- `POST /api/logout.php` - User logout

### Booking Management
- `POST /api/book_service.php` - Create new booking
- `POST /api/update_booking_status.php` - Update booking status
- `POST /api/assign_worker.php` - Assign worker to booking

### Dashboard Data
- `POST /api/get_admin_dashboard.php` - Admin dashboard data
- `POST /api/get_dashboard_data.php` - Customer dashboard data
- `POST /api/get_contact_messages.php` - Contact form messages

### User Management
- `POST /api/add_worker.php` - Add new worker
- `POST /api/update_profile.php` - Update user profile
- `POST /api/add_review.php` - Add customer review

## 🎨 UI/UX Features

### Design System
- **Framework**: Tailwind CSS (via CDN)
- **Icons**: Font Awesome
- **Typography**: Google Fonts (Inter, Poppins)
- **Color Scheme**: Modern blue/white theme

### Responsive Design
- Mobile-first approach
- Tablet and desktop optimized
- Touch-friendly interface

### Interactive Elements
- Modal dialogs for forms
- Toast notifications
- Loading states
- Form validation

## 🔒 Security Features

### Authentication
- Password hashing (PHP password_hash)
- Session management
- Role-based access control

### Data Protection
- Input sanitization
- SQL injection prevention (PDO prepared statements)
- XSS protection

### API Security
- CORS handling
- Request validation
- Error logging

## 📊 Dashboard Features

### Admin Dashboard
- **Statistics**: Total bookings, revenue, active workers
- **Recent Bookings**: Latest 10 bookings with status
- **Customer Overview**: Top customers by bookings/spending
- **Quick Actions**: Add workers, manage packages

### Customer Dashboard
- **Booking History**: All past and current bookings
- **Account Info**: Profile management
- **Quick Booking**: Direct booking from dashboard
- **Reviews**: Leave feedback for completed services

### Worker Dashboard
- **Assigned Jobs**: Current and upcoming bookings
- **Work History**: Completed jobs and earnings
- **Status Updates**: Mark jobs as in-progress/completed

## 🚀 Deployment

### Local Development
1. Use XAMPP for local development
2. Access via `http://localhost/web/`
3. Database: `localhost/autoclean_bd`

### Production Deployment
1. Upload files to web server
2. Configure database connection in `api/config.php`
3. Set up SSL certificate for HTTPS
4. Configure Apache/Nginx virtual hosts

## 🐛 Troubleshooting

### Common Issues

**Database Connection Failed**
- Check XAMPP MySQL service is running
- Verify database credentials in `api/config.php`
- Ensure database `autoclean_bd` exists

**Login Issues**
- Clear browser cache and localStorage
- Check user exists in database
- Verify password hashing is working

**Booking Not Appearing**
- Check booking was saved to database
- Verify API endpoints are accessible
- Check browser console for JavaScript errors

**Dashboard Not Loading**
- Check user authentication
- Verify API responses
- Check network connectivity

### Debug Tools
- Browser Developer Tools (F12)
- PHP error logs in XAMPP
- MySQL query logs
- Network tab for API calls

## 📝 Development Notes

### Code Organization
- **Frontend**: Vanilla JavaScript (no frameworks)
- **Backend**: Procedural PHP with PDO
- **Database**: MySQL with snake_case naming
- **Styling**: Tailwind CSS utility classes

### Key Functions
- `sendJSONResponse()` - Standardized API responses
- `generateBookingId()` - Unique booking ID generation
- `checkAuth()` - Authentication verification
- `showToast()` - User notification system

### File Merging
- JavaScript files have been merged into respective HTML files
- No external JS dependencies
- Self-contained dashboard pages

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 📞 Support

For support and questions:
- Check the troubleshooting section
- Review API documentation
- Test with provided sample data

## 🔄 Version History

- **v1.0.0** - Initial release with core booking functionality
- **v1.1.0** - Added admin dashboard and worker management
- **v1.2.0** - Enhanced UI/UX and mobile responsiveness
- **v1.3.0** - Fixed database schema and API endpoints

---

**Built with ❤️ for efficient car wash management**
