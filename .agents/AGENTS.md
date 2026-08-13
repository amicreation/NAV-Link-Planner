# NAV LINK PLANNER - Agent Guidelines & Workflow Rules

## Project Overview
NAV LINK PLANNER is a vendor-independent RF & FSO Link Planning Tool built using Laravel 12, Vue 3 (Vite), Bootstrap 5, Leaflet.js, Chart.js, and DomPDF.

## Mandatory Daily Log Maintenance
- **Time & Date Stamp Requirement**: Whenever modifying features, fixing bugs, or completing development tasks, you MUST append or update the entry in `e:\Projects\LINK PLANNER\DAILY_ENGINEERING_REPORT.md` with the current date and timestamp (e.g. `YYYY-MM-DD HH:MM:SS IST`).
- **Log Structure**: Include completed engineering milestones, technical architecture changes, verification status, and timestamp.

## Engineering Rules
1. **Verification**: Always run `php artisan test` and `npm run build` after editing code to confirm zero regressions.
2. **Ground Elevation**: Ensure elevation values default to AMSL derived from elevation lookup when user input is 0.
3. **FSO Report Privacy**: Never render internal FSO wave metrics in customer PDF reports (`report.blade.php`).
4. **Distance Label**: Use "Aerial Distance" instead of "Geodesic Distance".
5. **GD PNG Images for PDF**: DomPDF has an SVG flip bug. Always generate and embed base64 PNG images for map and chart graphics in PDF exports.
