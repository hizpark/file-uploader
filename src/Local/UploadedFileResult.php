<?php

declare(strict_types=1);

namespace Hizpark\FileUploader\Local;

use Hizpark\FileUploader\UploadedFileResultInterface;

class UploadedFileResult implements UploadedFileResultInterface
{
    public function __construct(
        public readonly string $fileUrl,
        public readonly string $filePath,
    ) {
    }

    public function getFileUrl(): string
    {
        return $this->fileUrl;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
