# Netlify Deployment & Architecture Guide

## 1. Why Did the Deployed Site Return a 404 on Netlify?

Your original deployment on `https://elaborate-babka-a84a5c.netlify.app/` showed a **Page not found (404)** error because:
1. **Netlify is a static Jamstack host** — it serves HTML, CSS, JavaScript, and client-side web apps. It does not execute PHP scripts or run a MySQL server.
2. Netlify's deployment engine looks for an **`index.html`** file in the root directory by default.
3. Your repository previously contained only **`index.php`** at the root. Because no `index.html` was found, Netlify had no entry point to serve and returned a 404.

---

## 2. The Solution Implemented

To resolve the 404 while preserving all PHP backend functionality:
1. **Created Root `index.html`**:
   - Acts as the primary Netlify entry point.
   - Provides a portal landing page with direct routing to Student, Faculty, Admin, and React Analytics.
   - Includes a **Backend Connection Settings** panel where you can specify your live PHP backend URL (e.g., your future cloud host or `http://localhost:8000` for local testing).
2. **Created `netlify.toml`**:
   - Sets `publish = "."` (publish directory is the repository root).
   - Configures required security and MIME type headers for JavaScript and CSS.
3. **Preserved Local XAMPP & Apache with `DirectoryIndex`**:
   - In `.htaccess`, added `DirectoryIndex index.php index.html`.
   - On Apache (XAMPP & PHP web servers), `index.php` is prioritized, redirecting to the login portal.
   - On Netlify (which has no PHP), `index.html` is served, eliminating the 404.
4. **Enhanced React Dashboard (`react-dashboard/index.html`)**:
   - Added dynamic backend routing so it can fetch telemetry data from your hosted PHP backend or display rich fallback data.

---

## 3. How the Hybrid Architecture Works

| Component | Hosted On | What It Does |
| :--- | :--- | :--- |
| **Static Portal & Telemetry** | **Netlify** (`elaborate-babka-a84a5c.netlify.app`) | Serves the homepage, navigation cards, and the interactive React & Chart.js analytics engine. |
| **PHP Backend & MySQL** | **PHP Cloud Server** / **Local XAMPP** | Executes authentication, sessions, attendance recording, marks submission, database queries, and file uploads. |

---

## 4. Netlify Dashboard Settings

When you push this repository to GitHub, Netlify will automatically detect the changes and rebuild. Verify these settings in your Netlify dashboard:

1. Open **[app.netlify.com](https://app.netlify.com)** &rarr; Click your site (`elaborate-babka-a84a5c`).
2. Go to **Site configuration** &rarr; **Build & deploy** &rarr; **Continuous deployment**.
3. Confirm settings:
   - **Repository**: `Aakash-002-rec/Smart-Campus-Management-System`
   - **Branch to deploy**: `master`
   - **Base directory**: *(leave empty)*
   - **Build command**: *(leave empty)*
   - **Publish directory**: `.` *(or leave empty, as `netlify.toml` sets this automatically)*
4. Click **Trigger deploy** &rarr; **Deploy site**.

---

## 5. Connecting Netlify to Your Hosted PHP Backend

Once your Netlify site loads:
1. Open `https://elaborate-babka-a84a5c.netlify.app/`.
2. In the **PHP & MySQL Application Server Link** panel at the bottom:
   - Enter your hosted PHP URL (for example: `https://your-php-server.com` or `http://localhost:8000`).
   - Click **Save Server URL**.
3. All portal buttons (**Access Student**, **Access Faculty**, **Access Admin**, **Open Login Portal**) will route to your live PHP host.

---

## 6. Feature Availability Matrix

| Feature | Supported on Netlify Alone? | Requires PHP Backend Host? |
| :--- | :---: | :---: |
| Homepage & Portal Navigation |  Yes | No |
| React & Chart.js Telemetry Engine |  Yes (Client-Side) | Optional (Live data when backend connected) |
| User Login & Role Authorization | ❌ No |  Yes (`backend/auth/login_process.php`) |
| Attendance Session Marking | ❌ No |  Yes (`backend/faculty/save_session_attendance.php`) |
| Assessment Marks Entry | ❌ No |  Yes (`backend/faculty/save_student_marks.php`) |
| Assignment File Uploads / Downloads | ❌ No |  Yes (`uploads/` directory on PHP host) |
| Student Records & Subject Enrollment | ❌ No |  Yes (`backend/admin/` + MySQL) |
