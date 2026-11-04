# Sylius Wishlist Plugin

Lets customers and guests save product variants to a wishlist from the product page and product
listings, then add them to the native Sylius cart. Administrators get a read-only view of customer
and guest wishlists.

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
- A shopper can have several named wishlists per channel. Each one holds its own products, and the
  same product can be in several wishlists.
- One wishlist is the default. It is created on first use and is the target of the add button. The
  shopper can make another wishlist the default. The current default can be renamed but not deleted.
  A dropdown next to the button lists the shopper's other wishlists.
- **Customer wishlists** are stored in the database.
- **Guest wishlists** are stored in the database too, without a customer and with a random token.
  The Symfony session holds only that token, so a guest keeps access for as long as the session
  lasts. Administrators see these wishlists marked as guest, without the token.
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

## API

The shop API exposes the same wishlists as the storefront, under `/api/v2/shop`. The Sylius API has to
be enabled with `sylius_api.enabled: true`. Interactive documentation is at `/api/v2/docs`.

| Method | Endpoint | Operation | Success |
|---|---|---|---|
| `GET` | `/wishlists` | List the shopper's wishlists | 200 |
| `POST` | `/wishlists` | Create a wishlist: `{"name": "Birthday"}` | 201 |
| `GET` | `/wishlists/{id}` | Get a wishlist and its items | 200 |
| `PATCH` | `/wishlists/{id}` | Rename a wishlist: `{"name": "Birthday Ideas"}` | 200 |
| `DELETE` | `/wishlists/{id}` | Delete a wishlist | 204 |
| `POST` | `/wishlists/{id}/items` | Add a product: `{"productVariantCode": "T_SHIRT-variant-0"}` | 201 |
| `GET` | `/wishlists/{wishlistId}/items/{id}` | Get an item | 200 |
| `DELETE` | `/wishlists/{wishlistId}/items/{id}` | Remove an item | 204 |
| `POST` | `/wishlists/{wishlistId}/items/{id}/move` | Move an item: `{"targetWishlistId": 12}` | 200 |

`PATCH` requests use `Content-Type: application/merge-patch+json`. Other requests with a body use
`application/json` or `application/ld+json`.

Errors: `404` for a wishlist that does not exist or belongs to someone else, `422` for invalid input,
and `409` for a product already in the wishlist or an attempt to delete the default wishlist.

Items report `productVariantCode`, `productVariantName`, `productCode`, `productName` and `available`.
A product that cannot be bought stays in the response with `available: false`.

**Customers** authenticate with the native Sylius token:

```sh
curl -X POST http://localhost:8080/api/v2/shop/customers/token \
  -H 'Content-Type: application/json' \
  -d '{"email": "customer@example.com", "password": "secret"}'

curl http://localhost:8080/api/v2/shop/wishlists -H 'Authorization: Bearer <token>'
```

**Guests** send no credentials. Creating a guest's first wishlist returns a `token`, which identifies
that guest in later requests through the `X-Wishlist-Token` header:

```sh
curl -X POST http://localhost:8080/api/v2/shop/wishlists \
  -H 'Content-Type: application/json' -d '{"name": "My Favorites"}'

curl http://localhost:8080/api/v2/shop/wishlists -H 'X-Wishlist-Token: <token>'
```

Registering through the API does not transfer a guest's wishlists. Only storefront registration does.

