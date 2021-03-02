import Variants from './fieldtypes/Variants.vue';
import DisabledText from './fieldtypes/DisabledText.vue';

Statamic.booting(() => {
    Statamic.$components.register('variants-fieldtype', Variants);
    Statamic.$components.register('disabled_text-fieldtype', DisabledText);
});
