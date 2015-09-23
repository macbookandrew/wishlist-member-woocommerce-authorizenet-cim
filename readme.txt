=== Wishlist Member + Woocommerce + Authorize.net CIM ===
Contributors: macbookandrew
Donate link: https://cash.me/$AndrewRMinionDesign
Tags: wishlist, membership, woocommerce, authorize, authorize.net
Requires at least: 3.0.1
Tested up to: 4.3.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin integrates WishList Member, Woocommerce, and the Authorize.net CIM gateway.

== Description ==

This plugin integrates WishList Member, Woocommerce, and the Authorize.net CIM gateway specifically, though it should work with any gateway that redirects to the Woocommerce thank-you page.

== Installation ==

1. Upload `wishlist-member-woocommerce-authorizenet-cim` to the `/wp-content/plugins/` directory
1. Activate the plugin through the “Plugins” menu in WordPress
1. In WishList Member settings, go to “Integration” and then to “Shopping Cart.” Choose “Generic” from the shoppingcart menu and press the “Set Shopping Cart” button.
1. Create the Woocommerce products and assign the appropriate SKU numbers as instructed by WishList Member

== Frequently Asked Questions ==

= How does it work? =

When the Woocommerce thankyou page is loaded, this plugin checks each purchased product and adds the user to the appropriate level(s) based on the product SKU(s).

When a subscription is cancelled or expires, the user is removed from the appropriate level(s).

== Changelog ==

= 1.0 =
* Initial version
