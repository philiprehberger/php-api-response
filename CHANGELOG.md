# Changelog

All notable changes to `php-api-response` will be documented in this file.

## [Unreleased]

## [1.1.1] - 2026-03-31

### Changed
- Standardize README to 3-badge format with emoji Support section
- Update CI checkout action to v5 for Node.js 24 compatibility
- Add GitHub issue templates, dependabot config, and PR template

## [1.1.0] - 2026-03-22

### Added
- `withMeta(array $meta)` fluent method on `ResponsePayload` — returns a new instance with merged metadata
- `withHeaders(array $headers)` fluent method on `ResponsePayload` — returns a new instance with custom response headers
- `withStatusCode(int $code)` fluent method on `ResponsePayload` — returns a new instance with overridden HTTP status code
- `headers` property on `ResponsePayload` for carrying custom response headers

## [1.0.3] - 2026-03-23

### Fixed
- Standardize CHANGELOG preamble to use package name

## [1.0.2] - 2026-03-17

### Changed
- Standardized package metadata, README structure, and CI workflow per package guide

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
