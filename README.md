# Luxe Voyage - Travel Agency & Luxury Hotel Booking System

A premium hotel booking platform with three user roles: Admin, Host, and Customer.

##  Quick Start Guide (XAMPP on Windows)

### Prerequisites
1. **XAMPP** installed on Windows
2. **Web Browser** (Chrome, Firefox, etc.)
3. **Text Editor** (VS Code, Notepad++, etc.)

### Step 1: Setup XAMPP.
1. **Download and Install XAMPP** from [Apache Friends](https://www.apachefriends.org/)
2. **Start XAMPP Control Panel**
3. **Start Services**:
   - Click "Start" for **Apache**
   - Click "Start" for **MySQL**

### Step 2: Setup Project
1. **Clone/Download the project** to your local machine
2. **Move project folder** to XAMPP's `htdocs` directory:


### Step 3: Import Database
**Method A: Using phpMyAdmin (Recommended)**
1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click **"New"** in left sidebar
3. Enter database name: `luxe_voyage`
4. Click **"Create"**
5. Go to **"Import"** tab
6. Click **"Choose File"** and select `database.sql` from project folder
7. Click **"Go"** at bottom

**Method B: Using MySQL Command Line**
1. Open Command Prompt as Administrator
2. Navigate to XAMPP MySQL bin:
```cmd
cd C:\xampp\mysql\bin
```
mysql -u root -p < "C:\xampp\htdocs\Luxe-Voyage\database.sql":


### Step 5: Set File Permissions
** Create uploads directory manually or run:

```cmd
mkdir C:\xampp\htdocs\Luxe-Voyage\uploads
mkdir C:\xampp\htdocs\Luxe-Voyage\uploads\destinations
mkdir C:\xampp\htdocs\Luxe-Voyage\uploads\hotels
mkdir C:\xampp\htdocs\Luxe-Voyage\uploads\profiles
```
### To run
 on Your browser paste the following file path; http://localhost/Luxe-Voyage/index.php to view the front end
### 📄 License
 This project is for educational purposes.

### 👨‍💻 Development Team
~ Just check the contributors







