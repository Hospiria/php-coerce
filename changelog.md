# Changelog

## [1.2.0] - 2019-06-07

### Added

- Constants Coerce::NULLABLE and Coerce::REJECT_BOOL can now be supplied as
  flags to coercion functions in order to modify the default behaviour when
  encountering nulls and booleans.

### Breaking changes

- Empty string inputs are now always treated the same as null.

## [1.1.0] - 2019-05-30

### Added

- This changelog.

### Changed

- Coercion functions should now always set the `$output` variable to `null` if
  coercion fails. Perviously the behaviour was inconsistent, as sometimes the
  `$output` variable was used during the coercion process. This should help
  people avoid some unexpected bugs particularly when coercing several values
  in a loop and reusing the same `$output` variable.
