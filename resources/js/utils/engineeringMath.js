// High Precision Geodetic & Engineering Calculations for Field Engineers

const EARTH_RADIUS_M = 6371000.0;
const K_FACTOR = 4.0 / 3.0;
const EFFECTIVE_EARTH_RADIUS = EARTH_RADIUS_M * K_FACTOR; // ~8494666.67 m

/**
 * Calculates Haversine Geodesic Distance in km (Double Precision)
 */
export function calculateDistanceKm(lat1, lon1, lat2, lon2) {
    const dLat = (lat2 - lat1) * (Math.PI / 180.0);
    const dLon = (lon2 - lon1) * (Math.PI / 180.0);

    const rLat1 = lat1 * (Math.PI / 180.0);
    const rLat2 = lat2 * (Math.PI / 180.0);

    const a = Math.sin(dLat / 2.0) ** 2 +
              Math.cos(rLat1) * Math.cos(rLat2) * (Math.sin(dLon / 2.0) ** 2);
    const c = 2.0 * Math.atan2(Math.sqrt(a), Math.sqrt(1.0 - a));

    return (EARTH_RADIUS_M * c) / 1000.0;
}

/**
 * Calculates Initial Forward Azimuth (Bearing) in Degrees (0° to 360°)
 */
export function calculateAzimuth(lat1, lon1, lat2, lon2) {
    const rLat1 = lat1 * (Math.PI / 180.0);
    const rLat2 = lat2 * (Math.PI / 180.0);
    const dLon = (lon2 - lon1) * (Math.PI / 180.0);

    const y = Math.sin(dLon) * Math.cos(rLat2);
    const x = Math.cos(rLat1) * Math.sin(rLat2) - Math.sin(rLat1) * Math.cos(rLat2) * Math.cos(dLon);

    let bearing = Math.atan2(y, x) * (180.0 / Math.PI);
    return (bearing + 360.0) % 360.0;
}

/**
 * Calculates Mechanical Tilt (Elevation Angle) in Degrees
 */
export function calculateMechanicalTilt(heightA_m, elevA_m, heightB_m, elevB_m, distanceKm) {
    if (distanceKm <= 0.0) return 0.0;

    const distanceM = distanceKm * 1000.0;
    const totalH_A = elevA_m + heightA_m;
    const totalH_B = elevB_m + heightB_m;

    const earthDrop_m = (distanceM ** 2) / (2.0 * EFFECTIVE_EARTH_RADIUS);
    const deltaH = (totalH_B - earthDrop_m) - totalH_A;

    return Math.atan2(deltaH, distanceM) * (180.0 / Math.PI);
}

/**
 * Max 1st Fresnel Zone Radius (Meters)
 */
export function calculateMaxFresnelRadius(distanceKm, frequencyGHz) {
    if (distanceKm <= 0.0 || frequencyGHz <= 0.0) return 0.0;
    return 8.66 * Math.sqrt(distanceKm / frequencyGHz);
}

/**
 * Max Earth Curvature Drop (Meters) at midpoint
 */
export function calculateMaxEarthCurvature(distanceKm) {
    if (distanceKm <= 0.0) return 0.0;
    const distanceM = distanceKm * 1000.0;
    return (distanceM ** 2) / (8.0 * EFFECTIVE_EARTH_RADIUS);
}

/**
 * Format float value with minimum 3 decimal places
 */
export function formatPrecision(val, decimals = 3) {
    if (val === null || val === undefined || isNaN(val)) return '0.000';
    return Number(val).toFixed(decimals);
}
