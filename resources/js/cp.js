import Variants from './fieldtypes/Variants.vue';
import DisabledText from './fieldtypes/DisabledText.vue';
import ImportProductButton from "./components/ImportProductButton";
import ImportProductsButton from "./components/ImportProductsButton";

Statamic.booting(() => {
    Statamic.$components.register('variants-fieldtype', Variants);
    Statamic.$components.register('disabled_text-fieldtype', DisabledText);

    // Dashboard Stuff
    Statamic.$components.register('import-product-button', ImportProductButton);
    Statamic.$components.register('import-products-button', ImportProductsButton);
});
