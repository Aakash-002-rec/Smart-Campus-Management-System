# Netlify Frontend Deployment Guide

This guide provides the complete documentation and exact deployment instructions for the **Smart Campus Management System** on **Netlify** (`https://elaborate-babka-a84a5c.netlify.app/`).

---

## 1. Deployment Overview & Architecture

The Smart Campus Management System is configured to deploy its frontend on Netlify while preserving the complete PHP/MySQL codebase for local execution in XAMPP.

| Component | Platform / Host | What It Does |
| :--- | :--- | :--- |
| **Static Gateway Homepage** | **Netlify CDN** | High-performance, responsive academic gateway (`index.html`) with role navigation and deployment status. |
| **React Telemetry Engine** | **Netlify CDN** | Interactive client-side React 18 & Chart.js analytics engine (`react-dashboard/index.html`) with multi-assessment charts, early warning alerts, and theme switcher. |
| **Static UI Assets** | **Netlify CDN** | Bootstrap 5, Bootstrap Icons, Google Fonts, and custom CSS styling served directly from CDN and static files. |
| **PHP/MySQL Backend** | **Local XAMPP** (`http://localhost:8000`) | Complete server-side authentication, session state, attendance marking, continuous assessment marks entry, assignment uploads, and database operations. |

---

## 2. Why Netlify is Frontend-Only

1. **Static Jamstack Architecture**: Netlify serves HTML, CSS, client-side JavaScript, and static media via global CDNs. Netlify does not execute PHP scripts or run MySQL database servers.
2. **Transparent User Experience**: When visitors click any PHP portal button on Netlify (**Student Portal**, **Faculty Portal**, **Admin Portal**, or **Open Login Portal**), the application displays a clear, informative modal:
   > *"The PHP/MySQL backend is not connected because this deployment only uses Netlify."*
3. **No Mock or Fake Authentication**: In accordance with system requirements, no fake credentials, mock login handlers, or simulated databases have been introduced. This protects data integrity and ensures the real academic application is not misrepresented.
4. **Zero Third-Party Hosting Dependencies**: All default external hosting URLs (including InfinityFree) have been completely removed.

---

## 3. Configuration Files & 404 Prevention

### Root `index.html`
- Serves as the primary entry point for Netlify.
- Eliminates the previous "Page not found (404)" error that occurred when Netlify attempted to locate a default HTML file.
- Automatically detects the host (`isNetlifyHost()`). On Netlify, it presents the backend notice when PHP features are requested. On local XAMPP, it routes seamlessly to `frontend/pages/login.php`.

### `netlify.toml`
Located in the repository root:
```toml
# Netlify Configuration for Smart Campus Management System

[build]
  # Publish root directory containing index.html, static assets, and react-dashboard
  publish = "."

# Handle direct requests to .php endpoints on Netlify gracefully
[[redirects]]
  from = "/frontend/pages/*"
  to = "/index.html"
  status = 302

[[redirects]]
  from = "/backend/*"
  to = "/index.html"
  status = 302

[[redirects]]
  from = "/*.php"
  to = "/index.html"
  status = 302

# Security and MIME Type Headers
[[headers]]
  for = "/*"
  [headers.values]
    X-Frame-Options = "SAMEORIGIN"
    X-Content-Type-Options = "nosniff"

[[headers]]
  for = "/*.js"
  [headers.values]
    Content-Type = "application/javascript"

[[headers]]
  for = "/*.css"
  [headers.values]
    Content-Type = "text/css"
```

### Local Environment Preservation (`.htaccess`)
- Preserves `DirectoryIndex index.php index.html`.
- On Apache / XAMPP, `index.php` is prioritized, redirecting directly to `frontend/pages/login.php`.
- Local development workflow is 100% intact.

---

## 4. Exact Steps to Deploy on Netlify

### Step 1: Confirm Netlify Site Settings
1. Open your Netlify account at **[app.netlify.com](https://app.netlify.com)**.
2. Select your site: `elaborate-babka-a84a5c` (or open [https://elaborate-babka-a84a5c.netlify.app/](https://elaborate-babka-a84a5c.netlify.app/)).
3. Navigate to **Site configuration** &rarr; **Build & deploy** &rarr; **Continuous deployment**.
4. Verify the following parameters:
   - **Repository**: `Aakash-002-rec/Smart-Campus-Management-System`
   - **Branch to deploy**: `master`
   - **Base directory**: *(leave empty)*
   - **Build command**: *(leave empty)*
   - **Publish directory**: `.` *(the dot indicates the repository root containing `index.html`)*

### Step 2: Trigger Deployment
- If connected via GitHub automatic deployments, pushing the changes to branch `master` will trigger an automatic deployment.
- Alternatively, trigger manually from the Netlify dashboard:
  - Go to **Deploys** tab &rarr; Click **Trigger deploy** &rarr; Select **Deploy site**.

### Step 3: Verify the Live Deployment
1. Visit **`https://elaborate-babka-a84a5c.netlify.app/`**:
   - The homepage should load instantly with the sandalwood theme, header, and portal cards.
   - The status badge at top right displays `Netlify Frontend (No PHP)`.
2. Click **Access Student**, **Access Faculty**, or **Access Admin**:
   - A modal will open displaying:
     > *"The PHP/MySQL backend is not connected because this deployment only uses Netlify."*
3. Click **Launch Engine** or **Open React Telemetry**:
   - Loads `https://elaborate-babka-a84a5c.netlify.app/react-dashboard/index.html`.
   - Chart.js attendance charts and assessment marks display smoothly in client-side preview mode.
   - Clicking **Gateway Home** returns to `index.html`.
   - Clicking **PHP Portal** or **Logout** displays the backend notice modal.

---

## 5. Local XAMPP Verification (Full Application)

To run the complete full-stack application with live authentication and MySQL queries:
1. Open **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Run `.\run_server.bat` or navigate to:
   ```
   http://localhost:8000
   ```
3. The server prioritizes `index.php` and loads the live login portal (`frontend/pages/login.php`).
4. Log in using your registered credentials (e.g., student register number or faculty email).

---

## 6. Summary of Safeguards
- **Zero Secrets**: No database passwords, API tokens, or server credentials are in the repository.
- **`db_credentials.php` Excluded**: Ignored by `.gitignore` to protect local credentials.
- **No Third-Party Hosting Dependencies**: Netlify serves the static layer cleanly, and XAMPP runs the full stack locally.
