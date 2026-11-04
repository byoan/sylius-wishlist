# Sylius Wishlist Plugin

Lets customers and guests save product variants to a wishlist from the product page and product
listings, then add them to the native Sylius cart. Administrators get a read-only view of customer
wishlists.

Requires PHP 8.3+ and Sylius 2.3+. Migrations target MySQL and MariaDB.

## Installation

1. Require the package:

   ```sh
   composer require byoan/sylius-wishlist-plugin
   ```

2. Enable the bundle in `config/bundles.php`, if Symfony Flex has not done it:

   ```php
   Byoan\SyliusWishlistPlugin\ByoanSyliusWishlistPlugin::class => ['all' => true],
   ```

3. Import the configuration in `config/packages/byoan_sylius_wishlist.yaml`:

   ```yaml
   imports:
       - { resource: "@ByoanSyliusWishlistPlugin/config/config.yaml" }
   ```

4. Import the routes in `config/routes/byoan_sylius_wishlist.yaml`:

   ```yaml
   byoan_sylius_wishlist:
       resource: "@ByoanSyliusWishlistPlugin/config/routes.yaml"
   ```

5. Run the migrations:

   ```sh
   bin/console doctrine:migrations:migrate
   ```

No core entity has to be extended and no core template is overridden.

## How it works

- A wishlist belongs to one channel and holds product variants, each at most once.
- The data model supports several named wishlists per customer and channel. In this version, every
  action uses the default wishlist.
- **Customer wishlists** are stored in the database.
- **Guest wishlists** are stored in the database too, without a customer and with a random token.
  The Symfony session holds only that token, so a guest keeps access for as long as the session
  lasts. Administrators do not see these wishlists.
- **On registration**, guest wishlists are assigned to the new customer, and the token is removed
  from the wishlists and the session. Logging in to an existing account does not move them.
- **Shop routes** use the native resource controller, configured in `config/routes/shop.yaml`, with
  business rules in `WishlistItemListener`. Adding a selection to the cart is the only custom action.
- **Adding to the cart** uses the same command, validation and adder as the native add-to-cart form.
  One unit of each variant is added, and it stays in the wishlist.

A product is unavailable when it is disabled, not in the channel, has no price in the channel, or
its variant is disabled or out of stock. It stays in the wishlist, marked as unavailable. Listings
have no variant picker, so they use the default variant, or another one in stock if that one is not
available.

## Extending

- **Models and repository:** override `byoan_sylius_wishlist.resources.*.classes` and extend the
  mapped superclasses.
- **Services:** decorate the services through their interfaces.
- **Storefront and admin pages:** use the `byoan_sylius_wishlist.*` Twig hooks.
