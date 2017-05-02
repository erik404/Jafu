<?php

abstract class PHPUnit_Framework_Assert
{
    public static function assertTrue($condition = true, $message = '')
    {
        self::assertThat($condition, self::isTrue(), $message);
    }
}
