// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: {enabled: true},

  runtimeConfig: {
      apiBaseServer: '',
      apiTokenPath: '',

      public: {
          apiBaseClient: '',
          statsPollInterval: '',
      }
  },

  modules: ['@nuxtjs/tailwindcss'],
})