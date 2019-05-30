<?php
namespace MadisonSolutions\Coerce;

use InvalidArgumentException;

class Coerce
{
    /**
     * Coerce a value to string type
     *
     * Notes:
     * A null value is coerced to the empty string
     * Boolean values are coerced to strings 'true' or 'false'
     * Integers and floats are coerced to their standard string representation
     * No attempt is made to coerce array inputs to a string - the function will return false
     * Objects will be coerced if and only if, they have a defined __toString() method
     * IE the creater of the class has a specific string representation explicity defined
     *
     * @param mixed $input The input value
     * @param mixed &$output If coersion is successful, the string value will be written to the $output variable
     * @return bool True if $input was successfully coerced to a string, false otherwise
     */
    public static function toString($input, &$output) : bool
    {
        if (is_null($input)) {
            $output = '';
            return true;
        }
        if (is_bool($input)) {
            $output = $input ? 'true' : 'false';
            return true;
        }
        if (is_scalar($input)) {
            $output = (string) $input;
            return true;
        }
        if (is_object($input) && method_exists($input, '__toString')) {
            $output = $input->__toString();
            return true;
        }
        $output = null;
        return false;
    }

    /**
     * Coerce a value to string type
     *
     * @param mixed $input The input value
     * @return string The coerced value
     * @throws InvalidArgumentException If the input value cannot be coerced to a string
     */
    public static function toStringOrFail($input) : string
    {
        if (! Coerce::toString($input, $output)) {
            throw new InvalidArgumentException("Unable to coerce value to string");
        }
        return $output;
    }

    /**
     * Coerce a value to integer type
     *
     * Notes
     * Null values, arrays and objects will not be coerced - the function will return false
     * Boolean true will be coerced to 1, and false to 0
     * If the input value is the float or string representation of an integer, then the value will be coerced,
     * For example the float 4.0, the string '4.0' or the string '4' would all be coerced to the integer 4
     * Any representation of a non-integer number will fail coersion - in particular numbers are never rounded to the nearest integer.
     *
     * @param mixed $input The input value
     * @param mixed &$output If coersion is successful, the integer value will be written to the $output variable
     * @return bool True if $input was successfully coerced to an integer, false otherwise
     */
    public static function toInt($input, &$output) : bool
    {
        if (is_int($input)) {
            $output = $input;
            return true;
        }
        if (is_bool($input)) {
            $output = $input ? 1 : 0;
            return true;
        }
        if (is_string($input) && is_numeric($input)) {
            $input = (float) $input;
        }
        if (is_float($input) && is_finite($input) && intval($input) == $input) {
            $output = intval($input);
            return true;
        }
        $output = null;
        return false;
    }

    /**
     * Coerce a value to integer type
     *
     * @param mixed $input The input value
     * @return int The coerced value
     * @throws InvalidArgumentException If the input value cannot be coerced to an integer
     */
    public static function toIntOrFail($input) : int
    {
        if (! Coerce::toInt($input, $output)) {
            throw new InvalidArgumentException("Unable to coerce value to integer");
        }
        return $output;
    }

    /**
     * Coerce a value to float type
     *
     * Notes
     * Null values, arrays and objects will not be coerced - the function will return false
     * Boolean true will be coerced to 1.0, and false to 0.0
     * Numeric strings will be coerced to their float values
     * If the input is technically of float type, but is non-finite (eg NAN or INF),
     * then the value will not be coerced and the function will return false.
     *
     * @param mixed $input The input value
     * @param mixed &$output If coersion is successful, the float value will be written to the $output variable
     * @return bool True if $input was successfully coerced to a float, false otherwise
     */
    public static function toFloat($input, &$output) : bool
    {
        if (is_float($input)) {
            if (is_finite($input)) {
                $output = $input;
                return true;
            } else {
                $output = null;
                return false;
            }
        }
        if (is_int($input)) {
            $output = floatval($input);
            return true;
        }
        if (is_bool($input)) {
            $output = floatval($input ? 1 : 0);
            return true;
        }
        if (is_string($input) && is_numeric($input)) {
            $output = floatval($input);
            return true;
        }
        $output = null;
        return false;
    }

    /**
     * Coerce a value to float type
     *
     * @param mixed $input The input value
     * @return float The coerced value
     * @throws InvalidArgumentException If the input value cannot be coerced to a float
     */
    public static function toFloatOrFail($input) : float
    {
        if (! Coerce::toFloat($input, $output)) {
            throw new InvalidArgumentException("Unable to coerce value to float");
        }
        return $output;
    }

    /**
     * Coerce a value to type suitable for use as a key in a PHP array (string or int)
     *
     * Notes:
     * Null values, arrays and objects will not be coerced - the function will return false.
     * Boolean values will not be coerced, due to an ambiguity whether to use a string ('true', 'false') or an integer (1, 0) as the key.
     * Strings or floats representing an integer will be coerced to integer.
     * Floats representing non-integer values will be converted to strings for use as an array key.
     * Integers or any string which does not represent an integer will be returned unmodified for use an an array key.
     *
     * @param mixed $input The input value
     * @param mixed &$output If coersion is successful, the coerced string or integer value will be written to the $output variable
     * @return bool True if $input was successfully coerced to an array key value, false otherwise
     */
    public static function toArrayKey($input, &$output) : bool
    {
        if (is_null($input) || is_bool($input)) {
            $output = null;
            return false;
        }
        if (is_float($input) && ! is_finite($input)) {
            $output = null;
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
        $output = null;
        return false;
    }

    /**
     * Coerce a value to a type suitable for use as an array key (string or int)
     *
     * @param mixed $input The input value
     * @return mixed The coerced value
     * @throws InvalidArgumentException If the input value cannot be coerced to an array key value
     */
    public static function toArrayKeyOrFail($input)
    {
        if (! Coerce::toArrayKey($input, $output)) {
            throw new InvalidArgumentException("Unable to coerce value to array key");
        }
        return $output;
    }

    /**
     * Coerce a value to boolean type
     *
     * Notes:
     * Null values, arrays and objects will not be coerced - the function will return false
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
     */
    public static function toBool($input, &$output) : bool
    {
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
                $output = null;
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
                    $output = null;
                    return false;
            }
        }
        $output = null;
        return false;
    }

    /**
     * Coerce a value to a boolean
     *
     * @param mixed $input The input value
     * @return bool The coerced value
     * @throws InvalidArgumentException If the input value cannot be coerced to a boolean
     */
    public static function toBoolOrFail($input) : bool
    {
        if (! Coerce::toBool($input, $output)) {
            throw new InvalidArgumentException("Unable to coerce value to boolean");
        }
        return $output;
    }
}
