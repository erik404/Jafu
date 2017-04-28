<?php

/**
 * Just Another File Uploader
 * Erik-Jan van de Wal, MIT License 2017.
 */

namespace erik404;

class Jafu
{
    /**
     * Expects the temporary saved file on the filesystem
     * @var $file
     */
    protected $file;

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param $file
     * @return void
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @var mixed
     */
    protected $config;

    /**
     * @return object
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(Array $config)
    {
        $this->config = (object)$config;
    }

    /**
     * Jafu constructor.
     */
    function __construct()
    {
        try {
            $this->setConfig(include('config.php'));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function assertMimeType()
    {
        var_dump($this->getConfig()->allowed_image_types);
    }

}