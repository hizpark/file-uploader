<?php

declare(strict_types=1);

namespace Hizpark\FileUploader\Local;

use Hizpark\FileUploader\Exception\FileUploadEmptyException;
use Hizpark\FileUploader\Exception\FileUploadException;
use Hizpark\FileUploader\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    private string $name;

    private string $type;

    private string $tmpName;

    private int $size;

    private function __construct(string $name, string $type, string $tmpName, int $size)
    {
        $this->name    = $name;
        $this->type    = $type;
        $this->tmpName = $tmpName;
        $this->size    = $size;
    }

    /**
     */
    public static function create(string $fileField): UploadedFileInterface
    {
        if (!isset($_FILES[$fileField])) {
            $message = sprintf("The input '%s' was not found.", $fileField);

            throw new FileUploadEmptyException($message);
        }

        /** @var array{name: string, type: string, tmp_name: string, error: int, size: int} $_file */
        $_file = $_FILES[$fileField];

        if (empty($_file['name']) || $_file['error'] === UPLOAD_ERR_NO_FILE) {
            throw new FileUploadEmptyException("No file was uploaded for '{$fileField}'.");
        }

        if ($_file['error']) {
            $message = sprintf('[%s]:%s', $_file['name'], self::getUploadError($_file['error']));

            throw new FileUploadException($message);
        }

        return new self($_file['name'], $_file['type'], $_file['tmp_name'], $_file['size']);
    }

    /**
     * @return array<string|int, UploadedFileInterface> List of uploaded files. Returns empty array if none uploaded.
     */
    public static function forFiles(string $fileField): array
    {
        if (!isset($_FILES[$fileField])) {
            $message = sprintf("The input '%s' was not found.", $fileField);

            throw new FileUploadEmptyException($message);
        }

        /** @var array{
         *   name: string|array<int, string>,
         *   type: string|array<int, string>,
         *   tmp_name: string|array<int, string>,
         *   error: int|array<int, int>,
         *   size: int|array<int, int>
         * } $_files
         */
        $_files = $_FILES[$fileField];

        $errors     = [];
        $validFiles = [];

        if (is_array($_files['name'])) {
            /** @var array<int, string> $names */
            $names = $_files['name'];
            $count = count($names);
            for ($i = 0; $i < $count; $i++) {
                $name    = $_files['name'][$i]     ?? null;
                $type    = $_files['type'][$i]     ?? null;
                $tmpName = $_files['tmp_name'][$i] ?? null;
                $size    = $_files['size'][$i]     ?? null;
                $error   = $_files['error'][$i]    ?? UPLOAD_ERR_NO_FILE;

                if (empty($name) || $error === UPLOAD_ERR_NO_FILE) {
                    continue; // 空文件跳过
                }

                if ($error) {
                    $errors[] = sprintf('[%s]:%s', $name, self::getUploadError($error));
                    continue;
                }

                $validFiles[] = new self($name, (string)$type, (string)$tmpName, (int)$size);
            }
        } else {
            // 单文件结构
            $name    = $_files['name'];
            $type    = $_files['type'];
            $tmpName = $_files['tmp_name'];
            $size    = $_files['size'];
            $error   = (int) $_files['error'];

            if (empty($name) || $error === UPLOAD_ERR_NO_FILE) {
                return []; // 没有文件，返回空数组
            }

            if (!is_string($type) || !is_string($tmpName) || !is_int($size)) {
                return []; // 非法结构
            }

            if ($error) {
                throw new FileUploadException(sprintf('[%s]:%s', (string)$name, self::getUploadError($error)));
            }

            $validFiles[] = new self($name, $type, $tmpName, $size);
        }

        if (!empty($errors)) {
            throw new FileUploadException(implode(PHP_EOL, $errors));
        }

        return $validFiles;
    }

    private static function getUploadError(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
            default               => 'Unknown upload error.',
        };
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTmpName(): string
    {
        return $this->tmpName;
    }

    public function getSize(): int
    {
        return $this->size;
    }
}
