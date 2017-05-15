<?php

use PHPUnit\Framework\TestCase;

/**
 * @covers Jafu
 */
final class JafuTest extends TestCase
{
    const CONFIG_DIST_FILE = __DIR__ . '/../../src/erik404/config.dist.php';

    protected $noFileUploadedCode     = 4;
    protected $mimeTypeNotAllowedCode = 9;
    protected $uploadErrorCode        = 2;

    public function testClassCanBeConstructed()
    {
        $this->assertFileExists(JafuTest::CONFIG_DIST_FILE);
        $this->assertInstanceOf(\erik404\Jafu::class, new \erik404\Jafu(require JafuTest::CONFIG_DIST_FILE));
    }

    /**
     * @depends testClassCanBeConstructed
     */
    public function testSetGetSaveLocation()
    {
        $jafu = new \erik404\Jafu(require JafuTest::CONFIG_DIST_FILE);
        $jafu->setSaveLocation(__DIR__);
        $this->assertEquals(__DIR__, $jafu->getSaveLocation());
    }

    /**
     * @depends testClassCanBeConstructed
     */
    public function testGetDefaultSaveLocation()
    {
        $jafu = new \erik404\Jafu(require JafuTest::CONFIG_DIST_FILE);
        $this->assertInternalType('string', $jafu->getDefaultSaveLocation());
    }

    /**
     * @depends testClassCanBeConstructed
     */
    public function testSetGetMaxSize()
    {
        $jafu = new \erik404\Jafu(require JafuTest::CONFIG_DIST_FILE);
        $jafu->setMaxSize(100);
        $this->assertEquals(100, $jafu->getMaxSize());
    }

    /**
     * @depends testClassCanBeConstructed
     */
    public function testMimeTypeConstants()
    {
        $jafu = new \erik404\Jafu(require JafuTest::CONFIG_DIST_FILE);
        $this->assertInternalType('array', $jafu::APPLICATION_TYPES);
        $this->assertInternalType('array', $jafu::AUDIO_TYPES);
        $this->assertInternalType('array', $jafu::IMAGE_TYPES);
        $this->assertInternalType('array', $jafu::TEXT_TYPES);
        $this->assertInternalType('array', $jafu::VIDEO_TYPES);
    }

    /**
     * Simulate the setting of multiple allowed types using both class constants and extra arrays and validates the return data.
     *
     * @depends testClassCanBeConstructed
     * @depends testMimeTypeConstants
     */
    public function testSetGetAllowedMimeTypes()
    {
        $jafu      = new \erik404\Jafu(require JafuTest::CONFIG_DIST_FILE);
        $testArray = [
            'test/type', 'more/types'
        ];

        $jafu->setAllowedMimeTypes(
            $jafu::VIDEO_TYPES,
            $jafu::TEXT_TYPES,
            $jafu::APPLICATION_TYPES,
            $jafu::AUDIO_TYPES,
            $jafu::IMAGE_TYPES,
            $testArray
        );

        $expectedResult = array_merge(
            $jafu::VIDEO_TYPES,
            $jafu::TEXT_TYPES,
            $jafu::APPLICATION_TYPES,
            $jafu::AUDIO_TYPES,
            $jafu::IMAGE_TYPES,
            $testArray
        );

        $expectedResultCount = count($expectedResult);
        $this->assertCount($expectedResultCount, $jafu->getAllowedMimeTypes());
        for ($i = 0; $i < $expectedResultCount; $i++) {
            $this->assertEquals($expectedResult[$i], $jafu->getAllowedMimeTypes()[$i]);
        }
        $this->assertEquals($expectedResultCount, $i);
    }

    /**
     * @depends testClassCanBeConstructed
     * @depends testMimeTypeConstants
     */
    public function testErrorNoUploads()
    {
        $jafu = new \erik404\Jafu(require JafuTest::CONFIG_DIST_FILE);
        $jafu->setFiles([]);
        $jafu->save();

        $this->assertInternalType('array', $jafu->getErrors());
        $this->assertCount(1, $jafu->getErrors());
        $this->assertEquals($this->noFileUploadedCode, $jafu->getErrors()[0]['error']);
    }

    /**
     * @depends testClassCanBeConstructed
     * @depends testMimeTypeConstants
     */
    public function testErrorRestrictedType()
    {
        $jafu = new \erik404\Jafu(require JafuTest::CONFIG_DIST_FILE);
        $jafu->setAllowedMimeTypes($jafu::APPLICATION_TYPES);
        $_FILES = [
            'foo' => [
                'name'     => 'test-example.jpeg',
                'type'     => 'image/jpeg',
                'size'     => 542,
                'tmp_name' => __DIR__ . '/_files/example.png',
                'error'    => 0
            ]
        ];

        $jafu->setFiles($_FILES);
        $jafu->save();
        $this->assertInternalType('array', $jafu->getErrors());
        $this->assertCount(1, $jafu->getErrors());
        $this->assertEquals($this->mimeTypeNotAllowedCode, $jafu->getErrors()[0]['error']);
    }

    /**
     * @depends testClassCanBeConstructed
     * @depends testMimeTypeConstants
     */
    public function testErrorUploadError()
    {
        $jafu = new \erik404\Jafu(require JafuTest::CONFIG_DIST_FILE);
        $jafu->setAllowedMimeTypes($jafu::APPLICATION_TYPES);
        $_FILES = [
            'foo' => [
                'name'     => 'test-example.jpeg',
                'type'     => 'image/jpeg',
                'size'     => 542,
                'tmp_name' => __DIR__ . '/_files/example.png',
                'error'    => 2
            ]
        ];

        $jafu->setFiles($_FILES);
        $jafu->save();
        $this->assertInternalType('array', $jafu->getErrors());
        $this->assertCount(1, $jafu->getErrors());
        $this->assertEquals($this->uploadErrorCode, $jafu->getErrors()[0]['error']);
    }

    /**
     * @depends testClassCanBeConstructed
     * @depends testMimeTypeConstants
     */
    public function testSave()
    {
        $jafu = new \erik404\Jafu(require JafuTest::CONFIG_DIST_FILE);
        $jafu->setAllowedMimeTypes($jafu::IMAGE_TYPES);
        $_FILES = [
            'foo' => [
                'name'     => 'test-example.jpeg',
                'type'     => 'image/jpeg',
                'size'     => 542,
                'tmp_name' => __DIR__ . '/_files/example.png',
                'error'    => 0
            ],
            [
                'name'     => 'test-example.jpeg',
                'type'     => 'image/jpeg',
                'size'     => 542,
                'tmp_name' => __DIR__ . '/_files/example.png',
                'error'    => 0
            ],
            [
                'name'     => 'test-example.jpeg',
                'type'     => 'image/jpeg',
                'size'     => 542,
                'tmp_name' => __DIR__ . '/_files/example.png',
                'error'    => 0
            ],

        ];
        $jafu->setSaveLocation(__DIR__ . '/');
        $jafu->setFiles($_FILES);
        $jafu->save();

        $this->assertCount(3, $jafu->getResults());
        foreach ($jafu->getResults() as $result) {
            $this->assertTrue(file_exists($result['file']));
            unlink($result['file']);
        }
    }
}
