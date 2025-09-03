<?php

declare(strict_types=1);

namespace Hizpark\FileUploader;

interface UploadedFileResultInterface
{
    public function getFileUrl(): string;
}
