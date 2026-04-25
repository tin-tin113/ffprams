# Internet-Dependent Functions & System Storage Guide

This document provides a comprehensive breakdown of which features in FFPRAMS require internet connectivity and where your system data is stored.

---

## 🌐 1. Internet-Dependent Functions

If the system is running in a purely offline environment (no internet), the following features will be affected:

### A. SMS Messaging (Semaphore API)
*   **How it works:** The system communicates with the **Semaphore SMS Gateway** via a bridge API (`https://smsapiph.onrender.com`). When an administrator sends a broadcast or an automated notification is triggered, the server sends an encrypted web request to this endpoint.
*   **Code Location:** `app/Services/SemaphoreService.php`
*   **Requirement:** Active internet connection on the server.

### B. Interactive Geo-Map (Leaflet)
*   **How it works:** The Geo-Map module uses **Leaflet.js** to render geographical data. While the data (coordinates) is local, the **Map Tiles** (the visual maps of E.B. Magalona) are fetched dynamically from:
    *   `basemaps.cartocdn.com` (Modern/Light View)
    *   `openstreetmap.org` (Standard View)
    *   `arcgisonline.com` (Satellite View)
*   **Code Location:** `resources/views/geo-map/index.blade.php`
*   **Requirement:** Browser-side internet connection for the administrator.

### C. Dashboard Charts & Visuals (Chart.js)
*   **How it works:** The charts on the dashboard and in the reports section use **Chart.js**. The library is loaded via a Content Delivery Network (CDN).
*   **Code Location:** Loaded in `resources/views/layouts/app.blade.php` via `cdn.jsdelivr.net`.
*   **Requirement:** Browser-side internet to load the library script.

### D. System Styling & Icons (CDNs)
*   **How it works:** FFPRAMS uses professional typography and icons to maintain a premium interface. These are pulled from external servers:
    *   **Google Fonts:** `fonts.googleapis.com` (Inter Font Family)
    *   **Bootstrap Icons:** `cdn.jsdelivr.net`
*   **Requirement:** Browser-side internet. Without it, icons will appear as boxes and text will default to system fonts.

### E. Admin Password Resets (Email)
*   **How it works:** If an admin clicks "Forgot Password", the system connects to an external Mail Server (configured in `.env` as SMTP or via services like AWS SES/Postmark).
*   **Requirement:** Server-side internet to send the outgoing email.

---

## 📂 2. File Storage Locations

FFPRAMS is currently configured to store all uploaded documents and generated reports **locally on your server**. These are stored in the `storage` directory, which is isolated from the public web for security.

| Document Type | Physical Path on Server |
| :--- | :--- |
| **Beneficiary Attachments** | `storage/app/private/beneficiary-documents` |
| **Record/Distribution Proofs**| `storage/app/private/record-documents` |
| **Program Reference Files** | `storage/app/private/program-documents` |
| **CSV Import Error Reports** | `storage/app/private/allocation-import-reports` |
| **Public User Assets** | `storage/app/public` (mapped to `public/storage`) |

### 🔒 Security Note
Documents in the `private` folders (Beneficiary, Record, Program) **cannot** be accessed by a direct URL. They are protected by the system and can only be viewed by authenticated users with the correct permissions.

---

## 🛠️ Summary for Offline Deployment
If you intend to use this system in a **Local Area Network (LAN)** with no internet access, you would need to:
1.  **Localize Assets:** Download the Bootstrap, Font, and Chart.js files and save them in the `public/` folder.
2.  **Offline Maps:** Implement a local tile server or use a static map image for the Geo-Map.
3.  **Local SMS:** Connect a physical GSM Modem/Hardware SMS Gateway instead of the Semaphore API.
