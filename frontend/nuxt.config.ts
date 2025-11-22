// nuxt.config.ts
export default defineNuxtConfig({
  modules: ['@nuxtjs/tailwindcss'],
  css: ['@/assets/css/tailwind.css'],

  nitro: {
       routeRules: {
      '/api/**': { 
        proxy: 'http://127.0.0.1:8000/api/**' 
      }
    }
  },
})