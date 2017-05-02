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
    const ERROR_MESSAGES = array(
        0 => 'There is no error, the file uploaded with success',
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk.',
        8 => 'A PHP extension stopped the file upload.',
    );

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
     * Holds the errors if any
     * @var array
     */
    protected $errors = array();

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
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
     * Holds a normalized array containing the information passed with the $_FILES array.
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
    }

    /**
     * Holds the location of where the file must be saved
     * @var
     */
    protected $saveLocation;

    /**
     * @return mixed
     */
    public function getSaveLocation()
    {
        return $this->saveLocation;
    }

    /**
     * @param mixed $saveLocation
     */
    public function setSaveLocation($saveLocation)
    {
        $this->saveLocation = $saveLocation;
    }

    /**
     * Jafu constructor.
     */
    function __construct()
    {
        try {
            $this->setConfig(require('config.php'));
            $this->setSaveLocation($this->config->defaultSaveLocation);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Checks if there are upload errors, if not saves the files to the filedisk
     * @return bool
     */
    public function save()
    {
        if ($this->checkForErrors()) {
            return false; // there was an error with (one of) the fileupload(s)
        }

        if (!$this->checkIfUploadedFilesAreAllowed()) {
            return false; // the MIME type is not allowed
        }

        $this->saveFilesToFiledisk();

        return true;
    }

    /**
     * Check the array with files for errors (ignoring errorCode 4 which means no file uploaded)
     * @return bool
     */
    private function checkForErrors()
    {
        foreach ($this->files as $file) {
            if ( (int) $file['error'] !== 0 && (int) $file['error'] !== 4) { // ignore no file uploaded for now because of multiple upload forms
                $this->errors[] = array($file['name'] => Jafu::ERROR_MESSAGES[$file['error']]);
            }
        }
        return (!empty($this->errors));
    }

    /**
     * Checks if the files MIME type is according to $this->allowedFileTypes
     * @return bool
     */
    private function checkIfUploadedFilesAreAllowed()
    {
        foreach($this->files as $file) {
            if (!in_array($file['type'], $this->allowedFileTypes)) {
                $this->errors[] = array($file['name'] => 'File type ' . $file['type'] . ' not allowed.');
            }
        }
        return (!empty($this->errors));
    }

    /**
     *
     */
    private function saveFilesToFiledisk()
    {
        foreach($this->files as $file) {

            // get the file save location
            // get the name
            // create sanitized name
            // save to disk
            // remove tmp file
        }
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
}