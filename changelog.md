# Changelog

## [1.1.0] - 2019-05-30

### Added

 - This changelog.

 ### Changed

  - Coercion functions should now always set the `$output` variable to `null` if
    coercion fails. Perviously the behaviour was inconsistent, as sometimes the
    `$output` variable was used during the coercion process. This should help
    people avoid some unexpected bugs particularly when coercing several values
    in a loop and reusing the same `$output` variable.
