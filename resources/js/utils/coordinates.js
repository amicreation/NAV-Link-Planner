/**
 * Geodetic Coordinate Conversion Utilities (WGS84)
 * Supports bidirectional conversion between Decimal Degrees (DD) and Degrees, Minutes, Seconds (DMS).
 */

/**
 * Converts Decimal Degrees to structured DMS object and formatted string.
 *
 * @param {number|string} decimal - Decimal degrees value (e.g. 12.9715987)
 * @param {boolean} isLongitude - True if coordinate is longitude, false for latitude
 * @returns {{ degrees: number, minutes: number, seconds: number, direction: string, formatted: string }}
 */
export function decimalToDms(decimal, isLongitude = false) {
    const num = Number(decimal);
    if (isNaN(num)) {
        const defaultDir = isLongitude ? 'E' : 'N';
        return { degrees: 0, minutes: 0, seconds: 0, direction: defaultDir, formatted: `0° 00' 00.00" ${defaultDir}` };
    }

    const direction = isLongitude 
        ? (num >= 0 ? 'E' : 'W') 
        : (num >= 0 ? 'N' : 'S');

    const abs = Math.abs(num);
    let deg = Math.floor(abs);
    const minFloat = (abs - deg) * 60.0;
    let min = Math.floor(minFloat);
    let sec = Number(((minFloat - min) * 60.0).toFixed(2));

    if (sec >= 60.0) {
        sec = 0.0;
        min += 1;
        if (min >= 60) {
            min = 0;
            deg += 1;
        }
    }

    const formatted = `${deg}° ${String(min).padStart(2, '0')}' ${sec.toFixed(2).padStart(5, '0')}" ${direction}`;

    return {
        degrees: deg,
        minutes: min,
        seconds: sec,
        direction,
        formatted
    };
}

/**
 * Converts structured DMS components to Decimal Degrees.
 *
 * @param {number|string} deg - Degrees (e.g. 12)
 * @param {number|string} min - Minutes (0..59)
 * @param {number|string} sec - Seconds (0..59.99)
 * @param {string} direction - 'N', 'S', 'E', or 'W'
 * @returns {number} Decimal degrees with 8 decimal places
 */
export function dmsToDecimal(deg, min, sec, direction) {
    const d = Math.abs(Number(deg) || 0);
    const m = Math.abs(Number(min) || 0);
    const s = Math.abs(Number(sec) || 0);
    const dir = String(direction || 'N').toUpperCase().trim();

    let val = d + (m / 60.0) + (s / 3600.0);
    if (dir === 'S' || dir === 'W') {
        val = -val;
    }

    return Number(val.toFixed(8));
}

/**
 * Parses a free-form DMS or DD string into structured components.
 * Accepts formats like:
 * - 19°12'48.20"N or 19° 12' 48.20" N
 * - 19 12 48.20 N
 * - 19:12:48.20 N
 * - 19d 12m 48.20s N
 * - 19.21338889
 *
 * @param {string} input - Raw user input
 * @param {boolean} isLongitude - True if longitude
 * @returns {{ degrees: number, minutes: number, seconds: number, direction: string, decimal: number, formatted: string, isValid: boolean }}
 */
