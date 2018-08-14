<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Helpers;

use Threema\MsgApi\Exceptions\InvalidArgumentException;

final class FileAnalysisTool
{
    /**
     * @param string $file
     * @return FileAnalysisResult
     * @throws \Threema\MsgApi\Exceptions\InvalidArgumentException
     */
    public static function analyseOrDie(string $file): FileAnalysisResult
    {
        if (false === file_exists($file)) {
            throw new InvalidArgumentException('No such file ' . $file);
        }

        if (false === is_file($file)) {
            throw new InvalidArgumentException('Not a file: ' . $file);
        }

        $mimeType = '';
        if (function_exists('finfo_open')) {
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file) ?: '';
        } else if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($file);
        }
        if (empty($mimeType)) {
            $mimeType = 'application/octet-stream';
        }

        return new FileAnalysisResult($mimeType, filesize($file) ?: 0, $file);
    }
}
