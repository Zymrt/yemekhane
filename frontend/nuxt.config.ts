// nuxt.config.ts
export default defineNuxtConfig({
  compatibilityDate: '2026-03-11',
  modules: ['@nuxtjs/tailwindcss'],
  css: ['@/assets/css/tailwind.css'],

  runtimeConfig: {
    public: {
      socketUrl: process.env.VITE_SOCKET_URL || 'http://localhost:3001',
      mealPrice: parseFloat(process.env.VITE_MEAL_PRICE || '50'),
    }
  },

  nitro: {
    routeRules: {
      '/api/**': {
        proxy: process.env.VITE_BACKEND_URL
          ? `${process.env.VITE_BACKEND_URL}/api/**`
          : 'http://127.0.0.1:8000/api/**'
      }
    }
  },
})
