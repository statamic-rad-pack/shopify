import Variants from './fieldtypes/Variants.vue';

Statamic.booting(() => {
    Statamic.$components.register('variants-fieldtype', Variants);
});
