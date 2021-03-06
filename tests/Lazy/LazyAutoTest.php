<?php
declare(strict_types=1);

namespace Capsule\Di\Lazy;

use Capsule\Di\Factory;
use Capsule\Di\Registry;

class LazyAutoTest extends \PHPUnit\Framework\TestCase
{
    public function test__debugInfo()
    {
        $registry = new Registry();

        $auto = new LazyAuto(
            $registry,
            new Factory($registry),
            'foo'
        );

        $expect = ['spec' => 'foo'];
        $actual = $auto->__debugInfo();
        $this->assertSame($expect, $actual);
    }
}
