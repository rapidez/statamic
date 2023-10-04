# Changelog 

[Unreleased changes](https://github.com/rapidez/statamic/compare/2.3.0...master)
## [2.3.0](https://github.com/rapidez/statamic/releases/tag/2.3.0) - 2023-09-22

### Added
* Honeypot to form template (#40)

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

