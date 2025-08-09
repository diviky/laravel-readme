<?php

declare(strict_types=1);

namespace Diviky\Readme\Helpers;

class AssetsParser
{
    public function parse(string $content)
    {
        return $this->parseImages($content);
    }

    protected function parseImages(string $content): string
    {
        $regex = '/\((\/images\/[^\)]+)\)/m';

        return preg_replace_callback(
            $regex,
            function (array $matches): string {
                if (isset($matches[1])) {
                    return '({{ asset(\'' . $matches[1] . '\') }})';
                }

                return '';
            },
            $content
        );
    }
}
