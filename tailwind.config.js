/** @type {import('tailwindcss').Config} */
export default {
    content: [
      "./resources/**/*.blade.php",
      "./resources/**/*.js",
      "./resources/**/*.vue",
    ],
    theme: {
      extend: {
        fontFamily: {
          figtree: ['Figtree', 'sans-serif'],
          ephesis: ['Ephesis', 'sans-serif'],
        },
      },
    },
    plugins: [],
  }
  