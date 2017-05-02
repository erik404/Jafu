<?php
require_once __DIR__ . '/vendor/autoload.php'; // Autoload files using Composer autoload

use PHPUnit\Framework\TestCase;

abstract class PHPUnit_Framework_Assert
{
    public static function assertTrue($condition = true, $message = '')
    {
        self::assertThat($condition, self::isTrue(), $message);
    }
}
