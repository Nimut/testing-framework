<?php
namespace Nimut\TestingFramework\File;

/*
 * This file is part of the NIMUT testing-framework project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read
 * LICENSE file that was distributed with this source code.
 */
use Nimut\TestingFramework\Exception\NtfStreamException;

/**
 * Read-only stream wrapper to get testing fixtures with ntf:// protocol
 */
class NtfStreamWrapper
{
    /**
     * @var resource
     */
    protected $fileHandle = null;

    /**
     * @var bool
     */
    protected static $registered = false;

    /**
     * @var string
     */
    protected static $root;

    /**
     * @var string
     */
    protected static $scheme = 'ntf';

    /**
     * Method to register the stream wrapper
     *
     * If the stream is already registered the method returns silently. If there
     * is already another stream wrapper registered for the ntf scheme
     * a NtfStreamException will be thrown.
     *
     * @throws NtfStreamException
     * @return void
     */
    public static function register()
    {
        if (self::$registered) {
            return;
        }

        if (@stream_wrapper_register(self::$scheme, __CLASS__) === false) {
            throw new NtfStreamException('A handler has already been registered for the ' . self::$scheme . ' protocol.');
        }

        self::$root = rtrim(realpath(__DIR__ . '/../../../res/Fixtures'), '\\/') . '/';
        self::$registered = true;
    }

    /**
     * Unregisters a previously registered stream wrapper for the ntf scheme.
     *
     * If this stream wrapper wasn't registered, the method returns silently.
     *
     * If unregistering fails, or if the stream wrapper for ntf scheme was not
     * registered with this class, a NtfStreamException will be thrown.
     *
     * @throws NtfStreamException
     * @return void
     */
    public static function unregister()
    {
        if (!self::$registered) {
            if (in_array(self::$scheme, stream_get_wrappers())) {
                throw new NtfStreamException('The stream wrapper for the protocol ' . self::$scheme . ' was not registered with the nimut/testing-framework.');
            }

            return;
        }

        if (!@stream_wrapper_unregister(self::$scheme)) {
            throw new NtfStreamException('Failed to unregister the stream wrapper for the ' . self::$scheme . ' protocol.');
        }

        self::$registered = false;
    }

    /**
     * Closes a directory handle
     *
     * @return bool
     */
    public function dir_closedir()
    {
        return true;
    }

    /**
     * Opens a directory handle
     *
     * @param string $path
     * @param int $options
     * @return bool
     */
    public function dir_opendir($path, $options = 0)
    {
        return true;
    }

    /**
     * Reads entries from a directory handle
     *
     * @return string
     */
    public function dir_readdir()
    {
        return '';
    }

    /**
     * Rewinds a directory handle
     *
     * @return bool
     */
    public function dir_rewinddir()
    {
        return true;
    }

    /**
     * Creates a directory
     *
     * @param string $path
     * @param int $mode
     * @param int $options
     * @return bool
     */
    public function mkdir($path, $mode, $options = 0)
    {
        return true;
    }

    /**
     * Renames a file or directory
     *
     * @param string $pathFrom
     * @param string $pathTo
     * @return bool
     */
    public function rename($pathFrom, $pathTo)
    {
        return true;
    }

    /**
     * Removes a directory
     *
     * @param string $path
     * @return bool
     */
    public function rmdir($path)
    {
        return true;
    }

    /**
     * Retrieves the underlaying resource
     *
     * @param int $castAs
     * @return resource|bool
     */
    public function stream_cast($castAs)
    {
        if ($this->fileHandle !== null && $castAs & STREAM_CAST_AS_STREAM) {
            return $this->fileHandle;
        }

        return false;
    }

    /**
     * Closes a resource
     */
    public function stream_close()
    {
        if ($this->fileHandle !== null) {
            fclose($this->fileHandle);
            $this->fileHandle = null;
        }
    }

    /**
     * Tests for end-of-file on a file pointer
     *
     * @return bool
     */
    public function stream_eof()
    {
        if ($this->fileHandle === null) {
            return false;
        }

        return feof($this->fileHandle);
    }

    /**
     * Flushes the output
     *
     * @return bool
     */
    public function stream_flush()
    {
        return true;
    }

    /**
     * Advisory file locking
     *
     * @param int $operation
     * @return bool
     */
    public function stream_lock($operation)
    {
        return true;
    }

    /**
     * Changes stream metadata
     *
     * @param string $path
     * @param int $options
     * @param mixed $value
     * @return bool
     */
    public function stream_metadata($path, $options, $value)
    {
        return true;
    }

    /**
     * Opens a file
     *
     * @param string $path
     * @param string $mode
     * @param int $options
     * @param string &$opened_path
     * @return bool
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        if ($this->fileHandle !== null) {
            return false;
        }

        $this->fileHandle = fopen($this->resolvePath($path), $mode, (bool)($options & STREAM_USE_PATH));

        return $this->fileHandle !== false;
    }

    /**
     * Reads from stream
     *
     * @param int $length
     * @return string
     */
    public function stream_read($length)
    {
        if ($this->fileHandle === null) {
            return false;
        }

        return fread($this->fileHandle, $length);
    }

    /**
     * Seeks to specific location in a stream
     *
     * @param int $offset
     * @param int $whence
     * @return bool
     */
    public function stream_seek($offset, $whence = SEEK_SET)
    {
        if ($this->fileHandle === null) {
            return false;
        }

        return fseek($this->fileHandle, $offset, $whence);
    }

    /**
     * Changes stream options
     *
     * @param int $option
     * @param int $arg1
     * @param int $arg2
     * @return bool
     */
    public function stream_set_option($option, $arg1, $arg2)
    {
        return true;
    }

    /**
     * Retrieves information about a file resource
     *
     * @return array|bool
     */
    public function stream_stat()
    {
        if ($this->fileHandle === null) {
            return false;
        }

        return fstat($this->fileHandle);
    }

    /**
     * Retrieves the current position of a stream
     *
     * @return int
     */
    public function stream_tell()
    {
        if ($this->fileHandle === null) {
            return -1;
        }

        return ftell($this->fileHandle);
    }

    /**
     * Truncates stream
     *
     * @param int $size
     * @return bool
     */
    public function stream_truncate($size)
    {
        if ($this->fileHandle === null) {
            return false;
        }

        return ftruncate($this->fileHandle, $size);
    }

    /**
     * Writes to stream
     *
     * @param string $data
     * @return int
     */
    public function stream_write($data)
    {
        return strlen($data);
    }

    /**
     * Deletes a file
     *
     * @param string $path
     * @return bool
     */
    public function unlink($path)
    {
        return true;
    }

    /**
     * Retrieves information about a file
     *
     * @param string $path
     * @param int $flags
     * @return array
     */
    public function url_stat($path, $flags)
    {
        $path = $this->resolvePath($path);
        if ($flags & STREAM_URL_STAT_LINK) {
            return @lstat($path);
        }

        return @stat($path);
    }

    /**
     * Helper method to resolve the path
     *
     * @param string $path
     * @return string
     */
    protected function resolvePath($path)
    {
        $newPath = [];

        $path = trim($path);
        $path = substr($path, strlen(self::$scheme . '://'));
        $path = strtr($path, ['\\' => '/', '//' => '/']);

        foreach (explode('/', $path) as $part) {
            if ($part !== '.') {
                if ($part !== '..') {
                    $newPath[] = $part;
                } elseif (count($newPath) > 1) {
                    array_pop($newPath);
                }
            }
        }

        return self::$root . implode('/', $newPath);
    }
}
