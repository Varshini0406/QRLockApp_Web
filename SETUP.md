# QRLockApp Web — Setup Guide

## What you get

| File | Purpose |
|------|---------|
| `index.html` | The complete web app — open this in any browser |
| `backend/` | Fixed PHP API files — copy to your XAMPP/server |
| `backend/qrlockapp_schema.sql` | Database schema — run once to create all tables |

---

## Step 1 — Install XAMPP (if you haven't already)

1. Download XAMPP from **https://www.apachefriends.org**
2. Install it (default path: `C:\xampp` on Windows, `/Applications/XAMPP` on Mac)
3. Open the **XAMPP Control Panel**
4. Click **Start** next to **Apache** and **MySQL**
5. Both should show green — Apache (port 80) and MySQL (port 3306)

---

## Step 2 — Copy backend files

1. Open your XAMPP folder → go to `htdocs/`
2. Create a new folder called **`QrLockApp`**
3. Copy **all files from the `backend/` folder** into `htdocs/QrLockApp/`

Your folder should look like:
```
htdocs/
  QrLockApp/
    DBconfig.php
    login.php
    register.php
    fetchlock.php
    updatelock.php
    add_device.php
    device_logs.php
    notification.php
    feedback.php
    change_password.php
    forgot_password.php
    Usermanagement.php
    qrlockapp_schema.sql
```

---

## Step 3 — Create the database

1. Open your browser and go to **http://localhost/phpmyadmin**
2. Click **"New"** in the left sidebar
3. Type `qrlockapp` as the database name, choose `utf8mb4_unicode_ci`, click **Create**
4. Click on `qrlockapp` in the left sidebar
5. Click the **"SQL"** tab at the top
6. Open `qrlockapp_schema.sql` in Notepad, copy all the text, paste it into the SQL box
7. Click **Go** — all tables will be created

---

## Step 4 — Test the backend

Open your browser and visit:
```
http://localhost/QrLockApp/login.php
```
You should see:
```json
{"status":false,"message":"Method not allowed"}
```
That means PHP is working correctly.

---

## Step 5 — Open the web app

1. Open `index.html` in your browser (double-click it, or drag it into Chrome/Firefox)
2. Go to **Settings** (bottom of the left sidebar)
3. Under **API Configuration**, enter:
   ```
   http://localhost/QrLockApp
   ```
4. Click **Save**, then click **Test** — it should say "Connected"
5. Now go back to the login screen and register an account

**Or use the demo account** (created by the seed data):
- Mobile: `9876543210`
- Password: `demo123`

---

## Troubleshooting

### "Could not reach server"
- Make sure Apache and MySQL are running in XAMPP Control Panel
- Make sure the folder name is exactly `QrLockApp` (capital Q, L, A)
- Try visiting `http://localhost/QrLockApp/login.php` in your browser directly

### phpMyAdmin SQL errors
- Make sure you selected the `qrlockapp` database before running the SQL
- Run the schema in one go (select all, paste, click Go)

### Login fails after registering
- Check that the `signup` table was created in phpMyAdmin
- Make sure PHP version is 7.4 or higher (XAMPP → Apache → PHP Info)

### Blank page on PHP files
- Enable error display: in XAMPP go to `php.ini`, find `display_errors = Off`, change to `On`, restart Apache

---

## Features

| Feature | Works with backend | Works offline (demo) |
|---------|-------------------|----------------------|
| Login / Register | ✓ | — |
| Add / view devices | ✓ | ✓ (demo data) |
| Lock / Unlock | ✓ | ✓ (local only) |
| QR Scanner | ✓ | ✓ |
| User Management | ✓ | — |
| Notifications | ✓ | ✓ (local only) |
| Activity Logs | ✓ | — |
| Feedback & Reviews | ✓ | — |
| Change / Reset Password | ✓ | — |

---

## Security notes (for production)

The backend has been improved over the original iOS app:
- ✓ Passwords are now hashed with bcrypt (`password_hash`)
- ✓ All queries use prepared statements (SQL injection fixed)
- ✓ Input validation on every endpoint
- ✓ Duplicate device ID check

Before putting this on a public server, also:
- Change the MySQL root password and update `DBconfig.php`
- Restrict `Access-Control-Allow-Origin` to your actual domain
- Add session token / JWT authentication to all endpoints
- Use HTTPS

---

*QRLockApp Web — Built from the iOS source*
