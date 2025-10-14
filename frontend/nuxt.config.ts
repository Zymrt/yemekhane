// https://nuxt.com/docs/api/configuration/nuxt-config
import tailwindcss from "@tailwindcss/vite";

export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  css: ['~/assets/main.css'],

  // Yeni eklenecek kısım: imports ve modules blokları
  // imports, composables ve middleware'in otomatik yüklendiğini kontrol eder
  imports: {
    dirs: ['composables', 'middleware'], // Bu, middleware klasörünüzü açıkça belirtir
  },
  
  // Modüller (Tailwind için modül eklenmesi gerekebilir)
  modules: [
    // Nuxt'ın Tailwind'i bir module olarak kullanması için gerekli olabilir. 
    // Eğer tailwindcss modülünü kurduysanız, buraya ekleyin:
    // '@nuxtjs/tailwindcss' 
  ],
  // Yeni eklenecek kısım sonu.

  vite: {
    plugins: [
      tailwindcss(),
    ],
  },
})