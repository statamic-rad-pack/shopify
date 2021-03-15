# 1.0.4

- [Update] Moved the Shopify Settings into Tools and branded it with the Shopify Bag icon.

- [Fix] Product Price tag had been accidently removed.

# 1.0.3

- [Fix] Fixing create new product in Statamic throwing error.
- [Fix] Styling on no variants found for products.
- [Fix] Invalid namespace on Variant Action

# 1.0.2

- [Fix] Fixing tag foreach on null
- [Fix] Fixing quick start link in placeholder

# 1.0.1

### Updated

- Added a debounce handler and implemented around the cart quantity JS.

# 1.0.0

Public Release!

### Key Changes

- Product Types renamed to Types
- Product Tags renamed to tags
- `product_variant` tag now accepts `show_price="true"` rather than `currency=""`
- Massively cleaned up the default theme files. 

### Other

- [New] Added `product_price` tag to show the price on the product overview page.
- [New] Added trait to extend to check if has product variants.
- [New] You can now pass `class` to the `product_variant` tag to style the select
- [New] Config now has option for `currency` - defualts to £.

# 0.3.0

### Update

- Docs have been updated with more relevant information that hopefully clears a few things up.

### Removed

- Removed the compile scripts as it didn't make sense.

# 0.2.0

### New

- Added in a way to check for qty changes on the cart screen.
- Tag for loading product variants with default settings.
- JavaScript can now be included compiled (with theme) or modular for you to customise.

### Update

- Completely rewrote the front end JavaScript so it's modular and you can import it at your will. Publishes to `resources/js/vendor/shopify`.
- Updated tag for loading in compiled js. `{{ shopify_scripts }}`
- Service provider no longer publishes everything on install.

# 0.1.2

### New

- Added trait for importing all products
- Added webhook for when a product is deleted

### Fix

- Fixing large data set imports

# 0.1

- Initial Release
