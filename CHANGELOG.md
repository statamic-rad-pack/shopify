# Changelog

## v4.0.6 - 2024-10-27

### What's Changed

* Prevent current timestamp being added to draft products by @sunscreem in https://github.com/statamic-rad-pack/shopify/pull/245

### New Contributors

* @sunscreem made their first contribution in https://github.com/statamic-rad-pack/shopify/pull/245

**Full Changelog**: https://github.com/statamic-rad-pack/shopify/compare/v4.0.5...v4.0.6

## v4.0.5 - 2024-10-17

### 🐛 Fixed

- Mirror pagination change to fetchCollections [@ryanmitchell](https://github.com/ryanmitchell) (#244)

### 🧰 Maintenance

- We don't need Product Create on setup [@ryanmitchell](https://github.com/ryanmitchell) (#243)

## v4.0.4 - 2024-10-03

### 🐛 Fixed

- Ensure future dated products get marked a published [@ryanmitchell](https://github.com/ryanmitchell) (#240)

## v4.0.3 - 2024-09-18

### What's Changed

* Error on Collection Create/Update Webhook by @anditopping in https://github.com/statamic-rad-pack/shopify/pull/238

### New Contributors

* @anditopping made their first contribution in https://github.com/statamic-rad-pack/shopify/pull/238

**Full Changelog**: https://github.com/statamic-rad-pack/shopify/compare/v4.0.2...v4.0.3

## v4.0.2 - 2024-09-13

### 🐛 Fixed

- Fix nextPage being null [@ryanmitchell](https://github.com/ryanmitchell) (#237)

## v4.0.1 - 2024-09-13

### 🐛 Fixed

- Fix installation errors [@ryanmitchell](https://github.com/ryanmitchell) (#235)

## v4.0.0 - 2024-09-11

### 🚀 New

- Final v4 changes [@ryanmitchell](https://github.com/ryanmitchell) (#231)
- Remove `respect_shopify_publish_status_and_dates` config [@ryanmitchell](https://github.com/ryanmitchell) (#230)
- Move collection create/update/delete to its own webhook and job [@ryanmitchell](https://github.com/ryanmitchell) (#229)
- Create collections, taxonomies and asset containers programatically [@ryanmitchell](https://github.com/ryanmitchell) (#233)

## v3.6.0 - 2024-09-09

### What's new

- https://github.com/statamic-rad-pack/shopify/pull/227 [@ryanmitchell](https://github.com/ryanmitchell) (#227)

## v3.5.1 - 2024-09-05

### 🐛 Fixed

- Don't download an image unless we need it [@ryanmitchell](https://github.com/ryanmitchell) (#226)

## v3.5.0 - 2024-09-04

### Whats new

- Set default roles and groups from Statamic when making users [@ryanmitchell](https://github.com/ryanmitchell) (#225)

## v3.4.2 - 2024-08-06

- Fix bug in customer orders tag when no customer exists [@ryanmitchell](https://github.com/ryanmitchell) (#223)

### 🐛 Fixed

- Fix icon in dark mode [@ryanmitchell](https://github.com/ryanmitchell) (#224)

## v3.4.1 - 2024-07-25

- Fix bugs in product webhook update [@ryanmitchell](https://github.com/ryanmitchell) (#222)

## v3.4.0 - 2024-05-24

- [fix] Add shopify prefix to tags requiring it [@haugen86](https://github.com/haugen86) (#219)

### 🚀 New

- Add session_storage_path config [@ryanmitchell](https://github.com/ryanmitchell) (#221)

## v3.3.1 - 2024-05-14

- Support pagination on customer orders tag [@ryanmitchell](https://github.com/ryanmitchell) (#216)

### 🐛 Fixed

- Don't set shopify context if a key isnt set [@ryanmitchell](https://github.com/ryanmitchell) (#218)

## v3.3.0 - 2024-05-02

- Support Statamic 5 [@ryanmitchell](https://github.com/ryanmitchell) (#208)

### 🚀 New

- Add helper for parsing Shopify Rich Text metafields [@ryanmitchell](https://github.com/ryanmitchell) (#215)

## v3.2.3 - 2024-05-01

- Add correct site handle for translations [@JonKaric](https://github.com/JonKaric) (#212)

### 🐛 Fixed

- Select default site for variants [@ryanmitchell](https://github.com/ryanmitchell) (#214)

## v3.2.2 - 2024-04-10

### 🐛 Fixed

- Hide blueprints so they cant be added through CP [@ryanmitchell](https://github.com/ryanmitchell) (#207)

## v3.2.1 - 2024-03-17

### What's Changed

* Update ImportSingleProductJob.php by @dwainscheeren in https://github.com/statamic-rad-pack/shopify/pull/205

### New Contributors

* @dwainscheeren made their first contribution in https://github.com/statamic-rad-pack/shopify/pull/205

**Full Changelog**: https://github.com/statamic-rad-pack/shopify/compare/v3.2.0...v3.2.1

## v3.2.0 - 2024-03-11

### 🚀 New

- Add `last_purchased` and `total_purchased` to product variants [@ryanmitchell](https://github.com/ryanmitchell) (#191)

### 🐛 Fixed

- Default all Shopify managed fields to readonly [@ryanmitchell](https://github.com/ryanmitchell) (#204)

## v3.1.1 - 2024-01-17

### 🐛 Fixed

- Use getNextPageQuery correctly [@ryanmitchell](https://github.com/ryanmitchell) (#200)

## v3.1.0 - 2024-01-12

### 🚀 New

- Use new nav methods to build custom navigation [@ryanmitchell](https://github.com/ryanmitchell) (#193)

### 🐛 Fixed

- Fix variant form styling [@ryanmitchell](https://github.com/ryanmitchell) (#197)

## v3.0.3 - 2023-11-10

### 🐛 Fixed

- Fix user creation job always throwing an error [@ryanmitchell](https://github.com/ryanmitchell) (#195)

## v3.0.2 - 2023-10-28

### 🐛 Fixed

- Fix connection defaulting to private app and make it configurable [@ryanmitchell](https://github.com/ryanmitchell) (#192)

## v3.0.1 - 2023-10-23

- Update setup.md [@JonKaric](https://github.com/JonKaric) (#186)

### 🚀 New

- Get customer information from Shopify [@ryanmitchell](https://github.com/ryanmitchell)  (#185)
- Support multi-site / Shopify translated fields [@ryanmitchell](https://github.com/ryanmitchell)  (#184)

### 🧰 Maintenance

- Add 2.x -> 3.x upgrading docs [@ryanmitchell](https://github.com/ryanmitchell)  (#190)
- Target Shopify PHP 5.1+ [@ryanmitchell](https://github.com/ryanmitchell)  (#189)
- Move webhook validation to middleware [@ryanmitchell](https://github.com/ryanmitchell)  (#188)
- Use Shopify's official PHP library [@ryanmitchell](https://github.com/ryanmitchell)  (#183)

## v2.0.0-beta1 - 2023-09-18

- Hide unnecessary stuff from the Marketplace, update Github links [@jackmcdade](https://github.com/jackmcdade) (#165)
- Add GH Action [@edalzell](https://github.com/edalzell) (#149)

### 🚀 New

- Attach variant images and add alt tags on initial asset creation [@ryanmitchell](https://github.com/ryanmitchell) (#158)
- Save metafields and images for collections [@ryanmitchell](https://github.com/ryanmitchell) (#172)
- Render form fields with a view not hardcoded HTML [@ryanmitchell](https://github.com/ryanmitchell) (#173)
- Create `deploy demo store` workflow [@ryanmitchell](https://github.com/ryanmitchell) (#169)
- Get metafields for products and variants [@ryanmitchell](https://github.com/ryanmitchell) (#163)
- Add test coverage [@ryanmitchell](https://github.com/ryanmitchell) (#170)
- Namespace tags behind `shopify:` [@ryanmitchell](https://github.com/ryanmitchell) (#157)
- Create docs to vercel action [@ryanmitchell](https://github.com/ryanmitchell) (#168)
- Change namespace [@edalzell](https://github.com/edalzell) (#150)

### 🐛 Fixed

- Allow lang strings to be translated [@ryanmitchell](https://github.com/ryanmitchell) (#171)
- Use checkoutId instead of localstorage [@ryanmitchell](https://github.com/ryanmitchell) (#162)
- Fix bug in storefront_url definition and usage [@ryanmitchell](https://github.com/ryanmitchell) (#166)
- Publish views according to conventions [@ryanmitchell](https://github.com/ryanmitchell) (#159)
- Allow a custom storefront url to be specified [@ryanmitchell](https://github.com/ryanmitchell) (#161)
- Add statamic/cms .editorconfig [@ryanmitchell](https://github.com/ryanmitchell) (#160)
- Change composer package name to statamic-rad-pack/shopify, and fix bug introduced with duplicate route name [@ryanmitchell](https://github.com/ryanmitchell) (#156)
- Upload assets on release [@edalzell](https://github.com/edalzell) (#155)
- Cleanup files [@edalzell](https://github.com/edalzell) (#154)
- Use term query builder instead of findBySlug [@ryanmitchell](https://github.com/ryanmitchell) (#152)
- Don't clear cache after import [@ryanmitchell](https://github.com/ryanmitchell) (#153)
- Use Laravel 10 conventions for routes [@ryanmitchell](https://github.com/ryanmitchell) (#151)

# 1.7.8

### Fixed

- Fixed an issue with redundancies made in #111. This should fix cart checkout removal issues and quantities. (#136).

# 1.7.7

### Changed

- Products now re-import off the `product_id` rather than the slug. This is to prevent duplications and changes whenever the product is changed in the Shopify Admin. **NOTE: we do not overwrite the slug again in Statamic in case it has been changed here for SEO purposes.** (#135)
- 

# 1.7.6

### Changed

- Loosened methods on `ProductVariant.php` tag so that they can be overwritten by extension. (#126)

### Fixed

- Fixed an issue with storefront_id, inventory_policy, and inventory_management being missing from variant (#122).
- Fixed an issue with the example js not invalidating carts if they were completed. (#123)

# 1.7.5

### Fixed

- Fixed admin dashboard no longer showing with new admin token update. Fixes #115. Fixes #118.

# 1.7.4

### New

- Adds support for new apps generated via Shopify, these require the use of an Admin API Token rather than the Auth/Password combo. You can add this option by specifiying `SHOPIFY_ADMIN_TOKEN` in your `.env` file.

# 1.7.3

### Fixed

- Fixed an issue with the localStorage values returning null on initial load of products. Please publish assets again. If you've modified the JS files, Please review PR #111 for changes you may need to make.

# 1.7.2

### Fixed

- Remove an issue with the `async` and `await` from the causing issues with the cart.

# 1.7.0

### Fixed

- Changed the way the checkout ID gets intitalised to prevent errors if the ID is cleared/not intialised properly. To get this change on an existing site, you'll need to update your JS or republish the JS. (See #103).
- Fixed an issue with the delete webhook not properly removing files. (See #104).

### New

- Added the option to define a queue for the Shopify jobs. Defaults to `default`. If you'd like to set a unique process you can set a env variable of `SHOPIFY_JOB_QUEUE`. (See #95).

# 1.6.0

### New

- **Potentially Breaking:**: Now imports `inventory_management` option for each variant to check if the product is out of stock. This allows for sale of products which aren't out of stock and only denies if the "track inventory" option is checked within Shopify. If, it's unchecked the system will believe that there is either "unlimited stock" or "you have manually adjusted". (See #89)

# 1.5.2

### Fixed

- Out of stock flag in `product_variants` now works if stock is negative/0 rather than just 0 (#81)

# 1.5.1

### Fixed

- Removing debugs and dumps (#78)

# 1.5.0

### New

- Product variants that are out of stock are **disabled** by default in the option. You can manually control this by looping around variants and changing the output. (#70).
- Products imported which are drafts or unpublished, are no longer marked as published in Statamic and will be set to draft. (#75).

# 1.4.3

### Fixed

- Products assigned to more than 250 collections would overwrite on pagination.
- Smart collections now import alongside custom assigned collections. See #64.

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
- handle: options
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

> these two webhooks if utilised, should mean you don't have to run full imports after the first one.

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
