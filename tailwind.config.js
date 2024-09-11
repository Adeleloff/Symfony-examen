/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./vendor/tales-from-a-dev/flowbite-bundle/templates/**/*.html.twig",
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      colors: {
        beige : "#FDFCF9",
        footer : "#F9F6F0",
        svg : "#0047a0", // couleur bleu du svg du logo
        hoversvg : "#003680", // couleur légèrement plus 
      },
    },
  },
  plugins: [
  ],
}
