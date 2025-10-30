// nuxt.config.ts

export default defineNuxtConfig({
  modules: ['@nuxtjs/tailwindcss'],
  css: ['@/assets/css/tailwind.css'],

  // ----------------------------------------------------
  // 🚀 EKLENEN PROXY AYARI
  // ----------------------------------------------------
  routeRules: {
    /**
     * '/api/' ile başlayan tüm tarayıcı isteklerini
     * (ör: /api/login, /api/admin/menu/all)
     * gizlice 'http://127.0.0.1:8000/api/...' adresine yönlendirir.
     */
    '/api/**': {
      proxy: 'http://127.0.0.1:8000/api/**',
    },
  },
})