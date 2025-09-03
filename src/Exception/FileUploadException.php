<?php

declare(strict_types=1);

namespace Hizpark\FileUploader\Exception;

use RuntimeException;

/**
 * 表示檔案上傳過程中發生錯誤的異常。
 *
 * 用於捕捉如檔案尺寸過大、臨時目錄遺失、寫入失敗等上傳錯誤。
 */
class FileUploadException extends RuntimeException
{
}
