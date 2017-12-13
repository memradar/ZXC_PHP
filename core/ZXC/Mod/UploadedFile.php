<?php

namespace ZXC\Mod;


use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    /**
     * @var string
     */
    private $file;
    /**
     * @var $stream Stream
     */
    private $stream;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $clientName;
    /**
     * @var int
     */
    private $size;
    /**
     * @var string
     */
    private $type;
    /**
     * @var boolean
     */
    private $fileMoved;
    /**
     * @var int
     */
    private $error;
    /**
     * @var string
     */
    private $sapi;
    /**
     * @var int[]
     */
    private static $errors = [
        UPLOAD_ERR_OK,
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE,
        UPLOAD_ERR_PARTIAL,
        UPLOAD_ERR_NO_FILE,
        UPLOAD_ERR_NO_TMP_DIR,
        UPLOAD_ERR_CANT_WRITE,
        UPLOAD_ERR_EXTENSION,
    ];

    public function __construct(
        $file,
        $size = 0,
        $error = 0,
        $clientFilename = null,
        $clientMediaType = null,
        $mode = 'r'
    ) {
        if (!$file) {
            throw new \InvalidArgumentException('Invalid argument $file');
        }
        if (is_string($file)) {
            $this->file = $file;
            $this->stream = new Stream($file, $mode);
        } elseif (is_resource($file)) {
            $this->stream = new Stream($file);
        } elseif ($file instanceof StreamInterface) {
            $this->stream = $file;
        }
        $this->error = self::$errors[$error];
        $this->size = $size;
        $this->clientName = $clientFilename;
        $this->type = $clientMediaType;
        $this->sapi = PHP_SAPI;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * This method MUST return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     *
     * If the moveTo() method has been called previously, this method MUST raise
     * an exception.
     *
     * @return StreamInterface Stream representation of the uploaded file.
     * @throws \RuntimeException in cases when no stream is available or can be
     *     created.
     */
    public function getStream()
    {
        if (!$this->stream) {
            throw new \RuntimeException('Undefined stream');
        }
        return $this->stream;
    }

    /**
     * Move the uploaded file to a new location.
     *
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     *
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     *
     * The original file or stream MUST be removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * used to ensure permissions and upload status are verified correctly.
     *
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     * @param string $targetPath Path to which to move the uploaded file.
     * @throws \InvalidArgumentException if the $targetPath specified is invalid.
     * @throws \RuntimeException on any error during the move operation, or on
     *     the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        if (!$targetPath || !is_string($targetPath)) {
            throw new \InvalidArgumentException('Invalid parameter $targetPath');
        }
//        if (!is_dir($targetPath)) {
//            throw new \InvalidArgumentException('Invalid parameter ' . $targetPath . ' is not directory');
//        }
        if ($this->fileMoved === true) {
            throw new \RuntimeException('Cannot retrieve stream after it has already been moved');
        }
        if ($this->error !== self::$errors[0]) {
            throw new \RuntimeException('Cannot retrieve stream due to upload error');
        }
        if (!$this->sapi || $this->sapi == 'cli' || !$this->file) {
            $handle = fopen($targetPath, Stream::MODE_READ_WRITE_RESET);
            if ($handle === false) {
                throw new \RuntimeException('Can not write to path: ' . $targetPath);
            }
            $this->stream->rewind();
            while (!$this->stream->eof()) {
                fwrite($handle, $this->stream->read(4096));
            }
            fclose($handle);
        } else {
            if (move_uploaded_file($this->file, $targetPath) === false) {
                throw new \RuntimeException('Error moving uploaded file');
            }
        }
    }

    /**
     * Retrieve the file size.
     *
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
     *
     * If the file was uploaded successfully, this method MUST return
     * UPLOAD_ERR_OK.
     *
     * Implementations SHOULD return the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename()
    {
        return $this->clientName;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @return string|null The media type sent by the client or null if none
     *     was provided.
     */
    public function getClientMediaType()
    {
        return $this->type;
    }
}