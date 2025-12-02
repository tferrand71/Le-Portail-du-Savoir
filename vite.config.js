import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// Remplacez 'NOM_DE_VOTRE_REPO' par le nom exact de votre dépôt GitHub (ex: 'portail-savoir')
export default defineConfig({
    plugins: [react()],
    base: '/Le-Portail-du-Savoir/',
})