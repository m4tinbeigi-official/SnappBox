import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'path';

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    react(),
    tailwindcss(),
  ],
  build: {
    // Output assets to assets/dist inside the plugin
    outDir: resolve(__dirname, 'assets/dist'),
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: [
        // Main entry for the Setup Wizard Mini-App
        resolve(__dirname, 'src/setup-wizard/main.tsx'),
        resolve(__dirname, 'src/admin/admin-snappbox.ts'),
        // Shared global styles
        resolve(__dirname, 'src/styles/global.css'),
      ],
    },
  },
  server: {
    // For local development with HMR
    origin: 'http://localhost:5173',
    cors: true,
    strictPort: true,
  },
});
