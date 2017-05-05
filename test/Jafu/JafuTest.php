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
     * Simulate the setting of $_FILES with single upload and validates the return data.
     *
     * @depends testClassCanBeConstructed
     */
    public function testSetGetFilesSingle()
    {
        $jafu = new \erik404\Jafu(require JafuTest::CONFIG_DIST_FILE);

        $test['foo'] = [
            'name'     => 'nameTest',
            'type'     => 'typeTest',
            'tmp_name' => 'tmpNameTest',
            'error'    => 0,
            'size'     => 'sizeTest'
        ];

        $jafu->setFiles($test);
        $this->assertCount(1, $jafu->getFiles());
        $this->assertEquals('nameTest', $jafu->getFiles()[0]->name);
        $this->assertEquals('typeTest', $jafu->getFiles()[0]->type);
        $this->assertEquals('tmpNameTest', $jafu->getFiles()[0]->tmpName);
        $this->assertEquals(0, $jafu->getFiles()[0]->error);
        $this->assertEquals('sizeTest', $jafu->getFiles()[0]->size);
    }

    /**
     * Simulate the setting of $_FILES with multiple uploads and validates the return data.
     *
     * @depends testClassCanBeConstructed
     */
    public function testSetGetFilesMultiple()
    {
        $jafu = new \erik404\Jafu(require JafuTest::CONFIG_DIST_FILE);

        $test = [
            'foo' => [
                'name'     => 'nameTestFoo',
                'type'     => 'typeTestFoo',
                'tmp_name' => 'tmpNameTestFoo',
                'error'    => 0,
                'size'     => 'sizeTestFoo'
            ],
            'bar' => [
                'name'     => 'nameTestBar',
                'type'     => 'typeTestBar',
                'tmp_name' => 'tmpNameTestBar',
                'error'    => 0,
                'size'     => 'sizeTestBar'
            ],
        ];

        $jafu->setFiles($test);
        $this->assertCount(2, $jafu->getFiles());
        // foo
        $this->assertEquals('nameTestFoo', $jafu->getFiles()[0]->name);
        $this->assertEquals('typeTestFoo', $jafu->getFiles()[0]->type);
        $this->assertEquals('tmpNameTestFoo', $jafu->getFiles()[0]->tmpName);
        $this->assertEquals(0, $jafu->getFiles()[0]->error);
        $this->assertEquals('sizeTestFoo', $jafu->getFiles()[0]->size);
        // bar
        $this->assertEquals('nameTestBar', $jafu->getFiles()[1]->name);
        $this->assertEquals('typeTestBar', $jafu->getFiles()[1]->type);
        $this->assertEquals('tmpNameTestBar', $jafu->getFiles()[1]->tmpName);
        $this->assertEquals(0, $jafu->getFiles()[1]->error);
        $this->assertEquals('sizeTestBar', $jafu->getFiles()[1]->size);
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
        $this->assertEquals($this->uploadErrorCode, $jafu->getErrors()[0]['error']);
    }

    /**
     * @depends testClassCanBeConstructed
     * @depends testMimeTypeConstants
     */
    public function testSaveSingle()
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
            ]
        ];
        $jafu->setSaveLocation(__DIR__.'/');
        $jafu->setFiles($_FILES);
        $jafu->save();

        foreach($jafu->getResults() as $result) {
            $this->assertTrue(file_exists($result['file']));
            unlink($result['file']);
        }
    }

    /**
     * @depends testClassCanBeConstructed
     * @depends testMimeTypeConstants
     */
    public function testSaveMultiple()
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
        $jafu->setSaveLocation(__DIR__.'/');
        $jafu->setFiles($_FILES);
        $jafu->save();

        foreach($jafu->getResults() as $result) {
            $this->assertTrue(file_exists($result['file']));
            unlink($result['file']);
        }
    }
}
