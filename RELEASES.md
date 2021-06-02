# 1.4.2

### Fixed

- Removing some unnecessary fields on the variant fieldtype.

### New

- Allows the user to override the container/folder that the assets are uploaded to with `SHOPIFY_ASSET_CONTAINER` and `SHOPIFY_ASSET_PATH`.

# 1.4.1

### Fixed

- Removing left over debug with `ray()`
# 1.4.0
### New

- Collections can now be imported (#24). Note: You'll need to publish the assets or create a new taxonomy for them in the admin panel. Once set up you can either run the 'Import All Products' again or you can use the new 'Import Collections' (`php artisan shopify:import:collections`).

# 1.3.6

### New

- Adding `compare_at_price` for variants to check if on sale - you'll need to add this to your blueprint to be visible in the admin. #55

### Fixed

- Fixing variant `columns() null` error due to variant blueprint being hidden. #51
- Fixed ImportSingleProduct failing to find old variants and deleting them. #57
- Updates to docs. #52. #49.

# 1.3.5

### Fixed

- Fixing variants getting deleted each sync causing overwrite. #45

# 1.3.4

### New

- You can now override the taxonomies used in the CMS.

# 1.3.2

### Fixed

- Fixing `ray()` debug left in ImportJob. #40

# 1.3.1

### New

- **ProductVariant** tag has been updated to give you multiple ways to interact with them.
    - `product_variant:generate` - outputs the html prerendered (default)
    - `product_variant:loop` - gives you access to variant data to use however you wish.
    - `product_variant:by_title` - lets you grab one variant by it's title.
    - `product_variant:by_index` - lets you grab one variant by index.

### Updated

- **InStock**, **ProductVariant**, and **ProductPrice** tag updated so you no longer need to define `:product="slug"` each time.
- Updated theme files to reflect the tag changes.

# 1.3.0

- [New] Adds ability to pull options names into an array field. (See Upgrade Guide). Ref #31.
- [Update] Default variant now has the title **'Default'** for single products rather than **'Default Title'**
- [Bug] Now removes old variants that are no longer present in Shopify. Fixes #32.
- [Bug] Additional fields are no longer being overwritten on Products. Fixes #30.
- [Bug] Fixes the way product tags/type/vendors import. No longer a mismatch.
- [Bug] Fixes variant data would be overwriten if extended.

### Upgrade Guide

If you want to display options/handle them on the site you need to update your `Product.yaml` blueprint in `resources/blueprints/collections/products` with the option field. Append the following. You're then free to hide it.

```yaml
  -
    handle: options
    field:
      mode: dynamic
      display: Options
      type: array
      icon: array
      listable: hidden
```

# 1.2.0

- [New] All webhooks have been given a name.
- [New] Added a webhook for when a product is created in Shopify.
- [New] Added a webhook for when a product is updated in Shopify

>  these two webhooks if utilised, should mean you don't have to run full imports after the first one.

- [Fix] Incorrect name on Product Delete Webhook.
- [Update] Product delete webhook now has a new path. See potentially breaking below.

### Potentially breaking

WebHook endpoint for **Product Deletion** has changed from `/!/statamic-shopify/webhook/product-deletion` to `/!/statamic-shopify/webhook/product/delete`

# 1.1.3

- [Fix] Fixing error with `cart.js` when variants didn't have images.

# 1.1.2

- [Fix] Fixing error when no image is found for a product

# 1.1.1

- [New] Added lang array to config to overwrite some of the default text used in tags.
- [New] Added option to `product_variants` tag to show out of stock in the select.
- [New] Appends `data-in-stock` to each option in the variant loop to show if in/out of stock.

# 1.1.0

- [New] Added `{{ in_stock :product="slug" }}` tag to check if a product is in stock.
- [New] Pulls in `inventory_policy` from the Shopify API for variants (You will need to sync again).
- [New] Displays **out of stock** on `{{ product_price }}` tag if policy exists and there is no stock.
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
