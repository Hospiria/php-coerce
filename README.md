# Coerce
Functions for coercing values to various data types

This library contains functions which takes an input value of any type, and attempt to 'coerce' it into an output value of a particular type, in a way that people would expect (following the principle of least astonishment). If the conversion is not possible, or if there is any ambiguity about how the conversion should be done, then the coercion will fail.

For example, the floating point number 2.0, can be coerced to the integer 2, because 2.0 is just another way of expressing the number 2.  Whereas attempting to coerce the floating point number 2.1 to an integer will fail.  In particular we don't try to guess whether some kind of rounding should be attempted, or which direction to round.  The input 2.1 does not represent an integer and that's that.

There are 2 functions for each target data type, which differ in the way that failed coercions are handled.

### Version 1

`Coerce::toString($input, &$output, int $flags = 0) : bool`  
`Coerce::toInt($input, &$output, int $flags = 0) : bool`  
`Coerce::toFloat($input, &$output, int $flags = 0) : bool`  
`Coerce::toArrayKey($input, &$output, int $flags = 0) : bool`  
`Coerce::toBool($input, &$output, int $flags = 0) : bool`  

In this version, the return value is a boolean indicating whether or not the coercion was successful (with the coerced value being stored in the `$output` parameter.

This version will be more useful when the input data comes from an untrusted source and therefore failed coercions are expected, and the code should always handle the failure gracefully.

The first parameter `$input` is passed by value, and contains the value (of any type) which will be coerced.
The second parameter `$output` is passed by reference - if coercion is possible, the result of the coercion will be populated into `$output`.
The optional third parameter `$flags` is a bitmask which allows some fine-tuning of the coercion behaviour (see flags section below).

The return value of the function is a boolean, `true` if the coercion was possible, `false` otherwise.
If the function returns `false` then the value of `$output` should be considered invalid and ignored.

### Version 2

`Coerce::toStringOrFail($input, int $flags = 0) : string`  
`Coerce::toIntOrFail($input, int $flags = 0) : int`  
`Coerce::toFloatOrFail($input, int $flags = 0) : float`  
`Coerce::toArrayKeyOrFail($input, int $flags = 0) : mixed`  
`Coerce::toBoolOrFail($input, int $flags = 0) : bool`  

In this version, the coerced value is the return value from the function, and if coercion failed then an `InvalidArgumentException` will be thrown.

This version will be more useful when the input data comes from a trusted source, and therefore the coercion should normally be expected to succeed, with failure being an exceptional event which may indicate a bug elsewhere in the code.

### Flags

Both versions of the coercion functions accept an optional $flags argument, which can be any bitwise combination of the following:

`Coerce::NULLABLE`  
If the input is `null` then coercion will be deemed to succeed and the output will also be `null`.

`Coerce::REJECT_BOOL`  
If the input is boolean type, do not attempt to cast into the target type and consider the coercion as failed.
So for example usually `Coerce::toString` would coerce boolean `true` to the string `'true'`, but if `REJECT_BOOL` is set, then boolean `true` will be rejected.
Note that for this flag does not apply to the `Coerce::toBool()` functions.

## Examples

Form validation, where user input in `$_POST` cannot be trusted

```php
use MadisonSolutions\Coerce\Coerce;

if (! Coerce::toInt($_POST['age'] ?? null, $age)) {
    die("Age must be an integer");
}
// $age is definitely now an integer, but might be negative
if ($age < 0) {
    die("Age must be positive");
}
echo "The user is {$age} years old";
```

Boolean value which has to be stored in a string database field
```php
use MadisonSolutions\Coerce\Coerce;

$sql = "SELECT option_value FROM options WHERE option_name = 'show_vat_on_prices'";
$show_vat = Coerce::toBoolOrFail($db->get_first_value($sql));
// $show_vat is definitely a boolean, otherwise an exception would have been thrown
if ($show_vat) {
    $price = $price * 1.2;
}
...

// saving the value back to the database
$sql = "UPDATE options SET option_value = ? WHERE option_name = 'show_vat_on_prices'";
$db->update($sql, Coerce::toStringOrFail($show_vat));
// The value send to the database was definitely a string representation of the boolean $show_vat flag - either 'true' or 'false'
```

## Functions

`Coerce::toString($input, &$output, int $flags = 0)`

Coerce a value to a string.

* A null value is coerced to the empty string (unless the `NULLABLE` flag is set in which case it will coerce to NULL).
* Boolean values are coerced to strings `'true'` or `'false'` (unless the `REJECT_BOOL` flag is set).
* Integers and floats are coerced to their standard string representation.
* No attempt is made to coerce array inputs to a string - the function will return `false`.
* Objects will be coerced if and only if, they have a defined `__toString()` method, IE the creator of the class has a specific string representation in mind and explicitly defined.

`Coerce::toInt($input, &$output, int $flags = 0)`

Coerce a value to an integer.

* Null values will not be coerced - the function will return `false` (unless the `NULLABLE` flag is set).
* Arrays and objects will not be coerced - the function will return `false`.
* Boolean true will be coerced to `1`, and false to `0` (unless the `REJECT_BOOL` flag is set).
* If the input value is the float or string representation of an integer, then the value will be coerced. For example the float `4.0`, the string `'4.0'` or the string `'4'` would all be coerced to the integer `4`.
* Any representation of a non-integer number will fail coercion - in particular numbers are never rounded to the nearest integer.

`Coerce::toFloat($input, &$output, int $flags = 0)`

Coerce a value to a float.

* Null values will not be coerced - the function will return `false` (unless the `NULLABLE` flag is set).
* Arrays and objects will not be coerced - the function will return `false`.
* Boolean `true` will be coerced to `1.0`, and `false` to `0.0` (unless the `REJECT_BOOL` flag is set).
* Numeric strings will be coerced to their float values.
* If the input is technically of float type, but is non-finite (eg `NAN` or `INF`), then the value will not be coerced and the function will return `false`.

`Coerce::toArrayKey($input, &$output, int $flags = 0)`

Coerce a value to type suitable for use as a key in a PHP array (string or int).

* Null values will not be coerced - the function will return `false` (unless the `NULLABLE` flag is set).
* Arrays and objects will not be coerced - the function will return `false`.
* Boolean values will not be coerced, due to an ambiguity whether to use a string (`'true'`, `'false'`) or an integer (`1`, `0`) as the key.
* Strings or floats representing an integer will be coerced to integer.
* Floats representing non-integer values will be converted to strings for use as an array key.
* Integers or any string which does not represent an integer will be returned unmodified for use an an array key.

`Coerce::toBool($input, &$output, int $flags = 0)`

Coerce a value to a boolean.

* Null values will not be coerced - the function will return `false` (unless the `NULLABLE` flag is set).
* Arrays and objects will not be coerced - the function will return `false`.
* A numeric value (int or float) exactly equal to zero is coerced to boolean `false`.
* A numeric value (int or float) exactly equal to one is coerced to boolean `true`.
* All other numeric values will not be coerced - the function will return `false`.
* The following strings (case-insensitive) will be coerced to boolean true: `'1'` `'true'` `'t'` `'yes'` `'y'` `'on'`.
* The following strings (case-insensitive) will be coerced to boolean false: `'0'` `'false'` `'f'` `'no'` `'n'` `'off'`.
* All other string values will not be coerced - the function will return `false`.
