import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: "#eff6ff",
                    100: "#dbeafe",
                    200: "#bfdbfe",
                    300: "#93c5fd",
                    400: "#60a5fa",
                    500: "#3b82f6",
                    600: "#2563eb", // Primary blue from Figma
                    700: "#1d4ed8",
                    800: "#1e40af",
                    900: "#1e3a8a",
                    950: "#172554",
                },
                success: {
                    50: "#f0fdf4",
                    100: "#dcfce7",
                    200: "#bbf7d0",
                    300: "#86efac",
                    400: "#4ade80",
                    500: "#22c55e", // Success green from Figma
                    600: "#16a34a",
                    700: "#15803d",
                    800: "#166534",
                    900: "#14532d",
                    950: "#052e16",
                },
                warning: {
                    50: "#fffbeb",
                    100: "#fef3c7",
                    200: "#fde68a",
                    300: "#fcd34d",
                    400: "#fbbf24",
                    500: "#f59e0b",
                    600: "#d97706",
                    700: "#b45309",
                    800: "#92400e",
                    900: "#78350f",
                },
                error: {
                    50: "#fef2f2",
                    100: "#fee2e2",
                    200: "#fecaca",
                    300: "#fca5a5",
                    400: "#f87171",
                    500: "#ef4444",
                    600: "#dc2626",
                    700: "#b91c1c",
                    800: "#991b1b",
                    900: "#7f1d1d",
                },
                chart: {
                    purple: "#8884d8",
                    green: "#82ca9d",
                    yellow: "#ffc658",
                    orange: "#ff8042",
                },
                background: "#f9fafb",
                surface: "#ffffff",
                muted: "#6b7280",
                "muted-foreground": "#9ca3af",
            },
            spacing: {
                18: "4.5rem",
                88: "22rem",
                128: "32rem",
                // Custom spacing for dashboard
                1405: "87.8125rem", // 1405px dashboard width
                24: "1.5rem", // 24px padding
            },
            borderRadius: {
                "4xl": "2rem",
                // Custom border radius from Figma
                2: "0.5rem", // 8px border radius
            },
            maxWidth: {
                "8xl": "88rem",
                "9xl": "96rem",
                // Dashboard max width
                dashboard: "87.8125rem", // 1405px
            },
            boxShadow: {
                soft: "0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)",
                "soft-xl":
                    "0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)",
                // Custom shadows for dashboard cards
                card: "0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)",
                "card-lg":
                    "0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)",
            },
            screens: {
                // Custom breakpoints for 1405px dashboard
                xs: "475px",
                sm: "640px",
                md: "768px",
                lg: "1024px",
                xl: "1280px",
                "2xl": "1536px",
                dashboard: "1405px",
            },
            gridTemplateColumns: {
                // Custom grid for 2x3 dashboard layout
                dashboard: "repeat(3, minmax(0, 1fr))",
                "dashboard-mobile": "repeat(1, minmax(0, 1fr))",
                "dashboard-tablet": "repeat(2, minmax(0, 1fr))",
            },
            gridTemplateRows: {
                dashboard: "repeat(2, minmax(0, 1fr))",
            },
            minHeight: {
                "dashboard-card": "280px",
            },
        },
    },

    plugins: [forms],
};
