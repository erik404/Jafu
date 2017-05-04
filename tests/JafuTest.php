<?php
use PHPUnit\Framework\TestCase;

require 'src/erik404/Jafu.php';

/**
 * @covers Jafu
 */
final class JafuTest extends TestCase
{
    const CONFIG_DIST_FILE = 'src/erik404/config.dist.php';

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
        // assert if config object has defaultSaveLocation of type string
        $this->assertObjectHasAttribute('defaultSaveLocation', $configFile, 'config object should have "defaultSaveLocation" attribute.');
        $this->assertEquals(true, gettype($configFile->defaultSaveLocation) === 'string', "config->defaultSaveLocation should hold string.");
        // assert if config object has responseMessages of type array
        $this->assertObjectHasAttribute('responseMessages', $configFile, 'config object should have "responseMessages" attribute.');
        $this->assertEquals(true, gettype($configFile->responseMessages) === 'array', "config->responseMessages should hold array.");
    }

    /**
     * Test if the class can be instantiated and is instance of erik404\Jafu
     *
     * @depends testConfigDist
     */
    public function testJafuClass()
    {
        $jafu = new \erik404\Jafu(require JafuTest::CONFIG_DIST_FILE);
        $this->assertInstanceOf(\erik404\Jafu::class, $jafu);
    }

    /**
     * Test the getDefaultSaveLocation method
     *
     * @depends testJafuClass
     */
    public function testIfDefaultSaveLocationCanBeRetrieved()
    {
        $jafu = new \erik404\Jafu(require JafuTest::CONFIG_DIST_FILE);
        $this->assertEquals(true, gettype($jafu->getDefaultSaveLocation()) === 'string');
    }


}
