# Karaoke Rental System

A web-based application for managing karaoke machine rentals, supporting both user and admin roles. Built with PHP and MySQL, featuring a modern dark-mode UI.

## Features

### User Side
- User registration and login
- Dashboard with booking stats and notifications
- Book karaoke units with real-time availability
- View booking and payment history
- Pay for bookings
- Receive notifications when bookings are approved

### Admin Side
- Admin login
- Dashboard with all bookings and unit stats
- Approve (confirm) or cancel bookings
- Mark bookings as "Returned" to increase available units
- Manage total number of karaoke units

## Setup Instructions

### 1. Requirements
- PHP 7.4+
- MySQL/MariaDB
- Web server (Apache recommended)

### 2. Installation
1. **Clone or copy the repository to your web server directory.**
2. **Import the database:**
   - Open `karaoke-rental.sql` in your MySQL client and run it to create the database and tables.
3. **Configure database connection:**
   - Edit `karaoke-rental/includes/db.php` if you need to set your own DB credentials.
4. **Start your web server and access the app in your browser.**

### 3. Default Admin/User Credentials (for testing)
- **Admin:**
  - Email: `admin@demo.com`
  - Password: `1234`
- **User:**
  - Email: `Junalvior21@gmail.com`
  - Password: `1234`

## Usage
- **Users** can register, log in, book units, pay, and view their history.
- **Admins** can log in, manage bookings, confirm/cancel/return units, and update total units.

## Customization
- All navigation and theming is handled in `includes/nav.php`, `includes/header.php`, and the `assets/` folder.
- To change the number of available units, use the admin "Manage Units" page.

## License
- This project is for educational/demo purposes. Customize and use as needed!

---

## Author
**This project is created by Jun Alvior.**

If you would like a copy of this project, please ask for permission from me. Permission will be granted upon request. 
