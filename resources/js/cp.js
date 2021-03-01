import Variants from './components/fieldtypes/Variants.vue';

Statamic.booting(() => {
    Statamic.$components.register('variants-fieldtype', Variants);
});
