# Changelog 

[Unreleased changes](https://github.com/rapidez/statamic/compare/4.6.6...4.6.6)
## [4.6.6](https://github.com/rapidez/statamic/releases/tag/4.6.6) - 2025-03-10

### Fixed

- Set absolute url for sitemap entries (#120)




## [4.6.5](https://github.com/rapidez/statamic/releases/tag/4.6.5) - 2025-02-19

### Fixed

- Forgot an import (#116)

## [4.6.4](https://github.com/rapidez/statamic/releases/tag/4.6.4) - 2025-02-19

### Fixed

- Load Statamic fallback controller directly (#115)

## [4.6.3](https://github.com/rapidez/statamic/releases/tag/4.6.3) - 2025-01-29

### Fixed

- StartPush on view composer (#110)
- Strict store id compare fix (#112)
- Don't hydrate globals twice (#99)

## [4.6.2](https://github.com/rapidez/statamic/releases/tag/4.6.2) - 2025-01-08

### Fixed

- Change cache key for nav tree (#100)
- Reload csrf token when Statamic reloads it (#103)
- Return if url path is not set on the linked entry (#105)
- Fixed infinite locking for certain requests (#108)

## [4.6.1](https://github.com/rapidez/statamic/releases/tag/4.6.1) - 2024-12-18

### Fixed

- Correct sitemap public url (#98)

## [4.6.0](https://github.com/rapidez/statamic/releases/tag/4.6.0) - 2024-12-11

### Added

- Static caching with invalidation on Magento urls (#94)

## [4.5.0](https://github.com/rapidez/statamic/releases/tag/4.5.0) - 2024-12-06

### Added

- Collections & taxonomies sitemaps with rapidez/sitemap (#93)

## [4.4.0](https://github.com/rapidez/statamic/releases/tag/4.4.0) - 2024-11-06

### Added

- Default responsive menu with caching (#79)

## [4.3.1](https://github.com/rapidez/statamic/releases/tag/4.3.1) - 2024-11-05

### Fixed

- Typos (#86, #87)

## [4.3.0](https://github.com/rapidez/statamic/releases/tag/4.3.0) - 2024-10-30

### Changed

- Removed justbetter/statamic-eloquent-driver-globalset-migration-generator (#83)

### Fixed

- Performance optimizations (#77)

## [4.2.0](https://github.com/rapidez/statamic/releases/tag/4.2.0) - 2024-09-26

### Changed

- License change GPL-3.0 > GPL-3.0-or-later (59b86c5)
- Replaced spatie/statamic-responsive-images with justbetter/statamic-glide-directive (#75)

### Fixed

- Don't load removed views (#76)



## [4.1.0](https://github.com/rapidez/statamic/releases/tag/4.1.0) - 2024-09-09

### Changed

- Cache the globals in memory (#73)

### Fixed

- Page builder form template error fix (#72)

## [4.0.1](https://github.com/rapidez/statamic/releases/tag/4.0.1) - 2024-08-27

### Fixed

- Clear the correct site cache when globals change (#70)

## [4.0.0](https://github.com/rapidez/statamic/releases/tag/4.0.0) - 2024-08-13

In this release we've added compatibility with Statamic 5. This forces you to configure your sites in the new `resources/sites.yaml`. You can still use your old config by referencing it in the `resources/sites.yaml`, see: https://github.com/rapidez/statamic#magento-store-id

### Added

- Statamic 5 support + install command (#69)

## [3.5.1](https://github.com/rapidez/statamic/releases/tag/3.5.1) - 2024-07-09

### Fixed

- Brand page container (#64)
- Use the selected site in the control panel (#66)

## [3.5.0](https://github.com/rapidez/statamic/releases/tag/3.5.0) - 2024-05-14

### Changed

- Get site based on Magento store ID (#65)

## [3.4.2](https://github.com/rapidez/statamic/releases/tag/3.4.2) - 2024-05-02

### Fixed

- Only push head when not running in console (#63)

## [3.4.1](https://github.com/rapidez/statamic/releases/tag/3.4.1) - 2024-05-02

### Changed

- Move globals to getGlobals function instead of constructor (#62)

## [3.4.0](https://github.com/rapidez/statamic/releases/tag/3.4.0) - 2024-05-01

### Changed

- Brandlist styling (#61)

## [3.3.0](https://github.com/rapidez/statamic/releases/tag/3.3.0) - 2024-04-12

### Added

 - Download images from Amasty brands if they're present (#60)

## [3.2.0](https://github.com/rapidez/statamic/releases/tag/3.2.0) - 2024-03-25

### Added

- Brand pages and overview (#59)

## [3.1.0](https://github.com/rapidez/statamic/releases/tag/3.1.0) - 2024-02-27

### Changed

- Moved responsive image observer to head stack (#52)

## [3.0.2](https://github.com/rapidez/statamic/releases/tag/3.0.2) - 2024-02-22

### Fixed

- Guarantee includeFirstSafe receives array (#56)

## [3.0.1](https://github.com/rapidez/statamic/releases/tag/3.0.1) - 2024-02-20

### Fixed

- Make sure the page collection has the site configured (#55)

## [3.0.0](https://github.com/rapidez/statamic/releases/tag/3.0.0) - 2024-02-14

See the upgrade notes: https://github.com/rapidez/statamic#upgrading

### Changed

- Implement optionalDeep (#53)

### Fixed

- Correct config path location (#54)

## [2.9.0](https://github.com/rapidez/statamic/releases/tag/2.9.0) - 2024-01-16

If you're listening to the events you should now use Eventy:
```diff
-Event::listen('rapidez-statamic:category-entry-data', fn($category) => [
+Eventy::addFilter('rapidez.statamic.category.entry.data', fn($category) => [

-Event::listen('rapidez-statamic:product-entry-data', fn($product) => [
+Eventy::addFilter('rapidez.statamic.product.entry.data', fn($product) => [

-Event::listen('rapidez-statamic:brand-entry-data', fn($brand) => [
+Eventy::addFilter('rapidez.statamic.brand.entry.data', fn($brand) => [
```

### Changed

- Use Eventy for import commands (#50)

## [2.8.2](https://github.com/rapidez/statamic/releases/tag/2.8.2) - 2024-01-04

### Fixed

- Use Rapidez core select component (#49)

## [2.8.1](https://github.com/rapidez/statamic/releases/tag/2.8.1) - 2024-01-02

### Fixed

- Use the current site instead of the selected site (#48)

## [2.8.0](https://github.com/rapidez/statamic/releases/tag/2.8.0) - 2023-12-20

### Changed

- Move config file to Rapidez folder (#44)

## [2.7.0](https://github.com/rapidez/statamic/releases/tag/2.7.0) - 2023-12-15

### Added

- Brands collection and import (#47)

## [2.6.0](https://github.com/rapidez/statamic/releases/tag/2.6.0) - 2023-12-12

### Added

- Product import command & add utility to start imports (#46)

### Changed

- Remove deprecated HasMany table mode (#45)

## [2.5.0](https://github.com/rapidez/statamic/releases/tag/2.5.0) - 2023-11-28

### Added

- Disabled site option (#43)

## [2.4.2](https://github.com/rapidez/statamic/releases/tag/2.4.2) - 2023-10-26

### Fixed

- Remove ambiguous keys from query (#41)

## [2.4.1](https://github.com/rapidez/statamic/releases/tag/2.4.1) - 2023-10-25

### Fixed

- Only use the base site url for the homepage (f1d331d)

## [2.4.0](https://github.com/rapidez/statamic/releases/tag/2.4.0) - 2023-10-25

### Added

- Command to import Magento categories into Statamic (#38)
- Always alternate href lang tags on home and filter option by group (0512f42)

## [2.3.0](https://github.com/rapidez/statamic/releases/tag/2.3.0) - 2023-09-22

### Added

- Honeypot to form template (#40)

## [2.2.0](https://github.com/rapidez/statamic/releases/tag/2.2.0) - 2023-07-06

### Added

- Automatic alternate hreflang link tags (9613e21)

## [2.1.2](https://github.com/rapidez/statamic/releases/tag/2.1.2) - 2023-07-04

### Fixed

- Overwrite the findByUrl method as it used on multiple places (589708e)

## [2.1.1](https://github.com/rapidez/statamic/releases/tag/2.1.1) - 2023-07-04

### Fixed

- Enabled localizable and propagate everywhere by default (871912d)
- Use url helpers (#37)

## [2.1.0](https://github.com/rapidez/statamic/releases/tag/2.1.0) - 2023-06-29

### Changed

- Use Blade directives from `rapidez/blade-directives` (#36)
- Determine the site by `MAGE_RUN_CODE` (7342d7e)

## [2.0.0](https://github.com/rapidez/statamic/releases/tag/2.0.0) - 2023-06-26

### Added

- Statamic 4 compatibility (#35)

## [1.2.0](https://github.com/rapidez/statamic/releases/tag/1.2.0) - 2023-06-07

### Added

- Conditional fields support (#34)

## [1.1.1](https://github.com/rapidez/statamic/releases/tag/1.1.1) - 2023-06-06

### Fixed

- Fallback when validation doesn't exist & rename forms to form (#33)

## [1.1.0](https://github.com/rapidez/statamic/releases/tag/1.1.0) - 2023-05-25

### Added

- Forms integration (0b6eca3)

## [1.0.2](https://github.com/rapidez/statamic/releases/tag/1.0.2) - 2023-05-04

### Fixed

- Page builder include fallback fix (1023025, 1929cb3)

## [1.0.1](https://github.com/rapidez/statamic/releases/tag/1.0.1) - 2023-05-04

### Fixed

- @includeFirst fallback (f7fcb88)

## [1.0.0](https://github.com/rapidez/statamic/releases/tag/1.0.0) - 2023-05-04

### Changed

- Refactor (#28)

## [0.9.6](https://github.com/rapidez/statamic/releases/tag/0.9.6) - 2023-03-06

### Fixed

- Fix locale -> site (#26)

## [0.9.5](https://github.com/rapidez/statamic/releases/tag/0.9.5) - 2023-02-24

### Fixed

- Query instead of using a collection (#25)

## [0.9.4](https://github.com/rapidez/statamic/releases/tag/0.9.4) - 2023-02-17

### Fixed

- Query with site instead of locale (#24)

## [0.9.3](https://github.com/rapidez/statamic/releases/tag/0.9.3) - 2023-02-08

### Fixed

- Slugs should not start/end with a slash (521a0b2)

## [0.9.2](https://github.com/rapidez/statamic/releases/tag/0.9.2) - 2023-02-08

### Fixed

- Removed unused uses (#23)
- Query with site instead of locale (d7705ef)
- Refactored deletes (641739e)

## [0.9.1](https://github.com/rapidez/statamic/releases/tag/0.9.1) - 2023-02-08

### Fixed

- Use trait instead of overwriting the entry repository (bf44710)
- Removed unique from CreateProducts Job (#22)

## [0.9.0](https://github.com/rapidez/statamic/releases/tag/0.9.0) - 2023-02-07

### Changed

- Add product visibility to product import and optimized updateOrCreate (#21)

### Fixed

- Use Cache::forever (#20)

## [0.8.2](https://github.com/rapidez/statamic/releases/tag/0.8.2) - 2023-02-06

### Fixed

- Fixed cache not actually updating on save (#19)

## [0.8.1](https://github.com/rapidez/statamic/releases/tag/0.8.1) - 2023-01-30

### Fixed

- Fixed bug with route cache and return of statamic view (#18)

## [0.8.0](https://github.com/rapidez/statamic/releases/tag/0.8.0) - 2023-01-27

### Added

- Added caching for product page entries (#17)

## [0.7.0](https://github.com/rapidez/statamic/releases/tag/0.7.0) - 2023-01-27

### Changed

- Refactored Product import and added separate delete job (#16)

## [0.6.0](https://github.com/rapidez/statamic/releases/tag/0.6.0) - 2022-12-01

### Changed

- Refactored routing (#15)

## [0.5.0](https://github.com/rapidez/statamic/releases/tag/0.5.0) - 2022-11-28

### Changed

- Only index indexable products (#14)

### Fixed

- Fixed url path name (#12)
- Fixed override bug (#13)

## [0.4.0](https://github.com/rapidez/statamic/releases/tag/0.4.0) - 2022-11-17

### Added

- Product sync (#7)
- Added Magento product and category imports (#9)

### Fixed

- Globals everywhere via singleton (#10)
- Cleaner syntax (dbb95ec)
- Product collection data for the correct locale (ca4131e)

## [0.3.0](https://github.com/rapidez/statamic/releases/tag/0.3.0) - 2022-10-20

### Changed

- Improved readability of code and data collection that is returned (#6)

## [0.2.1](https://github.com/rapidez/statamic/releases/tag/0.2.1) - 2022-10-03

### Fixed

- Give data as a Statamic values object to templates (#5)

## [0.2.0](https://github.com/rapidez/statamic/releases/tag/0.2.0) - 2022-08-10

### Added

- Globals support (#4)

## [0.1.0](https://github.com/rapidez/statamic/releases/tag/0.1.0) - 2022-07-28

Initial release

