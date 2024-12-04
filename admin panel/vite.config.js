import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  optimizeDeps: {
    // Ensure Vite pre-bundles the CKEditor package
    include: ['@ckeditor/ckeditor5-build-classic'],
  },
  build: {
    rollupOptions: {
      // If Rollup is having trouble resolving CKEditor, you can mark it as external
      external: ['@ckeditor/ckeditor5-build-classic'],
    },
  },
});
