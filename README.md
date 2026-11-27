## Description
Welcome to the Featured Image with URL Plugin repository on GitHub. Here you can browse the source, look at open issues and keep track of development. 

If you are not a developer, please use the [plugin page](https://wordpress.org/plugins/featured-image-with-url/) on WordPress.org.

## WooCommerce products REST API

Product responses include `featured_image_url` and `gallery_image_urls`. Send these fields when creating a product with `POST /wp-json/wc/v3/products` or updating one with `PUT /wp-json/wc/v3/products/<id>`.

```json
{
  "featured_image_url": "https://cdn.example.com/products/front.jpg",
  "gallery_image_urls": [
    "https://cdn.example.com/products/side.jpg",
    "https://cdn.example.com/products/back.jpg"
  ]
}
```

Use absolute HTTP or HTTPS URLs. Updating the gallery replaces its entire external URL list in the order provided. Omit a field to retain its value, or send `""` for the featured URL and `[]` for the gallery to clear them. WooCommerce's `images` field continues to manage Media Library attachments.

## Deployment
We use [10up/action-wordpress-plugin-deploy](https://github.com/10up/action-wordpress-plugin-deploy) Github Action for auto deployment to WordPress.
