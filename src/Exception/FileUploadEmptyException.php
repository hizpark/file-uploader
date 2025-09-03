<?php

declare(strict_types=1);

namespace Hizpark\FileUploader\Exception;

use InvalidArgumentException;

/**
 * 表示未上傳檔案的異常。
 *
 * 常用於處理檔案欄位為「可選」的情境，
 * 開發者可根據業務邏輯選擇捕獲或忽略此異常。
 */
class FileUploadEmptyException extends InvalidArgumentException
{
}
