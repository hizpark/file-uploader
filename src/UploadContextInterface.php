<?php

declare(strict_types=1);

namespace Hizpark\FileUploader;

use Closure;
use Hizpark\ScopedStorageStrategy\ScopedStorageStrategyInterface;
use Hizpark\ValidationInterface\ValidatorInterface;

interface UploadContextInterface
{
    public function getBasePath(): string;

    public function getDirname(): string;

    public function getScope(): ?string;

    public function getStorageStrategy(): ?ScopedStorageStrategyInterface;

    public function getValidator(): ValidatorInterface|Closure|null;

    /**
     * @return null|Closure(string):string
     */
    public function getFilenameCallback(): ?Closure;

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array;
}
