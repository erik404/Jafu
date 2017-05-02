<?php
/**
 * v1.0
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
    const AUDIO_TYPES       = array('audio/midi', 'audio/mpeg', 'audio/webm', 'audio/ogg', 'audio/wav');
    const TEXT_TYPES        = array('text/plain', 'text/html', 'text/css', 'text/javascript');
    const IMAGE_TYPES       = array('image/gif', 'image/png', 'image/jpeg', 'image/bmp');
    const VIDEO_TYPES       = array('video/webm', 'video/ogg');

    /**
     * @var int
     */
    protected $mimeValidationThruFinfoFailedCode = 10;
    protected $mimeTypeNotAllowedResponseCode    = 9;
    protected $noFileUploadedResponseCode        = 4;
    protected $fileUploadedResponseCode          = 0;

    /**
     * Holds the config object
     *
     * @var object
     */
    protected $config;

    /**
     * Holds the errors array
     *
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
     * Holds the allowed file types array
     *
     * @var array
     */
    protected $allowedMimeTypes = array();

    /**
     * Expects (multiple) one-dimensional arrays holding the mime-types which are allowed for upload.
     *
     * @param \array[] ...$allowedMimeTypes
     * @return void
     */
    public function setAllowedMimeTypes(Array ...$allowedMimeTypes)
    {
        foreach ($allowedMimeTypes as $allowedFileTypesArray) {
            $this->allowedMimeTypes = array_merge($this->allowedMimeTypes, $allowedFileTypesArray);
        }
    }

    /**
     * Holds a normalized array containing the information passed with the $_FILES array.
     *
     * @var array
     */
    protected $files;

    /**
     * Expects the $_FILES array and normalizes this to a more sane structure.
     *
     * @param mixed $files
     * @return void
     */
    public function setFiles($files)
    {
        // todo validate if we truly are given the $_FILES array
        $this->files = $this->normalize($files);
    }

    /**
     * Holds the directory where the file must be saved
     *
     * @var
     */
    protected $saveLocation;

    /**
     * @param mixed $saveLocation
     * @throws \Exception
     * @return void
     */
    public function setSaveLocation($saveLocation)
    {
        if (!is_writable($saveLocation)) {
            throw new \Exception('Directory ' . $saveLocation . ' not writable.');
        }
        $this->saveLocation = $saveLocation;
    }

    /**
     * Returns the default save location from the config file
     *
     * @return mixed
     */
    public function getDefaultSaveLocation()
    {
        return $this->config->defaultSaveLocation;
    }

    /**
     * Holds the files which are successfully uploaded
     *
     * @var array
     */
    protected $results = array();

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Jafu constructor.
     *
     * @throws \Exception
     */
    function __construct()
    {
        if (!file_exists('config.php')) {
            throw new \Exception('The file config.php can not be found. Did you forgot to rename the config.php.dist in src/erik404/ to config.php?');
        }
        $this->config = require('config.php');
        $this->setSaveLocation($this->config->defaultSaveLocation);
    }

    /**
     * Performs the validation and save operation
     *
     * @return bool
     */
    public function save()
    {
        if ($this->checkForErrors()) {
            return false; // there was an error with (one of) the file-upload(s)
        }
        if ($this->checkIfMimeTypeIsRestricted()) {
            return false; // the MIME type of (one of) the file(s) is not allowed
        }

        // do the actual saving.
        $this->saveFilesToFilesystem();

        return true;
    }

    /**
     * Check the uploaded files for errors
     *
     * @return bool
     */
    private function checkForErrors()
    {
        // check if there are uploaded files, if not, set the right error response
        if (empty($this->files)) {
            $this->errors[] = array(
                'name'      => null,
                'inputName' => null,
                'error'     => $this->noFileUploadedResponseCode,
                'message'   => $this->config->responseMessages[$this->noFileUploadedResponseCode]
            );
        } else {
            // loop through all uploaded files and set the according error response if there are any
            foreach ($this->files as $file) {
                if ($file->error !== $this->fileUploadedResponseCode) {
                    $this->errors[] = array(
                        'name'      => $file->name,
                        'inputName' => $file->inputName,
                        'error'     => $file->error,
                        'message'   => $this->config->responseMessages[$file->error]
                    );
                }
            }
        }

        return (!empty($this->errors)); // returns false if there are no errors
    }

    /**
     * Checks if the files MIME type is allowed or not
     *
     * @return bool
     */
    private function checkIfMimeTypeIsRestricted()
    {
        foreach ($this->files as $file) {
            // fetches the files MIME-type using PHP's finfo
            // see: http://php.net/manual/en/class.finfo.php
            $finfo    = new \finfo(FILEINFO_MIME);
            $mimeType = $finfo->file($file->tmpName);
            $mimeType = $mimeType === false ? false : explode(';', $mimeType)[0];

            // check the fetched MIME-type against the allowedMimeTypes array
            if (!$mimeType || !in_array($mimeType, $this->allowedMimeTypes)) {
                $error = array(
                    'name'      => $file->name,
                    'inputName' => $file->inputName
                );
                if (!$mimeType) {
                    $error['error']   = $this->mimeValidationThruFinfoFailedCode;
                    $error['message'] = $this->config->responseMessages[$this->mimeValidationThruFinfoFailedCode];
                } else {
                    $error['error']   = $this->mimeTypeNotAllowedResponseCode;
                    $error['message'] = str_replace('%s', $mimeType, $this->config->responseMessages[$this->mimeTypeNotAllowedResponseCode]);
                }
                $this->errors[] = $error;
            }
        }

        return (!empty($this->errors)); // returns false if there are no errors
    }

    /**
     * Creates an unique name per file and saves it to the filesystem. On success the new file is stored in the result array together with the name of the input from which the upload originates from
     *
     * @return void
     * @throws \Exception
     */
    private function saveFilesToFilesystem()
    {
        foreach ($this->files as $file) {
            // create an unique name, validate if this is truly unique and saves it to disk
            $target     = null;
            $fileExists = true;
            while ($fileExists) {
                $target     = $this->saveLocation . str_replace('.', '', uniqid(time(), true)) . '_' . basename($file->name);
                $fileExists = file_exists($target);
            }
            if (!move_uploaded_file($file->tmpName, $target)) {
                // throws an exception because this is a error the user can't fix. todo rollback earlier saved files
                throw new \Exception('The file ' . $target . ' could not be saved to the filesystem.');
            } else {
                // add saved file to result array
                $this->results[] = array(
                    'file'      => $target,
                    'inputName' => $file->inputName
                );
            }
        }
    }

    /**
     * Structure the information stored in $_FILES so Jafu can handle both single and multiple file-uploads.
     * Ignore error code 4 (noFileUploadedResponseCode). This enables optional multiple file-uploads.
     *
     * @param $files ($_FILES)
     * @return array holding the uploaded file information in objects
     */
    private function normalize($files)
    {
        $filesNormalized = array();
        foreach ($files as $key => $value) {
            if (gettype($files[$key]['name']) === 'array') {
                for ($i = 0; $i < count($files[$key]['name']); $i++) {
                    if ($files[$key]['error'][$i] !== $this->noFileUploadedResponseCode) {
                        $filesNormalized[] = (object)array(
                            'name'      => $files[$key]['name'][$i],
                            'type'      => $files[$key]['type'][$i],
                            'tmpName'   => $files[$key]['tmp_name'][$i],
                            'error'     => (int)$files[$key]['error'][$i],
                            'size'      => $files[$key]['size'][$i],
                            'inputName' => $key
                        );
                    }
                }
            } else {
                if ($files[$key]['error'] !== $this->noFileUploadedResponseCode) {
                    $filesNormalized[] = (object)array(
                        'name'      => $files[$key]['name'],
                        'type'      => $files[$key]['type'],
                        'tmpName'   => $files[$key]['tmp_name'],
                        'error'     => (int)$files[$key]['error'],
                        'size'      => $files[$key]['size'],
                        'inputName' => $key
                    );
                }
            }
        }

        return $filesNormalized;
    }
}
