# Upgrading Rapidez Statamic from 4.x to 5.x

## Steps to Upgrade

1. **Upgrade from `rapidez/statamic` 4.x to 5.x**
Ensure that all dependencies are compatible with version 5.x and follow the upgrade instructions provided in the official documentation.

```bash
composer update rapidez/statamic -W
```

2. **Remove Blueprint Directories**
> [!IMPORTANT]
> Before removing the blueprint and content directories, ensure you have copied over any custom fields or configurations you need to retain.

You can remove the following blueprint directories:
- `resources/blueprints/collections/categories`
- `resources/blueprints/collections/products`
- `resources/blueprints/collections/brands`

3. **Remove Content Directories**
The following content directories can also be removed:
- `content/collections/categories`
- `content/collections/products`
- `content/collections/brands`
- `content/collections/products.yaml`
- `content/collections/categories.yaml`
- `content/collections/brands.yaml`

4. **Clear Cache**
Run the following command to clear the cache:
- `php artisan optimize:clear`

5. **Import Page Builder Fieldset**
Add the following line to the specified runway resources blueprints:
- `resources/blueprints/vendor/runway/product.yaml`
- `resources/blueprints/vendor/runway/category.yaml`
- `resources/blueprints/vendor/runway/brand.yaml`:

```yaml
import: page_builder
```

6. **Change Runway Collections to Not Read-Only**
Update the runway collections to be not read-only by modifying the following code in `config/statamic.php`:
```php
\Rapidez\Statamic\Models\Product::class => [
    'name' => 'Products',
    'read_only' => false,
    'title_field' => 'sku',
    'cp_icon' => 'table',
],

\Rapidez\Statamic\Models\Category::class => [
    'name' => 'Categories',
    'read_only' => false,
    'title_field' => 'name',
    'cp_icon' => 'array',
],

\Rapidez\Statamic\Models\Brand::class => [
    'name' => 'Brands',
    'read_only' => false,
    'title_field' => 'value_store',
    'cp_icon' => 'tags',
    'order_by' => 'sort_order',
],
```
Additionally, add `visibility: read_only` to the system fields of Magento in the runway blueprints:
- In `category.yaml`, the fields are: `entity_id`, `name`.
- In `brand.yaml`, the fields are: `option_id`, `sort_order`, `value_admin`, `value_store`.
- In `product.yaml`, the fields are: `entity_id`, `sku`, `name`.

## Note
- Make sure to back up your project before performing these steps to prevent any data loss.
- Additionally, ensure that if there are any custom fields in the old blueprints we deleted, they are copied over to the new runway blueprints.