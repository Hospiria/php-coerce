<?php

use MadisonSolutions\Coerce\Coerce;
use PHPUnit\Framework\TestCase;

class CoerceTest extends TestCase
{
    // Helper function which attempts to return helpful information about the
    // type and value of a variable as concisely as possible for debugging.
    protected function debugval($val)
    {
        if (is_string($val)) {
            if (strlen($val) > 20) {
                $val = substr($val, 0, 20) . '...';
            }
            return "'{$val}'";
        } elseif (is_null($val)) {
            return 'NULL';
        } elseif (is_bool($val)) {
            return $val ? 'TRUE' : 'FALSE';
        } elseif (is_int($val)) {
            return (string) $val;
        } elseif (is_float($val)) {
            if (is_nan($val)) {
                return 'NAN';
            } elseif (is_infinite($val)) {
                return ($val < 0 ? '-INF' : 'INF');
            } else {
                return (string) $val;
            }
        } elseif (is_array($val)) {
            $out = [];
            foreach ($val as $key => $subval) {
                $out[] = $this->debugval($key) . ': ' . $this->debugval($subval);
            }
            $out = implode(', ', $out);
            if (strlen($out) > 20) {
                $out = substr($out, 0, 20) . '...';
            }
            return '{' . $out . '}';
        } elseif (is_object($val)) {
            return get_class($val) . ' ' . (method_exists($val, '__toString') ? $val->__toString() : '-obj-');
        } else {
            throw new \Exception("Unexpected type " . gettype($val));
        }
    }

    protected function assertCoersionsSucceeds($method, $tests)
    {
        foreach ($tests as $test) {
            $result = Coerce::$method($test[0], $output);
            $this->assertSame(true, $result, "Could not coerce value " . $this->debugval($test[0]) . " using method {$method}");
            $this->assertSame($test[1], $output);
            $method_or_fail = "{$method}OrFail";
            $this->assertSame($test[1], Coerce::$method_or_fail($test[0]));
        }
    }

    protected function assertCoersionsFails($method, $inputs)
    {
        foreach ($inputs as $input) {
            $result = Coerce::$method($input, $output);
            $this->assertSame(false, $result, "Should not have been able to coerce value " . $this->debugval($input) . " using method {$method} but was coerced to value " . $this->debugval($output));
            $method_or_fail = "{$method}OrFail";
            $thrown = false;
            try {
                $output = Coerce::$method_or_fail($input);
            } catch (InvalidArgumentException $e) {
                $thrown = true;
            }
            $this->assertTrue($thrown, "InvalidArgumentException should have been thrown attempting to coerce value " . $this->debugval($input) . " using method {$method_or_fail} but was coerced to value " . $this->debugval($output));
        }
    }

    public function testCoerceToString()
    {
        $foo = new class() {
            public function __toString() {
                return 'foo';
            }
        };

        $this->assertCoersionsSucceeds('toString', [
            [null, ''],
            ['', ''],
            ['foo', 'foo'],
            [0 ,'0'],
            [2, '2'],
            [2.5, '2.5'],
            [NAN, 'NAN'],
            [true, 'true'],
            [false, 'false'],
            [$foo,  'foo'],
        ]);

        $this->assertCoersionsFails('toString', [
            [],
            new \stdClass(),
        ]);
    }

    public function testCoerceToInt()
    {
        $this->assertCoersionsSucceeds('toInt', [
            [0 , 0],
            [2, 2],
            [2.0, 2],
            [true, 1],
            [false, 0],
            ['0', 0],
            ['2.0', 2],
        ]);

        $this->assertCoersionsFails('toInt', [
            null,
            2.5,
            NAN,
            INF,
            '',
            'foo',
            '2.5',
            [],
            new \stdClass(),
        ]);
    }

    public function testCoerceToFloat()
    {
        $this->assertCoersionsSucceeds('toFloat', [
            [0, 0.0],
            [2, 2.0],
            [2.0, 2.0],
            [true, 1.0],
            [false, 0.0],
            [2.5, 2.5],
            ['2.5', 2.5],
        ]);

        $this->assertCoersionsFails('toFloat', [
            null,
            NAN,
            INF,
            '',
            'foo',
            [],
            new \stdClass(),
        ]);
    }

    public function testCoerceToBool()
    {
        $this->assertCoersionsSucceeds('toBool', [
            [0, false],
            [1, true],
            [0.0, false],
            [1.0, true],
            [true, true],
            [false, false],
            ['t', true],
            ['TRUE', true],
            ['no', false],
        ]);

        $this->assertCoersionsFails('toBool', [
            null,
            NAN,
            INF,
            '',
            'foo',
            '2.5',
            [],
            new \stdClass(),
        ]);
    }

    public function testCoerceToArrayKey()
    {
        $this->assertCoersionsSucceeds('toArrayKey', [
            [0, 0],
            ['foo', 'foo'],
            ['', ''],
            [1.0, 1],
            [2.5, '2.5'],
            ['1', 1],
            ['1.0', 1],
            ['2.5', '2.5'],
        ]);

        $this->assertCoersionsFails('toArrayKey', [
            null,
            NAN,
            INF,
            true,
            [],
            new \stdClass(),
        ]);
    }
}
