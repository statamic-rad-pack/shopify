import { defineNuxtModule, extendPages } from '@nuxt/kit'

export default defineNuxtConfig({
  extends: ['docus'],

  site: {
    name: 'Statamic Shopify',
    url: 'https://statamic-shopify-docs.vercel.app',
  },

  modules: [
    // Registered as a real module (not a top-level `hooks` block) so its
    // callbacks subscribe after docus's own layer modules have subscribed
    // theirs, guaranteeing correct ordering below.
    defineNuxtModule({
      setup(_options, nuxt) {
        // Docus registers a static "/" route pointing at its marketing
        // landing template (node_modules/docus/modules/routing.ts), which
        // takes priority over the catch-all docs page route. This site has
        // no landing page copy, so drop that route and let the catch-all
        // render "/" as a normal docs page instead (content.config.ts
        // includes index.md in the docs collection for this to work).
        extendPages((pages) => {
          const index = pages.findIndex(page => page.name === 'index')
          if (index !== -1) pages.splice(index, 1)
        })

        // Docus auto-enables its AI assistant (header icon, floating input,
        // "explain with AI" button) whenever AI_GATEWAY_API_KEY or
        // VERCEL_OIDC_TOKEN is present in the environment — which Vercel
        // sets automatically on every deployment. We don't want this
        // feature, so force it off after the assistant module
        // (node_modules/docus/modules/assistant) has set its default.
        nuxt.hook('modules:done', () => {
          nuxt.options.runtimeConfig.public.assistant = {
            ...nuxt.options.runtimeConfig.public.assistant,
            enabled: false,
          }
        })
      },
    }),
  ],
})
