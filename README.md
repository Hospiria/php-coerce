# Coerce
Functions for coercing values to various data types

This library contains functions which takes an input value of any type, and attempt to 'coerce' it into an output value of a particular type, in a way that people would expect (following the principle of least astonishment). The functions always return a boolean value indicating whenther the coersion was successful. If the conversion is not possible, or if there is any ambiguity about how the conversion should be done, then the coersion will fail and the function will return false.

For example, the floating point number 2.0, can be coerced to the integer 2, because 2.0 is just another way of expressing the number 2.  Whereas attempting to coerce the floating point number 2.1 to an integer will fail.  In particular we don't try to guess whether some kind of rounding should be attempted, or which direction to round.  The input 2.1 does not represent an integer and that's that.

All of the functions have the same signature:

`($input, &$output) : bool`

The first parameter `$input` is passed by value, and contains the value (of any type) which will be coerced.
The second parameter `$output` is passed by reference - if coersion is possible, the result of the coersion will be populated into `$output`.
The return value of the function is a boolean, `true` if the coersion was possible, `false` otherwise.

If the function returns `false` then the value of `$output` should be considered invalid and ignored.

### Examples

Form validation, where user input in $_POST cannot be trusted

```php
use MadisonSolutions\Coerce\Coerce;

$age_input = $_POST['age'] ?? null;
if (! Coerce::toInt($age_input, $age)) {
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
if (! Coerce::toBool($db->get_first_value($sql), $show_vat) {
    throw new \Exception("Non boolean value for show_vat_on_prices option");
}
// $show_vat is definitely now a boolean
if ($show_vat) {
    $price = $price * 1.2;
}
...

// saving the value back to the database
$sql = "UPDATE options SET option_value = ? WHERE option_name = 'show_vat_on_prices'";
if (! Coerce::toString($show_vat, $option_value)) {
    throw new \Exception("Could not convert show vat option to string");
}
// $option_value is definitely now a string representation of the boolean $show_vat flag - either 'true' or 'false'
$db->update($sql, $option_value);
```

### Functions

`Coerce::toString($input, &$output)`

Coerce a value to a string.

* A null value is coerced to the empty string.
* Boolean values are coerced to strings `'true'` or `'false'`.
* Integers and floats are coerced to their standard string representation.
* No attempt is made to coerce array inputs to a string - the function will return `false`.
* Objects will be coerced if and only if, they have a defined `__toString()` method, IE the creater of the class has a specific string representation in mind and explicity defined.

`Coerce::toInt($input, &$output)`

Coerce a value to an integer.

* Null values, arrays and objects will not be coerced - the function will return `false`.
* Boolean true will be coerced to `1`, and false to `0`.
* If the input value is the float or string representation of an integer, then the value will be coerced. For example the float `4.0`, the string `'4.0'` or the string `'4'` would all be coerced to the integer `4`.
* Any representation of a non-integer number will fail coersion - in particular numbers are never rounded to the nearest integer.

`Coerce::toFloat($input, &$output)`

Coerce a value to a float.

* Null values, arrays and objects will not be coerced - the function will return false.
* Boolean `true` will be coerced to `1.0`, and `false` to `0.0`.
* Numeric strings will be coerced to their float values.
* If the input is technically of float type, but is non-finite (eg `NAN` or `INF`), then the value will not be coerced and the function will return `false`.

`Coerce::toArrayKey($input, &$output)`

Coerce a value to type suitable for use as a key in a PHP array (string or int).

* Null values, arrays and objects will not be coerced - the function will return `false`.
* Boolean values will not be coerced, due to an ambiguity whether to use a string (`'true'`, `'false'`) or an integer (`1`, `0`) as the key.
* Strings or floats representing an integer will be coerced to integer.
* Floats representing non-integer values will be converted to strings for use as an array key.
* Integers or any string which does not represent an integer will be returned unmodified for use an an array key.

`Coerce::toBool($input, &$output)`

Coerce a value to a boolean.

* Null values, arrays and objects will not be coerced - the function will return `false`.
* A numeric value (int or float) exactly equal to zero is coerced to boolean `false`.
* A numeric value (int or float) exactly equal to one is coerced to boolean `true`.
* All other numeric values will not be coerced - the function will return `false`.
* The following strings (case-insensitive) will be coerced to boolean true: `'1'` `'true'` `'t'` `'yes'` `'y'` `'on'`.
* The following strings (case-insensitive) will be coerced to boolean false: `'0'` `'false'` `'f'` `'no'` `'n'` `'off'`.
* All other string values will not be coerced - the function will return `false`.

