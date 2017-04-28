<?php
/**
 * Just Another File Uploader
 * Erik-Jan van de Wal, MIT License 2017.
 */

namespace erik404;

class Jafu
{
    /**
     * see: https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types
     */
    const APPLICATION_TYPES = array('application/octet-stream', 'application/pkcs12', 'application/vnd.mspowerpoint', 'application/xhtml+xml', 'application/xml', 'application/pdf', 'application/msword');
    const AUDIO_TYPES = array('audio/midi', 'audio/mpeg', 'audio/webm', 'audio/ogg', 'audio/wav');
    const TEXT_TYPES = array('text/plain', 'text/html', 'text/css', 'text/javascript');
    const IMAGE_TYPES = array('image/gif', 'image/png', 'image/jpeg', 'image/bmp');
    const VIDEO_TYPES = array('video/webm', 'video/ogg');

    /**
     * Holds the config object
     * @var object
     */
    protected $config;

    /**
     * @param object $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Holds the allowed file types which will be asserted
     * @var array
     */
    protected $allowedFileTypes = array();

    /**
     * @return array
     */
    public function getAllowedFileTypes()
    {
        return $this->allowedFileTypes;
    }

    /**
     * Expects (multiple) one-dimensional arrays holding the mime-types which are allowed for upload.
     * @param \array[] ...$allowedFileTypes
     */
    public function setAllowedFileTypes(Array ...$allowedFileTypes)
    {
        foreach ($allowedFileTypes as $allowedFileTypesArray) {
            $this->allowedFileTypes = array_merge($this->allowedFileTypes, $allowedFileTypesArray);
        }
    }

    /**
     * Expects the $_FILES array
     * @var
     */
    protected $files;

    /**
     * Expects the $_FILES array and normalizes this to a more sane structure.
     * @param mixed $files
     */
    public function setFiles($files)
    {
        $this->files = $this->normalize($files);
        print_r($this->files);
    }

    /**
     * Normalizes the information stored in $_FILES to represent a less insane structure.
     * see: http://php.net/manual/en/features.file-upload.post-method.php
     *
     * @param $files ($_FILES)
     * @return array
     */
    private function normalize($files)
    {
        $filesNormalized = array();
        foreach ($files as $key => $value) {
            if (gettype($files[$key]['name']) === 'array') {
                for ($i = 0; $i < count($files[$key]['name']); $i++) {
                    // assumption is the root of all evil. todo: check if the structure can differ. (also, what happens when there are multiple uploads and some are not used)
                    if ($files[$key]['error'][$i] !== 4) { // 4 means no file uploaded; assume for now that multiple upload inputs where used and some not used
                        $filesNormalized[] = array(
                            'name' => $files[$key]['name'][$i],
                            'type' => $files[$key]['type'][$i],
                            'tmp_name' => $files[$key]['tmp_name'][$i],
                            'error' => $files[$key]['error'][$i],
                            'size' => $files[$key]['size'][$i],
                        );
                    }
                }
            } else {
                if ($files[$key]['error'] !== 4) { // 4 means no file uploaded; assume for now that multiple upload inputs where used and some not used
                    $filesNormalized[] = array(
                        'name' => $files[$key]['name'],
                        'type' => $files[$key]['type'],
                        'tmp_name' => $files[$key]['tmp_name'],
                        'error' => $files[$key]['error'],
                        'size' => $files[$key]['size'],
                    );
                }
            }
        }
        return $filesNormalized;
    }

    /**
     * Jafu constructor.
     */
    function __construct()
    {
        try {
            $this->setConfig(require('config.php'));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

}