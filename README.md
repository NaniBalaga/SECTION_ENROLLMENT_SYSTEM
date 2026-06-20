# 📘 Section Enrollment System

The **Section Enrollment System** is a responsive web-based platform designed to help students join and manage class sections efficiently. It integrates seamlessly with the student database and offers real-time insights into section compositions, making it easier for students and administrators to handle section enrollments.

---

## 🔍 Overview

This system provides a user-friendly interface for students to:
- Enroll in available class sections
- View current section members
- Switch between sections when needed

It is mobile-friendly, secure, and built with modern UI elements for the best user experience.

---


## ✨ Features

### 🎓 Core Functionality
- **Section Enrollment**: Easily join available class sections.
- **Section Leaving**: Leave your current section anytime to switch.
- **Section Browsing**: View all available sections with total member count.
- **Classmate Preview**: Get a preview of classmates before joining.
- **Detailed Member Lists**: Expand to view all members in a section.

### 💡 User Interface
- Modern responsive design with gradient accents
- Animated modals and visual indicators for joined sections
- Filter and search functionality for quick access
- Interactive cards for each section with live counts

### ⚙️ Technical Features
- **Session-based authentication**
- **AJAX** for dynamic section member display
- **MySQL integration** for real-time updates
- **Mobile-first layout** and adaptive components
- Form input validation and error handling

---

## 🧰 Installation

### ✅ Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache or Nginx web server
- Composer (optional, for future dependency management)

### 🚀 Setup Steps
1. **Clone the Repository**  
   Upload the project to your web server directory.

2. **Database Setup**  
   Create a MySQL database and import the schema provided.

3. **Configuration**  
   Update your `config.php` file with correct database credentials:

   ```php
   $servername = "localhost";
   $username = "your_username";
   $password = "your_password";
   $dbname = "your_database";
