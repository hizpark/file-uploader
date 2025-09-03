<?php

declare(strict_types=1);

namespace Hizpark\FileUploader;

interface FileUploaderInterface
{
    public function upload(UploadedFileInterface $file, UploadContextInterface $context): UploadedFileResultInterface;
}
