<?php

declare(strict_types=1);

namespace Hizpark\FileUploader\Validator;

use Hizpark\FileUploader\UploadedFileInterface;
use Hizpark\ValidationInterface\Result\ValidationResult;
use Hizpark\ValidationInterface\ValidationResultInterface;
use Hizpark\ValidationInterface\Validator\AbstractValidator;

class UploadedFileValidator extends AbstractValidator
{
    /** @var UploadedFileInterface */
    protected object $target;

    /** @var string[] */
    protected array $allowedExtensions;

    protected int $maxSize;

    /**
     * @param array<string> $allowedExtensions
     */
    public function __construct(UploadedFileInterface $target, array $allowedExtensions = ['jpg','png','gif','pdf'], int $maxSize = 5_000_000)
    {
        parent::__construct($target);
        $this->allowedExtensions = $allowedExtensions;
        $this->maxSize           = $maxSize;
    }

    public function validate(): ValidationResult
    {
        $file      = $this->target;
        $extension = strtolower(pathinfo($file->getName(), PATHINFO_EXTENSION));

        if (!in_array($extension, $this->allowedExtensions, true)) {
            return $this->fail("Extension '{$extension}' is not allowed.");
        }

        if ($file->getSize() > $this->maxSize) {
            return $this->fail("File size exceeds limit of {$this->maxSize} bytes.");
        }

        return $this->ok();
    }

    /**
     * Validate multiple files
     *
     * @param UploadedFileInterface[] $files
     * @param bool                    $collectAllErrors Whether to collect all errors or return the first error immediately
     *
     * @return ValidationResultInterface|null Returns null if all files pass, or a ValidationResult (single or aggregated)
     */
    public static function forFiles(array $files, bool $collectAllErrors = false): ?ValidationResultInterface
    {
        $errors = [];

        foreach ($files as $file) {
            $validator = new self($file);
            $result    = $validator->validate();

            if (!$result->isValid()) {
                if (!$collectAllErrors) {
                    return $result; // 返回第一条错误
                }
                $errors[] = sprintf('[%s]: %s', $file->getName(), $result->getError());
            }
        }

        if (!empty($errors)) {
            return new ValidationResult(false, implode(PHP_EOL, $errors));
        }

        return null; // 全部文件验证通过
    }
}
