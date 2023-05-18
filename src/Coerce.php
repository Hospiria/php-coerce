<?php
namespace MadisonSolutions\Coerce;

use InvalidArgumentException;
use LogicException;

class Coerce
{
    protected static function isNullish(mixed $input): bool
    {
        return (is_null($input) || $input === '');
    }

    /**
     * Coerce a value to string type
     *
     * Notes:
     * Boolean values are coerced to strings 'true' or 'false' (unless $reject_bool=true)
     * Integers and floats are coerced to their standard string representation
     * No attempt is made to coerce array inputs to a string - the function will return false
     * Objects will be coerced if and only if, they have a defined __toString() method
     * IE the creater of the class has a specific string representation explicity defined
     * Empty strings and nulls are coerced to the empty string
     *
     * @param mixed $input The input value
     * @param mixed &$output The coerced string value will be written to the $output variable
     * @param bool $reject_bool If false (default), boolean values will be coerced to 'true' or 'false', otherwise they will be rejected
     * @return bool True if $input was successfully coerced to a string, false otherwise
     * @param-out string $output
     */
    public static function toString(mixed $input, mixed &$output, bool $reject_bool = false): bool
    {
        $output = '';
        if (Coerce::isNullish($input)) {
            return true;
        }
        if (is_bool($input)) {
            if ($reject_bool) {
                return false;
            } else {
                $output = $input ? 'true' : 'false';
                return true;
            }
        }
        if (is_scalar($input)) {
            $output = (string) $input;
            return true;
        }
        if (is_object($input) && method_exists($input, '__toString')) {
            $output = $input->__toString();
            return true;
        }
        return false;
    }

    /**
     * Coerce a value to string type or null
     *
     * Null values and empty strings will be coerced to null
     * All other values coerced as per Coerce::toString
     *
     * @param-out ?string $output
     */
    public static function toStringOrNull(mixed $input, mixed &$output, bool $reject_bool = false): bool
    {
        if (Coerce::isNullish($input)) {
            $output = null;
            return true;
        }
        return Coerce::toString($input, $output, reject_bool: $reject_bool);
    }

    /**
     * Coerce a value to string type
     *
     * @param mixed $input The input value
     * @param bool $reject_bool If false (default), boolean values will be coerced to 'true' or 'false', otherwise they will be rejected
     * @return string The coerced value
     * @throws InvalidArgumentException If the input value cannot be coerced to a string
     */
    public static function toStringOrFail(mixed $input, bool $reject_bool = false): string
    {
        if (! Coerce::toString($input, $output, reject_bool: $reject_bool)) {
            throw new InvalidArgumentException("Unable to coerce value to string");
        }
        return $output;
    }

    /**
     * Coerce a value to string or null
     */
    public static function toStringOrNullOrFail(mixed $input, bool $reject_bool = false): ?string
    {
        if (! Coerce::toStringOrNull($input, $output, reject_bool: $reject_bool)) {
            throw new InvalidArgumentException("Unable to coerce value to string");
        }
        return $output;
    }


