# VBAMS InfinityFree Deployment Guide

## Pre-Deployment Checklist
- ✅ Database config updated to: if0_42013470 / if0_42013470
- ✅ Code ready for upload
- ✅ GitHub repository: https://github.com/k4ranAAthreya/VBAMS

---

## Step 1: Upload Files to InfinityFree

### Option A: Using File Manager (Recommended)
1. Log in to **InfinityFree Control Panel**
2. Go to **Files → File Manager**
3. Navigate to **public_html** folder
4. **Delete or rename** any existing `vehicleassitancems` folder
5. **Create new folder**: `vehicleassitancems`
6. Download this folder locally:
   ```
   C:\xampp\htdocs\final VBAMS - Copy\final VBAMS - Copy\karan clone - Copy\Vehicle-Breakdown-Assistant-main\vehicleassitancems
   ```
7. Upload all files and folders into the new `vehicleassitancems` folder

### Option B: Using FTP (FileZilla)
1. Download **FileZilla** (https://filezilla-project.org)
2. Use these credentials:
   - **Host**: ftp.gamer.gd
   - **Username**: if0_42013470
   - **Password**: bGOqmJFSHJ
3. Navigate to: `public_html/`
4. Create/delete `vehicleassitancems` folder
5. Upload all files from your local `vehicleassitancems` folder

---

## Step 2: Set Up Database

1. Log in to **InfinityFree Control Panel**
2. Go to **Databases**
3. Click **PhpMyAdmin** next to database `if0_42013470`
4. In PhpMyAdmin:
   - Select database: `if0_42013470`
   - Go to **Import** tab
   - Click **Choose File**
   - Select: `vehassitancemsdb.sql` from your SQL File folder
   - Click **Import**
5. Wait for success message

---

## Step 3: Verify Directory Structure

After uploading, your folder structure should look like:
```
public_html/
└── vehicleassitancems/
    ├── index.php
    ├── about-us.php
    ├── booking-request.php
    ├── payment-confirm.php
    ├── track-service.php
    ├── admin/
    ├── driver/
    ├── api/
    ├── includes/
    ├── css/
    ├── css1/
    ├── js/
    ├── assets/
    └── ... (other folders/files)
```

---

## Step 4: Test Your Site

### Access the Site
- **Frontend**: http://vbams.gamer.gd
- **Admin Panel**: http://vbams.gamer.gd/admin/

### Default Credentials

**Admin Login:**
- Username: `admin`
- Password: `Test@123`

**Driver Login:**
- Username: `test123`
- Password: `Test@123`

**Customer:**
- Create a new booking request

---

## Troubleshooting

### Issue: Blank White Page
**Solution:**
1. Go to `admin/login.php` and check if you see error
2. Contact hosting support to enable PHP error display
3. Check if `includes/dbconnection.php` exists

### Issue: 404 Not Found
**Solution:**
1. Verify files are in `public_html/vehicleassitancems/`
2. Make sure `index.php` exists
3. Try accessing directly: `http://vbams.gamer.gd/admin/login.php`

### Issue: Database Connection Error
**Solution:**
1. Verify database name: `if0_42013470`
2. Verify MySQL was created in InfinityFree
3. Verify SQL was imported (check tables exist)
4. Check `includes/dbconnection.php` has correct credentials

### Issue: CSS/Images Not Loading
**Solution:**
1. Make sure all files were uploaded correctly
2. Check folder permissions (should be 755)
3. Verify `css/`, `css1/`, `js/`, `assets/` folders exist

### Issue: Admin/Driver Login Not Working
**Solution:**
1. Make sure database was imported successfully
2. Try default credentials again: `admin` / `Test@123`
3. Check if `users` table exists in database

---

## Quick Fix Checklist

If the site still isn't working:

- [ ] Files uploaded to `public_html/vehicleassitancems/`?
- [ ] Database `if0_42013470` created?
- [ ] SQL file imported?
- [ ] Database connection working?
- [ ] Tried both `/` and `/admin/` paths?
- [ ] All folders uploaded (admin, driver, includes, css, js, assets)?
- [ ] File permissions set to 755?

---

## Need Help?

1. Check errors in browser console (F12)
2. Try accessing: `http://vbams.gamer.gd/admin/login.php`
3. Check hosting support for PHP/MySQL issues
4. GitHub: https://github.com/k4ranAAthreya/VBAMS

