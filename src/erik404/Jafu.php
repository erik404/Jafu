<?php
/**
 * v1.0.1
 * Just Another File Uploader
 * Erik-Jan van de Wal, MIT License 2017.
 */

namespace erik404;

class Jafu
{
    /**
     * see: https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types
     */
    const APPLICATION_TYPES = ['application/octet-stream', 'application/pkcs12', 'application/vnd.mspowerpoint', 'application/xhtml+xml', 'application/xml', 'application/pdf', 'application/msword'];
    const AUDIO_TYPES       = ['audio/midi', 'audio/mpeg', 'audio/webm', 'audio/ogg', 'audio/wav'];
    const TEXT_TYPES        = ['text/plain', 'text/html', 'text/css', 'text/javascript'];
    const IMAGE_TYPES       = ['image/gif', 'image/png', 'image/jpeg', 'image/bmp'];
    const VIDEO_TYPES       = ['video/webm', 'video/ogg'];

    /**
     * @var int
     */
    protected $mimeValidationFailedCode = 10;
    protected $mimeTypeNotAllowedCode   = 9;
    protected $noFileUploadedCode       = 4;
    protected $fileUploadedCode         = 0;

    /**
     * @var object
     */
    protected $config;

    /**
     * @var string
     */
    protected $saveLocation;

    /**
     * @var array
     */
    protected $files;

    /**
     * @var array
     */
    protected $allowedMimeTypes = [];

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $results = [];

    /**
     * @param string $saveLocation
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
     * @return string
     */
    public function getSaveLocation()
    {
        return $this->saveLocation;
    }

    /**
     * Returns the default save location from the config file.
     *
     * @return string
     */
    public function getDefaultSaveLocation()
    {
        return $this->config->defaultSaveLocation;
    }

    /**
     * Expects the $_FILES array and normalizes this to a more sane structure.
     *
     * @param mixed $files
     * @return void
     */
    public function setFiles($files)
    {
        $this->files = $this->normalize($files);
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

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
     * @return array
     */
    public function getAllowedMimeTypes()
    {
        return $this->allowedMimeTypes;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param null $config (for PHPUnit test)
     * @throws \Exception
     */
    function __construct($config = null)
    {
        if ($config === null && !file_exists(__DIR__ . '/config.php')) {
            throw new \Exception('The file config.php can not be found. Did you forgot to rename the config.dist.php in src/erik404/ to config.php?');
        }
        $this->config = $config === null ? require(__DIR__ . '/config.php') : $config;
        $this->setSaveLocation($this->config->defaultSaveLocation);
    }

    /**
     * Performs the validation and save operation.
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
     * Checks if there are files uploaded and if one of the files had an error uploading. Stores the error information in $errors[].
     *
     * @return bool
     */
    private function checkForErrors()
    {
        // check if there are uploaded files, if not, set the right error response
        if (empty($this->files)) {
            $this->errors[] = [
                'name'      => null,
                'inputName' => null,
                'error'     => $this->noFileUploadedCode,
                'message'   => $this->config->responseMessages[$this->noFileUploadedCode]
            ];
        } else {
            // loop through all uploaded files and set the according error response if there are any
            foreach ($this->files as $file) {
                if ($file->error !== $this->fileUploadedCode) {
                    $this->errors[] = [
                        'name'      => $file->name,
                        'inputName' => $file->inputName,
                        'error'     => $file->error,
                        'message'   => $this->config->responseMessages[$file->error]
                    ];
                }
            }
        }

        return (!empty($this->errors)); // returns false if there are no errors
    }

    /**
     * Get the MIME type using PHP's finfo. Checks it against the allowedMimeTypes array. Stores the error information in $errors[].
     * See: http://php.net/manual/en/class.finfo.php
     *
     * @return bool
     */
    private function checkIfMimeTypeIsRestricted()
    {
        foreach ($this->files as $file) {
            $finfo    = new \finfo(FILEINFO_MIME);
            $mimeType = $finfo->file($file->tmpName);
            $mimeType = $mimeType === false ? false : explode(';', $mimeType)[0];

            // check the fetched MIME-type against the allowedMimeTypes array
            if (!$mimeType || !in_array($mimeType, $this->allowedMimeTypes)) {
                $error = [
                    'name'      => $file->name,
                    'inputName' => $file->inputName
                ];
                if (!$mimeType) {
                    $error['error']   = $this->mimeValidationFailedCode;
                    $error['message'] = $this->config->responseMessages[$this->mimeValidationFailedCode];
                } else {
                    $error['error']   = $this->mimeTypeNotAllowedCode;
                    $error['message'] = str_replace('%s', $mimeType, $this->config->responseMessages[$this->mimeTypeNotAllowedCode]);
                }
                $this->errors[] = $error;
            }
        }

        return (!empty($this->errors)); // returns false if there are no errors
    }

    /**
     * Creates a guaranteed unique name per file and saves it to the filesystem.
     * On success the path to the new file is stored in the result array together with the name of the input from which the upload originates from.
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
                // throws an exception because this is a error the user can't fix.
                throw new \Exception('The file ' . $target . ' could not be saved to the filesystem.');
            } else {
                // add to result array
                $this->results[] = [
                    'file'      => $target,
                    'inputName' => $file->inputName
                ];
            }
        }
    }

    /**
     * Structure the information stored in $_FILES so Jafu can handle both single and multiple file-uploads.
     * Ignore error code 4 (noFileUploadedCode). This enables optional multiple file-uploads.
     * See: http://php.net/manual/en/features.file-upload.post-method.php
     *
     * @param $files ($_FILES)
     * @return array holding the uploaded file information as objects
     */
    private function normalize($files)
    {
        $filesNormalized = [];
        foreach ($files as $key => $value) {
            if (gettype($files[$key]['name']) === 'array') {
                $arrLength = count($files[$key]['name']);
                for ($i = 0; $i < $arrLength; $i++) {
                    if ($files[$key]['error'][$i] !== $this->noFileUploadedCode) {
                        $filesNormalized[] = (object)[
                            'name'      => $files[$key]['name'][$i],
                            'type'      => $files[$key]['type'][$i],
                            'tmpName'   => $files[$key]['tmp_name'][$i],
                            'error'     => (int)$files[$key]['error'][$i],
                            'size'      => $files[$key]['size'][$i],
                            'inputName' => $key
                        ];
                    }
                }
            } else {
                if ($files[$key]['error'] !== $this->noFileUploadedCode) {
                    $filesNormalized[] = (object)[
                        'name'      => $files[$key]['name'],
                        'type'      => $files[$key]['type'],
                        'tmpName'   => $files[$key]['tmp_name'],
                        'error'     => (int)$files[$key]['error'],
                        'size'      => $files[$key]['size'],
                        'inputName' => $key
                    ];
                }
            }
        }

        return $filesNormalized;
    }
}
