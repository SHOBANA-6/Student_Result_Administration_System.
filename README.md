# Student_Result_Administration_System.
A web-based system for managing student academic results. It allows admins to add, update, and view student marks, calculate totals, determine pass/fail status, and generate reports efficiently using PHP, MySQL, HTML, CSS, and JavaScript.

# Student Result Administration System

A comprehensive web-based application designed to manage and publish student academic results efficiently. This system provides a secure portal for administrators to manage student data and a simple interface for students to view their performance.



***

## ✨ Features

The system is divided into two main modules: an Admin Panel and a Student Portal.

### 👨‍💼 Admin Panel

- *Secure Login*: Password-protected access for administrators.
- *Dashboard*: A central hub with quick access to all management modules.
- *Manage Students*: Full CRUD (Create, Read, Update, Delete) functionality for student records.
- *Manage Subjects*: Full CRUD functionality for academic subjects.
- *Manage Results*: A dedicated interface to add or update student marks for various assessments (CIA 1, CIA 2, Semester Exam). Internal and total marks are calculated automatically.
- *Reports*: A comprehensive view of all student results with options to filter by subject or register number.

### 🧑‍🎓 Student Portal

- *Secure Result Access: Students can view their results privately by entering their unique **Register Number* and *Date of Birth*.
- *Clean Result Display*: Results are presented in a clear, well-structured table, showing a complete breakdown of marks.
- *Dynamic Interface*: The result is fetched and displayed without a page reload for a smoother user experience.

***

## 🛠 Technologies Used

- *Frontend*: HTML, CSS, JavaScript
- *Backend*: PHP
- *Database*: MySQL
- *Server Environment*: [Laragon](https://laragon.org/) (includes Apache and MySQL)

***

## 🚀 Setup and Installation

Follow these steps to set up the project on your local machine.

### 1. Prerequisites

- You must have [Laragon](https://laragon.org/download/) or a similar WAMP/LAMP/MAMP stack installed.

### 2. Installation Steps

1.  *Download the Project*
    - Download the project files and extract them.
    - Place the project folder (e.g., result-system) inside your Laragon's www directory (C:\laragon\www\).

2.  *Start Services*
    - Open Laragon and click *"Start All"* to run Apache and MySQL.

3.  *Database Setup*
    - Click the *"Database"* button in Laragon to open phpMyAdmin.
    - Create a new database named result_system.
    - Select the result_system database and go to the *"SQL"* tab.
    - Copy the entire content of the provided SQL file (or the SQL code from the initial setup) and run it. This will create all the necessary tables (admin, students, subjects, results) and insert the default admin user.

4.  *Configure Database Connection*
    - Open the file php/db_connect.php.
    - Ensure the database credentials match your Laragon setup (by default, the user is root and the password is an empty string).
    php
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = ''; // Default password for Laragon is empty
    $db_name = 'result_system';
    

***

## 🏃‍♀ How to Run the Application

1.  *Access the Homepage*
    - Open your web browser and navigate to the project URL. This will be http://result-system.test (if using Laragon's pretty URLs) or http://localhost/result-system/.

2.  *Admin Login*
    - From the homepage, click on *"Admin Login"*.
    - Use the default credentials to log in:
      - *Username*: admin
      - *Password*: admin123
    - You will be redirected to the admin dashboard, where you can start managing students, subjects, and results.

3.  *Student Result View*
    - First, add a student, a subject, and their result via the admin panel.
    - From the homepage, click on *"Student Login"*.
    - Enter the *Register Number* and *Date of Birth* of the student whose result you want to check.
    - The result will be displayed on the screen.

***

## 📂 Project File Structure
