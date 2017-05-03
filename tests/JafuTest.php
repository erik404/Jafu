<?php
use PHPUnit\Framework\TestCase;

require '../src/erik404/Jafu.php';

/**
 * @covers Jafu
 */
final class JafuTest extends TestCase
{
    const CONFIG_DIST_FILE = '../src/erik404/config.dist.php';

    private $configFile;
    private $JafuTest;

    /**
     * Test config.dist.php
     */
    public function testConfigDist()
    {
        // assert if the config.dist.php file exists
        $this->assertFileExists(JafuTest::CONFIG_DIST_FILE);
        // load the config file
        $configFile = require JafuTest::CONFIG_DIST_FILE;
        // assert if the $configFile holds an object
        $this->assertEquals(true, gettype($configFile) === 'object', "config.dist.php should translate to object.");
        // assert if defaultSaveLocation holds a string
        $this->assertEquals(true, gettype($configFile->defaultSaveLocation) === 'string', "config.dist.php defaultSaveLocation should hold string.");
        // assert if responseMessages holds an array
        $this->assertEquals(true, gettype($configFile->responseMessages) === 'array', "config.dist.php responseMessages should hold array.");
        // passed tests, set $configFile to class scope
        $this->configFile = $configFile;
    }

}
