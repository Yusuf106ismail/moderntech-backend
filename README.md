# ModernTech Solutions HR System - Backend API (Phase 2)

## Overview
This repository contains the backend infrastructure for the ModernTech HR System. Built with PHP and MySQL running on port 3307, this service provides RESTful API endpoints for employee CRUD management, session-based authentication, and time-off request handling.

## Technologies Used
* **Backend:** PHP 8.x
* **Database:** MariaDB / MySQL (Port 3307)
* **Authentication:** Native PHP Sessions with `password_hash()` and `password_verify()`
* **Security:** Parameterized prepared statements (PDO) and environment isolation via `.env`

## Local Setup Instructions
1. **Start MariaDB on Port 3307:**
   ```bash
   mysqld_safe --port=3307 &
DB_HOST=127.0.0.1
DB_PORT=3307
DB_NAME=moderntech_hr
DB_USER=root
DB_PASS=
php -S 127.0.0.1:8000
