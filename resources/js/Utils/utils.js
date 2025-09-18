import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs) {
    return twMerge(clsx(inputs));
}

/**
 * Safely retrieves the CSRF token from the meta tag
 * @returns {string|null} The CSRF token or null if not found
 */
export function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute("content") : null;
}

/**
 * Creates axios headers with CSRF token if available
 * @param {Object} additionalHeaders - Additional headers to include
 * @returns {Object} Headers object with CSRF token and X-Requested-With
 */
export function createApiHeaders(additionalHeaders = {}) {
    const headers = {
        "X-Requested-With": "XMLHttpRequest",
        ...additionalHeaders,
    };

    const csrfToken = getCsrfToken();
    if (csrfToken) {
        headers["X-CSRF-TOKEN"] = csrfToken;
    }

    return headers;
}

/**
 * Formats a percentage value with proper sign and color coding
 * @param {number} value - The percentage value to format
 * @param {Object} options - Formatting options
 * @param {boolean} options.showSign - Whether to show + or - sign
 * @param {boolean} options.showColor - Whether to return color class
 * @returns {Object|string} Formatted percentage string or object with formatted string and color class
 */
export function formatPercentage(
    value,
    options = { showSign: true, showColor: false }
) {
    const { showSign, showColor } = options;

    // Handle null/undefined values
    if (value === null || value === undefined || isNaN(value)) {
        const result = "0%";
        return showColor
            ? { formatted: result, colorClass: "text-gray-600" }
            : result;
    }

    const numericValue = Number(value);
    const sign = numericValue >= 0 ? "+" : "-";
    const absoluteValue = Math.abs(numericValue);
    const formattedValue = `${absoluteValue}%`;

    let result = formattedValue;
    if (showSign) {
        result = `${sign}${formattedValue}`;
    }

    if (showColor) {
        let colorClass = "text-gray-600"; // Neutral color for zero
        if (numericValue > 0) {
            colorClass = "text-green-600";
        } else if (numericValue < 0) {
            colorClass = "text-red-600";
        }
        return { formatted: result, colorClass };
    }

    return result;
}

/**
 * Formats a percentage value for display with sign and color
 * @param {number} value - The percentage value
 * @returns {Object} Object with formatted string and color class
 */
export function formatPercentageWithColor(value) {
    return formatPercentage(value, { showSign: true, showColor: true });
}

/**
 * Formats a percentage value with sign but without color
 * @param {number} value - The percentage value
 * @returns {string} Formatted percentage string
 */
export function formatPercentageWithSign(value) {
    return formatPercentage(value, { showSign: true, showColor: false });
}

/**
 * Formats a percentage value without sign or color
 * @param {number} value - The percentage value
 * @returns {string} Formatted percentage string
 */
export function formatPercentageBasic(value) {
    return formatPercentage(value, { showSign: false, showColor: false });
}
