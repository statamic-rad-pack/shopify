import Variants from './fieldtypes/Variants.vue'
import DisabledText from './fieldtypes/DisabledText.vue'
import ImportProductButton from './components/ImportProductButton'
import ImportButton from './components/ImportButton'

Statamic.booting(() => {
  Statamic.$components.register('variants-fieldtype', Variants)
  Statamic.$components.register('disabled_text-fieldtype', DisabledText)

  // Dashboard Stuff
  Statamic.$components.register('shopify-import-product-button', ImportProductButton)
  Statamic.$components.register('shopify-import-button', ImportButton)
})