    /**
     * Coerce a value to integer type
     *
     * Notes
     * Null values, arrays and objects will not be coerced
     * The function will return false for arrays and objects.
     * Empty strings inputs are treated as null
     * Boolean true will be coerced to 1, and false to 0 unless $reject_bool=true
     * If the input value is the float or string representation of an integer, then the value will be coerced,
     * For example the float 4.0, the string '4.0' or the string '4' would all be coerced to the integer 4
     * Any representation of a non-integer number will fail coersion - in particular numbers are not rounded to the nearest integer (unless $round_floats parameter is true)
     *
     * @param mixed $input The input value
     * @param mixed &$output If coersion is successful, the integer value will be written to the $output variable
     * @param bool $reject_bool If false (default), boolean values will be coerced to 1 or 0, otherwise they will be rejected
     * @param bool $round_floats If false (default), non-integer numeric values (eg 2.2) are rejected, if true, non-integer numeric values are rounded to the nearest integer (2.2 -> 2)
     * @param bool $reject_negative If false (default), negative values are accepted, if true negative values are rejected
     * @param bool $reject_zero If false (default), zero is accepted, if true zero is rejected
     * @return bool True if $input was successfully coerced to an integer, false otherwise
     * @param-out int $output
     */
    public static function toInt(mixed $input, mixed &$output, bool $reject_bool = false, bool $round_floats = false, bool $reject_negative = false, bool $reject_zero = false): bool
    {
        $output = 0;
        if (Coerce::isNullish($input)) {
            return false;
        } elseif (is_int($input)) {
            $output = $input;
        } elseif (is_bool($input)) {
            if ($reject_bool) {
                return false;
            }
            $output = $input ? 1 : 0;
        } else {
            if (is_string($input) && is_numeric($input)) {
                $input = (float) $input;
            }
            if (is_float($input) && is_finite($input)) {
                if ($round_floats) {
                    $output = (int) round($input);
                } else {
                    if (intval($input) == $input) {
                        $output = intval($input);
                    } else {
                        return false;
                    }
                }
            } else {
                return false;
            }
        }
        return !($reject_negative && $output < 0) && !($reject_zero && $output === 0);
    }

    /**
     * Coerce a value to integer type or null
     *
     * Null values and empty strings will be coerced to null
     * All other values coerced as per Coerce::toInt
     *
     * @param-out ?int $output
     */
    public static function toIntOrNull(mixed $input, mixed &$output, bool $reject_bool = false, bool $round_floats = false, bool $reject_negative = false, bool $reject_zero = false): bool
    {
        if (Coerce::isNullish($input)) {
            $output = null;
            return true;
        }
        return Coerce::toInt($input, $output, reject_bool: $reject_bool, round_floats: $round_floats, reject_negative: $reject_negative, reject_zero: $reject_zero);
    }

    /**
     * Coerce a value to integer type
     *
     * @param mixed $input The input value
     * @param bool $reject_bool If false (default), boolean values will be coerced to 1 or 0, otherwise they will be rejected
     * @param bool $round_floats If false (default), non-integer numeric values (eg 2.2) are rejected, if true, non-integer numeric values are rounded to the nearest integer (2.2 -> 2)
     * @param bool $reject_negative If false (default), negative values are accepted, if true negative values are rejected
     * @param bool $reject_zero If false (default), zero is accepted, if true zero is rejected
     * @return int The coerced value
     * @throws InvalidArgumentException If the input value cannot be coerced to an integer
     */
    public static function toIntOrFail(mixed $input, bool $reject_bool = false, bool $round_floats = false, bool $reject_negative = false, bool $reject_zero = false): int
    {
        if (! Coerce::toInt($input, $output, reject_bool: $reject_bool, round_floats: $round_floats, reject_negative: $reject_negative, reject_zero: $reject_zero)) {
            throw new InvalidArgumentException("Unable to coerce value to integer");
        }
        return $output;
    }

    /**
     * Coerce a value to integer type or null
     */
    public static function toIntOrNullOrFail(mixed $input, bool $reject_bool = false, bool $round_floats = false, bool $reject_negative = false, bool $reject_zero = false): ?int
    {
        if (! Coerce::toIntOrNull($input, $output, reject_bool: $reject_bool, round_floats: $round_floats, reject_negative: $reject_negative, reject_zero: $reject_zero)) {
            throw new InvalidArgumentException("Unable to coerce value to integer");
        }
        return $output;
    }


