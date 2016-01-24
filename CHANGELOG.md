# Changelog

All notable changes to this project will be documented in this file.

## [1.0.1] - 2026-03-16

### Changed
- Standardize composer.json: add type, homepage, scripts

## [1.0.0] - 2026-03-13

### Added
- `ApiResponse` static builder with `success`, `created`, `noContent`, `error`, `validationError`, `notFound`, and `paginated` methods
- `ResponsePayload` immutable value object implementing `JsonSerializable` and `Stringable`
- `toArray()` and `toJson()` serialization methods
- Automatic pagination metadata calculation
- Conditional `errors` and `meta` fields in output
- Full test suite
- PHPStan level 6 static analysis
- Laravel Pint code style enforcement
