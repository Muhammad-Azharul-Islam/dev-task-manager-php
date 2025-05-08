# TechFlow - Project Management System

TechFlow is a comprehensive project management system designed to help businesses manage their digital projects efficiently. The system provides features for project tracking, team collaboration, and client feedback management.

## Features

### User Management
- User registration and authentication
- Profile management with profile picture upload
- Role-based access control (Admin, Developer, Client)
- Secure password handling

### Project Management
- Create and manage projects
- Track project status (New, In Progress, Completed)
- Assign developers to projects
- Project filtering by status and developer
- Project details with client information

### Feedback System
- Client feedback submission
- Feedback status tracking (New, In Progress, Resolved)
- Recent testimonials display
- Feedback management dashboard

### Dashboard
- Overview of projects and their status
- Recent feedback and testimonials
- Quick access to project management tools
- User profile management

## Technical Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: 
  - HTML5
  - CSS3 (Tailwind CSS)
  - JavaScript (jQuery)
- **Additional Libraries**:
  - Tailwind CSS for styling
  - jQuery for AJAX and DOM manipulation

## Directory Structure

```
TechFlow/
├── api/                    # API endpoints
├── assets/                 # Static assets
│   ├── css/               # CSS files
│   ├── js/                # JavaScript files
│   └── uploads/           # Uploaded files (profile pictures)
├── classes/               # PHP classes
│   ├── Database.php
│   ├── Project.php
│   ├── User.php
│   └── Feedback.php
├── config/                # Configuration files
│   └── database.php
├── dashboard/             # Dashboard pages
│   ├── index.php
│   ├── projects.php
│   └── profile.php
├── includes/              # Reusable components
│   ├── header.php
│   └── footer.php
├── index.php             # Home page
├── login.php             # Login page
├── register.php          # Registration page
└── README.md             # Project documentation
```

## Setup Instructions

1. **Prerequisites**
   - PHP 7.4 or higher
   - MySQL 5.7 or higher
   - Web server (Apache/Nginx)
   - XAMPP/WAMP/MAMP (recommended for local development)

2. **Installation**
   - Clone the repository to your web server directory
   - Create a MySQL database named `project_management`
   - Import the database schema from `database.sql`
   - Configure database connection in `config/database.php`
   - Ensure the `assets/uploads` directory is writable

3. **Configuration**
   - Update database credentials in `config/database.php`
   - Configure upload settings in PHP if needed
   - Set appropriate permissions for upload directories

4. **Running the Application**
   - Start your web server and MySQL service
   - Access the application through your web browser
   - Default URL: `http://localhost/TechFlow`

## User Roles

1. **Admin**
   - Full system access
   - User management
   - Project management
   - Feedback management

2. **Developer**
   - View assigned projects
   - Update project status
   - Manage profile

3. **Client**
   - View projects
   - Submit feedback
   - Manage profile

## Security Features

- Password hashing using PHP's password_hash()
- Session-based authentication
- Input validation and sanitization
- Prepared statements for database queries
- XSS protection
- CSRF protection

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request


## Acknowledgments

- Tailwind CSS for the UI framework
- jQuery for JavaScript functionality
