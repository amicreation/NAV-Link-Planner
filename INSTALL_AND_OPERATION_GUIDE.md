# NAV LINK PLANNER — Installation & Operation Guide

Welcome to the **NAV LINK PLANNER** quick-start guide. This document provides clear, concise instructions on how to install, configure, and operate the application on your computer or local server.

---

## 1. Quick Overview

**NAV LINK PLANNER** is an engineering design platform for planning **Point-to-Point (PtP) Radio Frequency (RF)** and **Free Space Optical (FSO)** telecommunications links.

### Key Capabilities:
- **RF Link Budget**: Free Space Path Loss (FSPL), 1st Fresnel zone clearance radius, Earth curvature drop ($k=4/3$), mechanical antenna tilt, fade margin, and throughput estimates.
- **FSO Optical Link Budget**: Kruse atmospheric attenuation model, divergence loss, fade margin, and 30-city Indian quarterly visibility baselines.
- **Geodetic Coordinates**: Dual support for **Decimal Degrees (DD)** and **Degrees, Minutes, Seconds (DMS)** with smart clipboard paste (e.g. `19°12'48.20"N, 77°35'40.43"E`).
- **Ground Elevation**: SRTM 30m Digital Elevation Model auto-lookup (AMSL).
- **Interactive Visuals**: Leaflet.js GIS map and terrain clearance profile chart.
- **Reports**: Branded PDF exports with high-resolution GD PNG graphics and custom field notes.

---

## 2. Prerequisites

Ensure you have the following installed on your machine:
* **PHP 8.2 or 8.3+** with extensions: `pdo_sqlite` (or `pdo_mysql`), `gd`, `mbstring`, `openssl`, `curl`, `xml`.
* **Composer** (PHP package manager: https://getcomposer.org/)
* **Node.js 18+ or 20+ & NPM** (https://nodejs.org/)
* **Git** (https://git-scm.com/)

---

## 3. Installation Steps

Open PowerShell, Command Prompt, or your Linux/macOS terminal:

### Step 1: Clone or Navigate to Project Folder
```bash
cd "e:\LINK PLANNER"
```
*(Or `git clone https://github.com/amicreation/NAV-Link-Planner.git` if cloning fresh).*

### Step 2: Install Backend Packages
```bash
composer install
```

### Step 3: Set Up Environment File
Create the `.env` configuration file from the template:
* **Windows**:
  ```powershell
  copy .env.example .env
  ```
* **Linux/macOS**:
  ```bash
  cp .env.example .env
  ```

Generate the unique encryption key:
```bash
php artisan key:generate
```

### Step 4: Prepare the SQLite Database
Create the SQLite database file if it does not already exist:
* **Windows (PowerShell)**:
  ```powershell
  if (-not (Test-Path database\database.sqlite)) { New-Item -ItemType File database\database.sqlite }
  ```
* **Linux/macOS**:
  ```bash
  touch database/database.sqlite
  ```

Run migrations and seed default user accounts & sample data:
```bash
php artisan migrate --seed
```

### Step 5: Install Frontend Packages & Build
```bash
npm install
npm run build
```

---

## 4. Default Login Credentials

The database seeder creates three standard role profiles:

| Profile | Email Address | Password | Intended Role |
|---|---|---|---|
| **Full Admin** | `amitsharma@navwireless.com` | `admin` | Full admin privileges: user management, audit logs, equipment library, all RF/FSO tools, reports. |
| **Full Technical** | `tech@navlinkplanner.com` | `password123` | Senior technical access: advanced optical FSO link budget parameters, RF tools, reports. |
| **Field Engineer** | `engineer@navlinkplanner.com` | `password123` | Survey access: RF workbench, FSO LOS survey mode, reports. |

---

## 5. How to Start the Application

### Option A: Standard Production / Built Mode (Recommended)
After running `npm run build`, you only need to start the Laravel backend:
```bash
php artisan serve
```
Open your browser and navigate to:
👉 **`http://127.0.0.1:8000`**

### Option B: Active Development Mode (With Hot-Reload)
Run these two commands in separate terminal tabs:
- **Terminal 1**: `php artisan serve`
- **Terminal 2**: `npm run dev`

---

## 6. How to Operate the Program (Step-by-Step)

### Step 1: Sign In
1. Visit `http://127.0.0.1:8000`.
2. Enter your email and password (e.g. `amitsharma@navwireless.com` / `admin`).
3. Click **Sign In**.

### Step 2: Create a New Project
1. Click **+ New Project** in the navigation header or dashboard.
2. Enter Project Name, Customer Name, and Engineer Name.
3. Select the Link Technology: **RF** or **FSO**.
4. Click **Create Project**.

### Step 3: Enter Site Coordinates & Elevation
1. In the **Site A** and **Site B** cards:
   - Enter Site Names (e.g. `Tower Alpha`, `Tower Bravo`).
   - Enter coordinates either in **Decimal Degrees (DD)** (e.g., `12.971598, 77.594562`) or toggle to **DMS Mode** (e.g., `12°58'17.76"N, 77°35'40.43"E`).
   - **Smart Paste**: You can paste coordinate strings like `19°12'48.20"N` directly into the input fields without manual parsing.
2. **Elevation**: Click **Lookup Elevation** to automatically retrieve SRTM 30m Above Mean Sea Level (AMSL) ground elevations.
3. **Tower Height**: Enter the antenna or optical transceiver height above ground (in meters).

### Step 4: Configure Link Parameters
* **RF Link**:
  - Choose Frequency (e.g., `5.8 GHz`, `2.4 GHz`, `60 GHz`).
  - Choose Channel Bandwidth (`20 MHz`, `40 MHz`, `80 MHz`).
  - Configure TX Power (`dBm`), Antenna Gains (`dBi`), and Cable Losses.
* **FSO Link**:
  - Select mode (**Line of Sight Survey** or **Full Technical**).
  - Configure optical wavelength (`850 nm`, `1310 nm`, `1550 nm`), beam divergence (`mrad`), and aperture diameter (`mm`).
  - Select the nearest Indian city to load atmospheric visibility baseline statistics.

### Step 5: Calculate and Review Results
1. Click **Calculate Link Budget**.
2. Review the resulting engineering metrics:
   - **Aerial Distance** (km).
   - **Azimuth & Reverse Azimuth** ($0^\circ - 360^\circ$ alignment bearings).
   - **Antenna Tilt Angle** (accounting for elevation delta and Earth curvature).
   - **Received Signal Level (RSL)** and **Fade Margin** (dB).
   - **Link Status**: Viable (Green), Marginal (Yellow), or Obstructed (Red).
   - **Interactive Terrain Chart**: Inspect the line of sight and 1st Fresnel zone clearance above terrain.

### Step 6: Add Field Remarks & Export PDF
1. Scroll down to the **Engineer Custom Remarks** text box.
2. Enter field notes, site survey observations, or installation guidelines.
3. Click **Export PDF Report**.
4. The system will compile and download a PDF document containing the link specs, high-resolution GD PNG terrain chart, and your custom notes.

---

## 7. Verification & Testing

To confirm that all mathematical calculations and build assets are working properly:

```bash
# 1. Run unit & feature calculation tests
php artisan test

# 2. Verify frontend asset compilation
npm run build
```

---

## 8. Common Troubleshooting

* **Blank charts in PDF report**: Enable PHP's `gd` extension in `php.ini` (`extension=gd`).
* **Database error**: Ensure `database/database.sqlite` exists and run `php artisan migrate --seed`.
* **Elevation not fetching**: Check your computer's internet connection (SRTM DEM lookup uses Open-Meteo & OpenTopoData public APIs). You can also type elevation manually.
