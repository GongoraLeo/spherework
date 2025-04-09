const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'primary': '#2980b9',      // Belize Hole (Navbar, primary actions)
                'secondary': '#7f8c8d',    // Asbestos (Footer, secondary elements)
                'light': '#ecf0f1',        // Clouds (Main background)
                'dark': '#34495e',         // Wet Asphalt (Default text)
                'muted': '#bdc3c7',        // Silver (Subtle text, borders) - Can also use Concrete #95a5a6
                'accent': '#2ecc71',       // Emerald (Success messages, highlights)

                // You can add more flat colors if needed:
                'info': '#3498db',         // Peter River
                'warning': '#f1c40f',      // Sun Flower
                'danger': '#e74c3c',       // Alizarin
                'concrete': '#95a5a6',     // Concrete (Alternative for muted/borders)
            }
        },
    },
    plugins: [require('@tailwindcss/forms')],
};
