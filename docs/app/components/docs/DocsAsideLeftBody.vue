<script setup lang="ts">
import type { ContentNavigationItem } from '@nuxt/content'

const { sidebarNavigation } = useSubNavigation()

// Nuxt UI's ULink treats "/" as a non-exact-active ancestor of every route
// (Vue Router's standard root-path prefix matching), so the Introduction
// link would otherwise stay highlighted on every page. Force exact
// matching just for that root item; overrides docus's own component of
// the same name (node_modules/docus/app/components/docs/DocsAsideLeftBody.vue).
const navigation = computed<ContentNavigationItem[]>(() =>
  (sidebarNavigation.value || []).map(item =>
    item.path === '/' ? { ...item, exact: true } : item,
  ),
)

const contentNavVariants = useUIConfig('contentNavigation')
</script>

<template>
  <UContentNavigation
    :collapsible="false"
    :highlight="contentNavVariants.highlight ?? true"
    :highlight-color="contentNavVariants.highlightColor"
    :variant="contentNavVariants.variant ?? 'link'"
    :color="contentNavVariants.color"
    :navigation="navigation"
  />
</template>
