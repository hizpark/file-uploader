<?php

declare(strict_types=1);

namespace Hizpark\FileUploader\Local;

use Closure;
use Exception;
use Hizpark\FileUploader\Exception\FileUploadException;
use Hizpark\FileUploader\FileUploaderInterface;
use Hizpark\FileUploader\UploadContextInterface;
use Hizpark\FileUploader\UploadedFileInterface;
use Hizpark\FileUploader\UploadedFileResultInterface;
use Hizpark\ValidationInterface\ValidatorInterface;

class FileUploader implements FileUploaderInterface
{
    public function upload(UploadedFileInterface $file, UploadContextInterface $context): UploadedFileResultInterface
    {
        // 1️⃣ 验证器
        $validator = $context->getValidator();

        if ($validator) {
            if ($validator instanceof ValidatorInterface) {
                $validator->validate();
            } elseif ($validator instanceof Closure) {
                $validator($file);
            }
        }

        // 2️⃣ 目标路径
        $destPath = self::resolveDestPath($context->getBasePath(), $context->getDirname());

        if (!is_dir($destPath) && !mkdir($destPath, 0o755, true) && !is_dir($destPath)) {
            throw new FileUploadException("Cannot create directory: $destPath");
        }
        $destPath = realpath($destPath);

        if ($destPath === false) {
            throw new FileUploadException('Invalid dest path');
        }

        // 3️⃣ 安全检查
        $documentRoot = self::getDocumentRootPath();

        if (!str_starts_with($destPath, $documentRoot)) {
            throw new FileUploadException('The upload path exceeds the access range.');
        }

        // 4️⃣ 文件名
        $filenameCallback = $context->getFilenameCallback();

        if ($filenameCallback) {
            $destFilename = $filenameCallback($file->getName());
        } else {
            $destFilename = self::generateFilename($file->getName());
        }

        self::validateDestFilename($destFilename);

        // 5️⃣ 唯一化文件名
        $destFile = $destPath . DIRECTORY_SEPARATOR . $destFilename;
        $count    = 1;
        $pathInfo = pathinfo($destFile);

        while (file_exists($destFile)) {
            $newFilename = sprintf('%s_%d', $pathInfo['filename'], $count);

            if (!empty($pathInfo['extension'])) {
                $newFilename .= '.' . $pathInfo['extension'];
            }
            $destFile = $destPath . DIRECTORY_SEPARATOR . $newFilename;
            $count++;
        }

        // 6️⃣ 上传文件
        if (!is_uploaded_file($file->getTmpName())) {
            throw new FileUploadException('Temporary file does not exist.');
        }

        if (false === move_uploaded_file($file->getTmpName(), $destFile)) {
            throw new FileUploadException('Cannot write file to disk.');
        }

        // 7️⃣ 返回结果
        $fileUrl = str_replace($documentRoot, '', $destFile);

        return new UploadedFileResult($fileUrl, $destFile);
    }

    /**
     * @param UploadedFileInterface[] $files
     *
     * @return UploadedFileResultInterface[]
     */
    public static function forFiles(array $files, UploadContextInterface $context): array
    {
        $results = [];
        $errors  = [];

        foreach ($files as $file) {
            try {
                $uploader  = new self();
                $results[] = $uploader->upload($file, $context);
            } catch (FileUploadException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            // 可选：记录错误或抛出
            throw new FileUploadException(implode(PHP_EOL, $errors));
        }

        return $results;
    }

    private static function resolveDestPath(string $uploadPath, string $dirname): string
    {
        $documentRoot = self::getDocumentRootPath();
        $destPath     = rtrim($uploadPath, '/');

        if ($destPath[0] !== '/') {
            $destPath = $documentRoot . DIRECTORY_SEPARATOR . $destPath;
        }

        if ($dirname !== '') {
            $destPath .= DIRECTORY_SEPARATOR . trim($dirname, '/');
        }

        return $destPath;
    }

    private static function getDocumentRootPath(): string
    {
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;

        if (!is_string($documentRoot) || $documentRoot === '') {
            throw new FileUploadException('Document root is not set or invalid.');
        }

        $documentRoot = realpath($documentRoot);

        if ($documentRoot === false) {
            throw new FileUploadException('Invalid document root path.');
        }

        return rtrim($documentRoot, '/');
    }

    private static function validateDestFilename(string $destFilename): void
    {
        if (in_array($destFilename, ['', '.', '..'], true)) {
            throw new FileUploadException('Invalid filename: Filename cannot be empty or a reserved name like "." or "..".');
        }

        if (preg_match('/[\/\\\\:*?"<>|\x00-\x1F]/u', $destFilename)) {
            throw new FileUploadException('Invalid filename: Filename contains illegal characters.');
        }
    }

    private static function generateFilename(string $filename): string
    {
        $microTime     = microtime(true);
        $seconds       = (int)floor($microTime);
        $microSeconds  = $microTime - $seconds;
        $formattedTime = date('YmdHis', $seconds);
        $milliseconds  = (int)($microSeconds * 1000);

        try {
            $hashString = bin2hex(random_bytes(8));
        } catch (Exception $e) {
            throw new FileUploadException('Failed to generate random filename hash.', 0, $e);
        }

        $destFilePrefix = sprintf('%s_%03d_%s-', $formattedTime, $milliseconds, $hashString);

        $filenameLengthLimit = 255 - strlen($destFilePrefix);

        if (strlen($filename) > $filenameLengthLimit) {
            throw new FileUploadException(sprintf('Filename exceeds the maximum length of %d characters.', $filenameLengthLimit));
        }

        $sanitizedFilename = preg_replace('/[\s-]+/', '_', $filename)      ?? '';
        $sanitizedFilename = preg_replace('/_+/', '_', $sanitizedFilename) ?? '';
        $sanitizedFilename = trim($sanitizedFilename, '_');

        $pathInfo     = pathinfo($sanitizedFilename);
        $filenamePart = $pathInfo['filename'];
        $extension    = $pathInfo['extension'] ?? '';

        $destFilename = sprintf('%s%s', $destFilePrefix, $filenamePart);

        if ($extension !== '') {
            $destFilename .= '.' . strtolower($extension);
        }

        return $destFilename;
    }

    public static function getOriginalFilename(string $filename): string
    {
        $originalFilename = preg_replace('/^\d{14}_\d{3}_[0-9a-z]{16}-/', '', $filename);

        return $originalFilename ?: $filename;
    }
}