    /**
     * Coerce a value to float type
     *
     * Notes
     * Null values, arrays and objects will not be coerced
     * The function will return false for arrays and objects.
     * Empty strings inputs are treated as null
     * Boolean true will be coerced to 1.0, and false to 0.0 unless $reject_bool=true
     * Numeric strings will be coerced to their float values
     * If the input is technically of float type, but is non-finite (eg NAN or INF),
     * then the value will not be coerced and the function will return false.
     *
     * @param mixed $input The input value
     * @param mixed &$output If coersion is successful, the float value will be written to the $output variable
     * @param bool $reject_bool If false (default), boolean values will be coerced to 1.0 or 0.0, otherwise they will be rejected
     * @return bool True if $input was successfully coerced to a float, false otherwise
     * @param-out float $output
     */
    public static function toFloat(mixed $input, mixed &$output, bool $reject_bool = false): bool
    {
        $output = 0.0;
        if (Coerce::isNullish($input)) {
            return false;
        }
        if (is_string($input) && is_numeric($input)) {
            $input = floatval($input);
        }
        if (is_float($input)) {
            if (is_finite($input)) {
                $output = $input;
                return true;
            } else {
                return false;
            }
        }
        if (is_int($input)) {
            $output = floatval($input);
            return true;
        }
        if (is_bool($input)) {
            if ($reject_bool) {
                return false;
            } else {
                $output = ($input ? 1.0 : 0.0);
                return true;
            }
        }
        return false;
    }

    /**
     * Coerce a value to float type or null
     *
     * Null values and empty strings will be coerced to null
     * All other values coerced as per Coerce::toFloat
     *
     * @param-out ?float $output
     */
    public static function toFloatOrNull(mixed $input, mixed &$output, bool $reject_bool = false): bool
    {
        if (Coerce::isNullish($input)) {
            $output = null;
            return true;
        }
        return Coerce::toFloat($input, $output, reject_bool: $reject_bool);
    }

    /**
     * Coerce a value to float type
     *
     * @param mixed $input The input value
     * @param bool $reject_bool If false (default), boolean values will be coerced to 'true' or 'false', otherwise they will be rejected
     * @return float The coerced value
     * @throws InvalidArgumentException If the input value cannot be coerced to a float
     */
    public static function toFloatOrFail(mixed $input, bool $reject_bool = false): float
    {
        if (! Coerce::toFloat($input, $output, reject_bool: $reject_bool)) {
            throw new InvalidArgumentException("Unable to coerce value to float");
        }
        return $output;
    }

    /**
     * Coerce a value to float type or null
     */
    public static function toFloatOrNullOrFail(mixed $input, bool $reject_bool = false): ?float
    {
        if (! Coerce::toFloatOrNull($input, $output, reject_bool: $reject_bool)) {
            throw new InvalidArgumentException("Unable to coerce value to float");
        }
        return $output;
    }


    /**
     * Coerce a value to type suitable for use as a key in a PHP array (string or int)
     *
     * Notes:
     * Null values, arrays and objects will not be coerced
     * The function will return false for arrays and objects.
     * Technically an empty string CAN be used as an array key in PHP...
     * however, this is a very uncommon situation. We think it will cause fewer shocks
     * to people if we treat empty strings as invalid for array keys.
     * This function therefore treats an empty string as null and returns false
     * Boolean values will not be coerced, due to an ambiguity whether to use a string ('true', 'false') or an integer (1, 0) as the key.
     * Strings or floats representing an integer will be coerced to integer.
     * Floats representing non-integer values will be converted to strings for use as an array key.
     * Integers or any string which does not represent an integer will be returned unmodified for use an an array key.
     *
     * @param mixed $input The input value
     * @param mixed &$output If coersion is successful, the coerced string or integer value will be written to the $output variable
     * @return bool True if $input was successfully coerced to an array key value, false otherwise
     * @param-out int | string $output
     */
    public static function toArrayKey(mixed $input, mixed &$output): bool
    {
        $output = '';
        if (Coerce::isNullish($input)) {
            return false;
        }
        if (is_bool($input)) {
            return false;
        }
        if (is_float($input) && ! is_finite($input)) {
            return false;
        }
        if (Coerce::toInt($input, $intval)) {
            $output = $intval;
            return true;
        }
        if (Coerce::toString($input, $stringval)) {
            $output = $stringval;
            return true;
        }
        return false;
    }

