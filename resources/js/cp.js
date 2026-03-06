import Variants from './fieldtypes/Variants.vue'
import DisabledText from './fieldtypes/DisabledText.vue'
import ImportProductButton from './components/ImportProductButton.vue'
import ImportButton from './components/ImportButton.vue'
import WebhookStatus from './components/WebhookStatus.vue'

Statamic.booting(() => {
  Statamic.$components.register('variants-fieldtype', Variants)
  Statamic.$components.register('disabled_text-fieldtype', DisabledText)

  // Dashboard Stuff
  Statamic.$components.register('shopify-import-product-button', ImportProductButton)
  Statamic.$components.register('shopify-import-button', ImportButton)
  Statamic.$components.register('shopify-webhook-status', WebhookStatus)
});
