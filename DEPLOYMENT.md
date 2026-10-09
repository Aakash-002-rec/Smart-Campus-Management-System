# Smart Campus Management System — Production Deployment Guide

This guide describes how to deploy the **Smart Campus Management System** directly from GitHub to a cloud hosting environment running PHP and managed MySQL.

---

## 1. Hosting Architecture Overview

- **Primary Recommended Platform**: **[Railway](https://railway.com)**
  - **Runtime**: PHP 8.2 on Apache (using the project's production `Dockerfile`)
  - **Database**: Managed MySQL 8 service on Railway
  - **Deployment Trigger**: Automatic continuous deployment on `git push origin master`
  - **SSL**: Automatic HTTPS on generated `*.up.railway.app` domain or custom domain
  - **Storage**: Persistent Volume support mounted to `/var/www/html/uploads` for student & faculty assignment files

---

## 2. Environment Variables Reference

The application dynamically detects standard database environment variables in production. When running locally in XAMPP, it automatically falls back to `127.0.0.1:3306`, user `root`, no password, and database `smart_campus`.

| Variable | Description | Railway Value Example |
| :--- | :--- | :--- |
| `MYSQLHOST` / `DB_HOST` | MySQL hostname | `${{MySQL.MYSQLHOST}}` or railway host |
| `MYSQLPORT` / `DB_PORT` | MySQL connection port | `${{MySQL.MYSQLPORT}}` (default `3306`) |
| `MYSQLUSER` / `DB_USER` | MySQL database user | `${{MySQL.MYSQLUSER}}` |
| `MYSQLPASSWORD` / `DB_PASS` | MySQL database password | `${{MySQL.MYSQLPASSWORD}}` |
| `MYSQLDATABASE` / `DB_NAME` | MySQL database name | `${{MySQL.MYSQLDATABASE}}` |
| `PORT` | Web server listening port | Automatically assigned by Railway/Render (e.g., `80` or `8080`) |

---

## 3. Step-by-Step Deployment on Railway

### Step 1: Provision the MySQL Database
1. Go to [railway.com](https://railway.com) and log in with your GitHub account.
2. Click **New Project** → **Provision MySQL**.
3. Railway will provision a dedicated MySQL instance in seconds.
4. Click on the newly created MySQL card and navigate to the **Variables** tab to view your credentials (`MYSQLHOST`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`, `MYSQLPORT`).

### Step 2: Import the Database Schema & Demo Data
You can import the prepared `database/smart_campus_deployment.sql` dump using any of the following methods:

#### Method A: Railway Web Data Import (Easiest)
1. In the Railway dashboard, click on the **MySQL** service.
2. Go to the **Data** or **Query** tab.
3. Open `database/smart_campus_deployment.sql` in any text editor, copy the contents, and paste into the Query editor, then click **Run Query**.

#### Method B: MySQL CLI or TablePlus / DBeaver
1. Under your Railway MySQL service **Connect** tab, copy the **Public Networking** connection string or connection details (Host, Port, User, Password).
2. Run from your local terminal:
   ```bash
   mysql -h <RAILWAY_HOST> -P <RAILWAY_PORT> -u <RAILWAY_USER> -p<RAILWAY_PASSWORD> <RAILWAY_DATABASE> < database/smart_campus_deployment.sql
   ```

### Step 3: Deploy the Application from GitHub
1. In the same Railway project canvas, click **+ New** → **GitHub Repo**.
2. Select your repository: `Aakash-002-rec/Smart-Campus-Management-System`.
3. Choose the `master` branch.
4. Railway will automatically detect the `Dockerfile` and begin building the container.

### Step 4: Link Database Environment Variables to the App
1. Click on your Smart Campus web service in Railway.
2. Go to the **Variables** tab.
3. Click **Add Reference** or manually add the variables linking to your MySQL service:
   - `MYSQLHOST` = `${{MySQL.MYSQLHOST}}`
   - `MYSQLPORT` = `${{MySQL.MYSQLPORT}}`
   - `MYSQLUSER` = `${{MySQL.MYSQLUSER}}`
   - `MYSQLPASSWORD` = `${{MySQL.MYSQLPASSWORD}}`
   - `MYSQLDATABASE` = `${{MySQL.MYSQLDATABASE}}`
4. Railway will automatically redeploy the web service with the linked variables.

### Step 5: (Optional but Recommended) Attach Persistent Volume for File Uploads
1. In the Smart Campus web service settings, go to **Volumes**.
2. Click **Add Volume**.
3. Set the Mount Path to `/var/www/html/uploads`.
4. This ensures assignment files and student submissions persist across container redeployments.

### Step 6: Generate Public URL
1. Go to the **Settings** tab of the web service.
2. Under **Networking**, click **Generate Domain**.
3. You will receive an instant HTTPS domain (e.g. `https://smart-campus-management-system-production.up.railway.app`).
4. Click the link to view your live application!

---

## 4. Alternative Host: Render + Managed MySQL (TiDB Cloud / Aiven)

If deploying to **Render**:
1. Create a free MySQL database on **TiDB Cloud** (Serverless Free tier: 5 GB permanent storage) or **Aiven for MySQL**.
2. On [render.com](https://render.com), click **New +** → **Web Service** → Connect your GitHub repository.
3. Runtime: **Docker** (Render uses the root `Dockerfile`).
4. In **Environment Variables**, add:
   - `DB_HOST` = `<Your TiDB / Aiven Host>`
   - `DB_PORT` = `<Your Port>`
   - `DB_USER` = `<Your User>`
   - `DB_PASS` = `<Your Password>`
   - `DB_NAME` = `<Your Database Name>`
5. Click **Create Web Service**.

---

## 5. Demo Accounts for Testing Live Portal

All test accounts use standardized credentials. You can also view and search all 64 enrolled accounts directly from the **Member Directory** modal on the login page:

| Role | Email | Password | Access |
| :--- | :--- | :--- | :--- |
| **Admin** | `admincampus@gmail.com` | `Admin@Campus2026` | Full administrative control, student/faculty records, subject allocation, reports |
| **Faculty** | `aruncampus@gmail.com` | `Faculty@FAC001` | Daily period attendance marking, multi-assessment marks, homework/assignment attachments |
| **Student** | `aakashcampus@gmail.com` | `Aakash@001` | Dashboard, real-time attendance percentage, shortage calculator, marks analytics, assignment submission |

---

## 6. How Future Updates Work

Automatic GitHub deployment is enabled:
1. Make your code changes locally.
2. Commit and push to GitHub:
   ```bash
   git add .
   git commit -m "Your update description"
   git push origin master
   ```
3. The hosting platform automatically detects the push, rebuilds the container, and deploys the latest version without manual intervention.