    /**
     * Coerce a value to type suitable for use as a key in a PHP array (string or int)
     *
     * Null values and empty strings will be coerced to null
     * All other values coerced as per Coerce::toArrayKey
     *
     * @param-out int|string|null $output
     */
    public static function toArrayKeyOrNull(mixed $input, mixed &$output): bool
    {
        if (Coerce::isNullish($input)) {
            $output = null;
            return true;
        }
        return Coerce::toArrayKey($input, $output);
    }

    /**
     * Coerce a value to a type suitable for use as an array key (string or int)
     *
     * @param mixed $input The input value
     * @return int|string The coerced value
     * @throws InvalidArgumentException If the input value cannot be coerced to an array key value
     */
    public static function toArrayKeyOrFail(mixed $input): int|string
    {
        if (! Coerce::toArrayKey($input, $output)) {
            throw new InvalidArgumentException("Unable to coerce value to array key");
        }
        return $output;
    }

    /**
     * Coerce a value to a type suitable for use as an array key (string or int), or null
     */
    public static function toArrayKeyOrNullOrFail(mixed $input): int|string|null
    {
        if (! Coerce::toArrayKeyOrNull($input, $output)) {
            throw new InvalidArgumentException("Unable to coerce value to array key");
        }
        return $output;
    }


    /**
     * Coerce a value to boolean type
     *
     * Notes:
     * Null values, arrays and objects will not be coerced
     * The function will return false for arrays and objects.
     * A numeric value (int or float) exactly equal to zero is coerced to boolean false
     * A numeric value (int or float) exactly equal to one is coerced to boolean true
     * All other numeric values will not be coerced - the function will return false
     * The following strings (case-insensitive) will be coerced to boolean true:
     * '1' 'true' 't' 'yes' 'y' 'on'
     * The following strings (case-insensitive) will be coerced to boolean false:
     * '0' 'false' 'f' 'no' 'n' 'off'
     * All other string values will not be coerced - the function will return false
     *
     * @param mixed $input The input value
     * @param mixed &$output If coersion is successful, the boolean value will be written to the $output variable
     * @return bool True if $input was successfully coerced to a boolean, false otherwise
     * @param-out bool $output
     */
    public static function toBool(mixed $input, mixed &$output): bool
    {
        $output = false;
        if (Coerce::isNullish($input)) {
            return false;
        }
        if (is_bool($input)) {
            $output = $input;
            return true;
        }
        if (is_int($input) || is_float($input)) {
            if ($input == 0) {
                $output = false;
                return true;
            } elseif ($input == 1) {
                $output = true;
                return true;
            } else {
                return false;
            }
        }
        if (is_string($input)) {
            switch (strtolower($input)) {
                case '1':
                case 'true':
                case 't':
                case 'yes':
                case 'y':
                case 'on':
                    $output = true;
                    return true;
                case '0':
                case 'false':
                case 'f':
                case 'no':
                case 'n':
                case 'off':
                    $output = false;
                    return true;
                default:
                    return false;
            }
        }
        return false;
    }

    /**
     * Coerce a value to boolean type or null
     *
     * Null values and empty strings will be coerced to null
     * All other values coerced as per Coerce::toArrayKey
     *
     * @param-out ?bool $output
     */
    public static function toBoolOrNull(mixed $input, mixed &$output): bool
    {
        if (Coerce::isNullish($input)) {
            $output = null;
            return true;
        }
        return Coerce::toBool($input, $output);
    }

    /**
     * Coerce a value to a boolean
     *
     * @param mixed $input The input value
     * @return bool The coerced value
     * @throws InvalidArgumentException If the input value cannot be coerced to a boolean
     */
    public static function toBoolOrFail(mixed $input): bool
    {
        if (! Coerce::toBool($input, $output)) {
            throw new InvalidArgumentException("Unable to coerce value to boolean");
        }
        return $output;
    }

    /**
     * Coerce a value to a boolean or null
     */
    public static function toBoolOrNullOrFail(mixed $input): ?bool
    {
        if (! Coerce::toBoolOrNull($input, $output)) {
            throw new InvalidArgumentException("Unable to coerce value to boolean");
        }
        return $output;
    }
}
