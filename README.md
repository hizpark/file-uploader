# File Uploader

> 灵活、解耦的文件上传组件

![License](https://img.shields.io/github/license/hizpark/file-uploader?style=flat-square)
![Latest Version](https://img.shields.io/packagist/v/hizpark/file-uploader?style=flat-square)
![PHP Version](https://img.shields.io/badge/php-8.2--8.4-blue?style=flat-square)
![Static Analysis](https://img.shields.io/badge/static_analysis-PHPStan-blue?style=flat-square)
![Tests](https://img.shields.io/badge/tests-PHPUnit-brightgreen?style=flat-square)
[![codecov](https://codecov.io/gh/hizpark/file-uploader/branch/main/graph/badge.svg)](https://codecov.io/gh/hizpark/file-uploader)
![CI](https://github.com/hizpark/file-uploader/actions/workflows/ci.yml/badge.svg?style=flat-square)

一个轻量组件，支持自定义验证和作用域标识管理（ScopedStorageStrategy），适合有状态或无状态的 HTTP API 场景，可快速集成到现有系统。

## ✨ 特性

- 自定义文件验证：通过 `UploadedFileValidator` 支持扩展名、大小等规则
- 作用域标识管理：支持 `ScopedStorageStrategy` 管理文件作用域或会话信息
- 面向接口编程：核心接口 `FileUploaderInterface`、`UploadContextInterface`、`UploadedFileInterface` 易于扩展
- 轻量易用：独立组件，可快速集成到现有系统，无需依赖框架

## 📦 安装

```bash
composer require hizpark/file-uploader
```

## 📂 目录结构

```txt
src
├── Exception
│   ├── FileUploadEmptyException.php
│   └── FileUploadException.php
├── Local
│   ├── FileUploader.php
│   ├── UploadContext.php
│   ├── UploadedFile.php
│   └── UploadedFileResult.php
├── Validator
│   └── UploadedFileValidator.php
├── FileUploaderInterface.php
├── UploadContextInterface.php
├── UploadedFileInterface.php
└── UploadedFileResultInterface.php
```

## 🚀 用法示例

### 示例 1：单文件上传

```php
use Hizpark\FileUploader\Local\FileUploader;
use Hizpark\FileUploader\Local\UploadContext;
use Hizpark\FileUploader\Local\UploadedFile;
use Hizpark\FileUploader\Validator\UploadedFileValidator;

$file = new UploadedFile('example.png', 'image/png', '/tmp/php123', 102400);

$context = new UploadContext(
    basePath: '/var/www/uploads',
    validator: new UploadedFileValidator()
);

$uploader = new FileUploader($context);
$result = $uploader->upload($file);

if ($result->isValid()) {
    echo "File uploaded successfully: " . $result->getPath();
} else {
    echo "Upload failed: " . $result->getError();
}
```

### 示例 2：多文件上传

```php
$files = [
    new UploadedFile('a.png', 'image/png', '/tmp/phpA', 102400),
    new UploadedFile('b.pdf', 'application/pdf', '/tmp/phpB', 204800),
];

$results = [];
foreach ($files as $file) {
    $results[] = $uploader->upload($file);
}

foreach ($results as $result) {
    if ($result->isValid()) {
        echo "Uploaded: " . $result->getPath() . PHP_EOL;
    } else {
        echo "Failed: " . $result->getError() . PHP_EOL;
    }
}
```

## 📐 接口说明

### FileUploaderInterface

> 文件上传核心接口，提供单文件和批量上传方法

```php
interface FileUploaderInterface
{
    public function upload(UploadedFileInterface $file): UploadedFileResultInterface;
}
```

### UploadedFileValidator

> 文件验证器，可自定义允许的扩展名和大小限制

```php
class UploadedFileValidator
{
    public function __construct(array $allowedExtensions = ['jpg','png','gif','pdf'], int $maxSize = 5_000_000) { ... }
    public function validate(UploadedFileInterface $file): ValidationResult { ... }
}
```

## 🔍 静态分析

使用 PHPStan 工具进行静态分析，确保代码的质量和一致性：

```bash
composer stan
```

## 🎯 代码风格

使用 PHP-CS-Fixer 工具检查代码风格：

```bash
composer cs:chk
```

使用 PHP-CS-Fixer 工具自动修复代码风格问题：

```bash
composer cs:fix
```

## ✅ 单元测试

执行 PHPUnit 单元测试：

```bash
composer test
```

执行 PHPUnit 单元测试并生成代码覆盖率报告：

```bash
composer test:coverage
```

## 🤝 贡献指南

欢迎 Issue 与 PR，建议遵循以下流程：

1. Fork 仓库
2. 创建新分支进行开发
3. 提交 PR 前请确保测试通过、风格一致
4. 提交详细描述

## 📜 License

MIT License. See the [LICENSE](LICENSE) file for details.
