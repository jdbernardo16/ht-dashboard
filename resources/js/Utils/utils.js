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
