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
                evogradienttop: "linear-gradient(to bottom, #3690DE, #32D8A0)",
                evogradientbottom: "linear-gradient(to top, #3690DE, #32D8A0)",
                evogradientgrayleft: "linear-gradient(to left, #384D53, #FFFFFF)",
                evochest: "linear-gradient(to top, #FFFFFF, #32D8A0 200%)",
                evolegs: "linear-gradient(to top, #FFFFFF, #3690DE 200%)",
                evoarm: "linear-gradient(to top, #FFFFFF, #AD5FD9 200%)",
                evoback: "linear-gradient(to top, #FFFFFF, #F50B0F 200%)",
                evoshoulder: "linear-gradient(to top, #FFFFFF, #FFCC00 200%)",
                evocardio: "linear-gradient(to top, #FFFFFF, #00FFEE 200%)",
            },
            boxShadow: {
                'evoShadow': '0px 0px 8px 1px rgba(0, 0, 0, 0.25)',
                'evoShadow2': '0px 0px 24.1px 1px rgba(0, 0, 0, 0.25)',
            },
            borderRadius: {
                mainRounded: '30px',
                secondaryRounded: '20px',
                secondaryButtonRounded: '18px',
                thirdRounded: '10px',
            },
            width:{
                evocardwidth: '23.125rem',
                evocardfullwidth: '36.25rem',
            },
            height:{
              evocardheight: '18.125rem',
            },
        },
    },

    plugins: [forms],
};
