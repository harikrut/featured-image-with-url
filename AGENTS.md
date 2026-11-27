# Repository Guidelines

## Project Structure

- `featured-image-with-url.php` is the WordPress plugin entry point. `includes/class-harikrutfiwu.php` loads the admin and common classes. The admin class handles settings, metaboxes, and saving image URLs. The common class filters featured images and WooCommerce product images.
- `templates/` holds the featured-image and product-gallery metabox views. `assets/js/` and `assets/css/` contain the admin jQuery script and stylesheet.
- `README.txt` contains the WordPress.org listing and changelog. `.wordpress-org/` holds listing images and a Playground blueprint, and `.github/workflows/` contains CI and release automation.

## Build and Local Development

- `composer install --prefer-dist` installs the development tools.
- `composer phpcs` checks PHP against the WordPress coding standards in `phpcs.xml`. `composer lint-fix` applies automatic fixes. Review the resulting diff before committing.
- `composer phpcs -- --standard=phpcs-compat.xml` checks PHP compatibility against the repository's supported versions.
- `composer build` creates `featured-image-with-url.zip`. For local development, place the checkout in `wp-content/plugins/featured-image-with-url/` and activate it in WordPress. Activate WooCommerce when testing product image features.

## Coding Style

Use tabs for indentation, WordPress PHP spacing, and snake_case function and method names. Follow the existing `class-harikrutfiwu-*.php` filename pattern and `harikrutfiwu_` prefix for plugin identifiers. Keep changes compatible with WordPress 6.0 and PHP 7.4. Sanitize submitted URLs, check nonces and capabilities in save handlers, and escape output for its HTML context.

## Testing

The repository has no unit-test suite or coverage threshold. Run PHPCS locally and review the applicable GitHub Actions checks, including WordPress Plugin Check. In a local WordPress install, preview, save, reload, and remove an external featured image. Check frontend rendering and verify that Media Library thumbnails still work. For WooCommerce changes, exercise product thumbnails, galleries, and variation images on the storefront.

## Commits and Pull Requests

Use short, descriptive commit subjects, following examples such as `Add PHPCS action check.` and `Update tested up to 7.0`. In the pull request, summarize the behavior change, link related GitHub issues, and report the checks and manual scenarios you ran. Include screenshots for visible admin or storefront changes.
