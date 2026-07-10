import { defineContentConfig, defineCollection, z } from '@nuxt/content'

// Override Docus's default content config: treat index.md as a normal docs
// page (rendered with the docs sidebar layout) rather than a marketing
// landing page, since this site only has docs content — no landing page copy.
export default defineContentConfig({
  collections: {
    docs: defineCollection({
      type: 'page',
      source: {
        cwd: 'content',
        include: '**',
      },
      schema: z.object({
        links: z.array(z.object({
          label: z.string(),
          icon: z.string(),
          to: z.string(),
          target: z.string().optional(),
        })).optional(),
      }),
    }),
  },
})
