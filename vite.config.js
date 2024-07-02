// vite.config.js
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import reactRefresh from "react-refresh";

export default defineConfig({
    server: {
        host: 'localhost',
        port: 3000, // Adjust the port as needed
    },
    plugins: [
        laravel({
            input: ["resources/sass/app.scss", "resources/js/app.js"],
            refresh: true,
        }),
        reactRefresh
    ],
    "scripts": {
  "watch": "vite --watch"
}
});
