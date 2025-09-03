<?php

declare(strict_types=1);

namespace Hizpark\FileUploader;

interface UploadedFileInterface
{
    public function getName(): string;

    public function getType(): string;

    public function getTmpName(): string;

    public function getSize(): int;
}
