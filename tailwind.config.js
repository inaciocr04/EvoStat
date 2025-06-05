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
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
                montserrat: ['Montserrat', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                evogreen: "#32D8A0",
                evoblue: "#3690DE",
                evogray: "#384D53",
            },
            backgroundImage: {
                evogradientright: "linear-gradient(to right, #3690DE, #32D8A0)",
                evogradientleft: "linear-gradient(to left, #3690DE, #32D8A0)",
            },
            boxShadow: {
                'evoShadow': '0px 0px 24.1px 1px rgba(0, 0, 0, 0.25)',
            },
            borderRadius: {
                mainRounded: '30px',
                secondaryRounded: '20px',
            }
        },
    },

    plugins: [forms],
};
