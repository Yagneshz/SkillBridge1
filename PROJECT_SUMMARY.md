# SkillBridge Project Summary

## Project Overview
SkillBridge is a comprehensive PHP-based web application designed to connect students with companies for internship opportunities. The system features three main panels: Admin, Company, and Student, each with specific functionalities.

## Project Structure

### Core Files
- `index.php` - Home page
- `database.sql` - Database schema and initial data
- `README.md` - Project documentation
- `INSTALLATION.md` - Installation guide
- `.htaccess` - Apache configuration
- `.gitignore` - Git ignore rules

### Configuration (`config/`)
- `config.php` - Main configuration (BASE_URL, paths, helper functions)
- `database.php` - Database connection settings

### Authentication (`auth/`)
- `login.php` - User login page
- `register.php` - User registration page
- `logout.php` - Logout handler

### Student Panel (`student/`)
- `dashboard.php` - Student dashboard with statistics
- `profile.php` - Student profile management (CRUD)
- `internships.php` - Browse internships with search
- `internship_details.php` - View internship details and apply
- `applications.php` - View application status
- `companies.php` - Browse companies
- `company_profile.php` - View company profile

### Company Panel (`company/`)
- `dashboard.php` - Company dashboard with statistics
- `profile.php` - Company profile management (CRUD)
- `internships.php` - Manage posted internships
- `internship_add.php` - Post new internship
- `internship_edit.php` - Edit existing internship
- `applications.php` - Manage internship applications (accept/reject)

### Admin Panel (`admin/`)
- `dashboard.php` - Admin dashboard with platform statistics
- `companies.php` - Manage all companies
- `students.php` - Manage all students

### Includes (`includes/`)
- `header.php` - Common header with navigation
- `footer.php` - Common footer with scripts

### Assets (`assets/`)
- `css/style.css` - Custom styles
- `js/main.js` - JavaScript functions

### Uploads (`uploads/`)
- `resumes/` - Student resume files
- `profiles/` - Student profile images
- `logos/` - Company logos

## Key Features Implemented

### ✅ Authentication & Security
- User registration (Student/Company)
- Login with case-sensitive username matching
- Password hashing (bcrypt)
- Session management
- SQL injection prevention (prepared statements)
- XSS protection (input sanitization)
- User type verification

### ✅ Student Features
- Profile CRUD operations
- Browse internships with search
- View company profiles
- Apply for internships (with duplicate prevention)
- View application status
- Dashboard with charts and statistics

### ✅ Company Features
- Profile CRUD operations
- Post internships (CRUD)
- Prevent duplicate internship postings (same title)
- Accept/reject applications
- View student profiles
- Dashboard with charts and statistics

### ✅ Admin Features
- View all companies and students
- Update user status (active/inactive)
- Delete users
- Dashboard with platform statistics

### ✅ UI/UX Features
- Modern, responsive design
- Smooth animations (AOS library)
- Hover effects and transitions
- Charts and statistics (Chart.js)
- Search functionality
- Pagination
- Back/forward navigation
- Form validations
- Alert messages (SweetAlert2)

### ✅ Validations
- Username uniqueness (case-sensitive)
- Email format validation
- Password strength requirements
- Duplicate application prevention
- Duplicate internship title prevention
- Form field validations
- File upload validations

## Database Schema

### Tables
1. **users** - User accounts (admin, student, company)
2. **students** - Student profiles
3. **companies** - Company profiles
4. **internships** - Internship postings
5. **applications** - Student applications

### Relationships
- users → students (1:1)
- users → companies (1:1)
- companies → internships (1:many)
- students → applications (1:many)
- internships → applications (1:many)

## Technology Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **UI Framework:** Bootstrap 5
- **Icons:** Font Awesome 6
- **Charts:** Chart.js
- **Animations:** AOS (Animate On Scroll)
- **Alerts:** SweetAlert2

## Default Credentials

**Admin:**
- Username: `admin`
- Password: `admin123`

## File Permissions Required

- `uploads/` directory: 755 (read/write)
- `uploads/resumes/`: 755
- `uploads/profiles/`: 755
- `uploads/logos/`: 755

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Security Features

1. Password hashing using PHP `password_hash()`
2. Prepared statements for SQL queries
3. Input sanitization using `htmlspecialchars()`
4. Session-based authentication
5. User type verification on protected pages
6. File upload validation
7. XSS protection
8. SQL injection prevention

## Future Enhancements (Optional)

- Email notifications
- Password reset functionality
- Advanced search filters
- Export data to PDF/Excel
- Real-time notifications
- Messaging system
- Rating/review system
- Advanced analytics

## Notes

- All validations are implemented as requested
- Duplicate prevention for applications and internships
- Case-sensitive username matching
- Back/forward buttons on all pages
- Smooth pagination throughout
- Search functionality where needed
- Charts and statistics in all dashboards
- Modern UI with animations

## Support

For installation help, refer to `INSTALLATION.md`
For feature documentation, refer to `README.md`