export function parseDmsString(input, isLongitude = false) {
    const defaultDir = isLongitude ? 'E' : 'N';
    if (!input || typeof input !== 'string' || !input.trim()) {
        return { degrees: 0, minutes: 0, seconds: 0, direction: defaultDir, decimal: 0, formatted: '', isValid: false };
    }

    const str = input.trim();

    // Check if plain numeric decimal degrees
    if (!isNaN(Number(str))) {
        const val = Number(str);
        const dms = decimalToDms(val, isLongitude);
        return {
            ...dms,
            decimal: val,
            isValid: true
        };
    }

    // Determine direction from string
    let dir = defaultDir;
    const dirMatch = str.match(/[NSEWnsew]/i);
    if (dirMatch) {
        dir = dirMatch[0].toUpperCase();
    } else if (str.startsWith('-')) {
        dir = isLongitude ? 'W' : 'S';
    }

    // Strip characters that aren't numbers, dot, or minus, normalizing separators to space
    const cleaned = str.replace(/[°\'"”’dmsDMSNSEWnsew:\,\-]+/g, ' ').trim();
    const parts = cleaned.split(/\s+/).map(Number).filter(n => !isNaN(n));

    if (parts.length >= 1) {
        const deg = Math.abs(parts[0] || 0);
        const min = Math.abs(parts[1] || 0);
        const sec = Math.abs(parts[2] || 0);
        const decimal = dmsToDecimal(deg, min, sec, dir);
        const formatted = `${deg}° ${String(min).padStart(2, '0')}' ${sec.toFixed(2).padStart(5, '0')}" ${dir}`;

        return {
            degrees: deg,
            minutes: min,
            seconds: sec,
            direction: dir,
            decimal,
            formatted,
            isValid: true
        };
    }

    return { degrees: 0, minutes: 0, seconds: 0, direction: defaultDir, decimal: 0, formatted: '', isValid: false };
}

/**
 * Universal Coordinate Input Parser:
 * Intelligently converts user input (whether Decimal Degrees, DMS string with symbols,
 * or compact DDMMSS.ss numbers like 191248.20 from raw copy-pastes) into clean decimal degrees.
 *
 * @param {string|number} input
 * @param {boolean} isLongitude
 * @returns {{ degrees: number, minutes: number, seconds: number, direction: string, decimal: number, formatted: string, isValid: boolean }}
 */
export function parseCoordinateInput(input, isLongitude = false) {
    if (input === null || input === undefined) {
        const defaultDir = isLongitude ? 'E' : 'N';
        return { degrees: 0, minutes: 0, seconds: 0, direction: defaultDir, decimal: 0, formatted: '', isValid: false };
    }

    const str = String(input).trim();
    if (!str) {
        const defaultDir = isLongitude ? 'E' : 'N';
        return { degrees: 0, minutes: 0, seconds: 0, direction: defaultDir, decimal: 0, formatted: '', isValid: false };
    }

    // 1. Check for standard DMS with symbols/letters e.g. 19°12'48.20"N
    if (str.match(/[°\'"”’dmsDMSNSEWnsew:]/)) {
        const res = parseDmsString(str, isLongitude);
        if (res.isValid) return res;
    }

    // 2. Check if compact DMS without symbols e.g. 191248.20 or 773540.43 (from stripped input)
    const num = Number(str);
    if (!isNaN(num)) {
        const isNegative = num < 0 || str.startsWith('-');
        const absStr = Math.abs(num).toString();
        const [intPart, decPart] = absStr.split('.');

        const isOutOfRange = isLongitude ? Math.abs(num) > 180 : Math.abs(num) > 90;
        if (isOutOfRange && (intPart.length === 5 || intPart.length === 6 || intPart.length === 7)) {
            let degStr, minStr, secStr;
            if (intPart.length === 5) {
                // e.g. 91248 -> 9° 12' 48"
                degStr = intPart.slice(0, 1);
                minStr = intPart.slice(1, 3);
                secStr = intPart.slice(3) + (decPart ? '.' + decPart : '');
            } else if (intPart.length === 6) {
                // e.g. 191248 -> 19° 12' 48"
                degStr = intPart.slice(0, 2);
                minStr = intPart.slice(2, 4);
                secStr = intPart.slice(4) + (decPart ? '.' + decPart : '');
            } else {
                // e.g. 0773540 -> 77° 35' 40"
                degStr = intPart.slice(0, 3);
                minStr = intPart.slice(3, 5);
                secStr = intPart.slice(5) + (decPart ? '.' + decPart : '');
            }

            const deg = Number(degStr);
            const min = Number(minStr);
            const sec = Number(secStr);

            if (min < 60 && sec < 60 && ((!isLongitude && deg <= 90) || (isLongitude && deg <= 180))) {
                const dir = isNegative 
                    ? (isLongitude ? 'W' : 'S') 
                    : (isLongitude ? 'E' : 'N');
                const decimal = dmsToDecimal(deg, min, sec, dir);
                const formatted = `${deg}° ${String(min).padStart(2, '0')}' ${sec.toFixed(2).padStart(5, '0')}" ${dir}`;
                return {
                    degrees: deg,
                    minutes: min,
                    seconds: sec,
                    direction: dir,
                    decimal,
                    formatted,
                    isValid: true
                };
            }
        }

        // 3. Normal standard decimal degrees e.g. 19.21338889
        const val = Number(num.toFixed(8));
        const dms = decimalToDms(val, isLongitude);
        return {
            ...dms,
            decimal: val,
            isValid: true
        };
    }

    return parseDmsString(str, isLongitude);
}

/**
 * Parses coordinate pairs separated by comma, semicolon, slash, or space.
 * e.g. "19°12'48.20\"N, 77°35'40.43\"E" or "19.213388, 77.594562"
 *
 * @param {string} input
 * @returns {{ latitude: number, longitude: number, latDms: string, lonDms: string }|null}
 */
export function parseCombinedCoordinates(input) {
    if (!input || typeof input !== 'string') return null;
    const str = input.trim();

    let parts = null;
    if (str.includes(',')) {
        parts = str.split(',');
    } else if (str.includes(';')) {
        parts = str.split(';');
    } else if (str.includes('/')) {
        parts = str.split('/');
    } else {
        const match = str.match(/(.+?[NSns])\s+([+-]?\d+.+)/);
        if (match) {
            parts = [match[1], match[2]];
        }
    }

    if (parts && parts.length >= 2) {
        const latRes = parseCoordinateInput(parts[0], false);
        const lonRes = parseCoordinateInput(parts[1], true);
        if (latRes.isValid && lonRes.isValid) {
            return {
                latitude: latRes.decimal,
                longitude: lonRes.decimal,
                latDms: latRes.formatted,
                lonDms: lonRes.formatted
            };
        }
    }

    return null;
}
