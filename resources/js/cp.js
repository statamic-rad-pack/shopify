import Variants from './fieldtypes/Variants.vue'
import DisabledText from './fieldtypes/DisabledText.vue'
import Dashboard from './pages/Dashboard.vue'

Statamic.booting(() => {
  Statamic.$components.register('variants-fieldtype', Variants)
  Statamic.$components.register('disabled_text-fieldtype', DisabledText)

  Statamic.$inertia.register('shopify::Dashboard', Dashboard)
});
