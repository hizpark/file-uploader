<?php

declare(strict_types=1);

namespace Hizpark\FileUploader\Local;

use Closure;
use Hizpark\FileUploader\UploadContextInterface;
use Hizpark\ScopedStorageStrategy\ScopedStorageStrategyInterface;
use Hizpark\ScopedStorageStrategy\Session\SessionInitializerWithToken;
use Hizpark\ScopedStorageStrategy\Session\SessionStorageStrategy;
use Hizpark\ValidationInterface\ValidatorInterface;

class UploadContext implements UploadContextInterface
{
    protected string $basePath;

    protected string $dirname;

    protected ?string $scope;

    /**  */
    protected ValidatorInterface|Closure|null $validator;

    /**  */
    protected ?Closure $filenameCallback;

    /**
     * @var array<string, mixed>
     */
    protected array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        string $basePath,
        string $dirname = '',
        ?string $scope = null,
        ValidatorInterface|Closure|null $validator = null,
        ?Closure $filenameCallback = null,
        array $options = [],
    ) {
        $this->basePath         = $basePath;
        $this->dirname          = $dirname;
        $this->scope            = $scope;
        $this->validator        = $validator;
        $this->filenameCallback = $filenameCallback;
        $this->options          = $options;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getDirname(): string
    {
        return $this->dirname;
    }

    /**
     * 返回作用域，用于多步上传的安全性管理
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * 可选：获取存储策略
     */
    public function getStorageStrategy(string $scopeKey = 'token'): ?ScopedStorageStrategyInterface
    {
        if ($this->scope === null) {
            return null;
        }

        $rawToken    = $_REQUEST[$scopeKey] ?? '';
        $token       = is_scalar($rawToken) ? (string) $rawToken : '';
        $initializer = new SessionInitializerWithToken($token);

        return new SessionStorageStrategy($this->scope, $initializer);
    }

    public function getValidator(): ValidatorInterface|Closure|null
    {
        return $this->validator;
    }

    public function getFilenameCallback(): ?Closure
    {
        return $this->filenameCallback;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
