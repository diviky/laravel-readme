<?php

declare(strict_types=1);

namespace Diviky\Readme\Helpers;

class CodeParser
{
    public function parse(string $content)
    {
        return $this->parseCode($content);
    }

    protected function parseCode(string $content): string
    {
        $regex = '/\#code ([\"\'])?([^\"\s\']+)([\"\'])?/m';

        if (!preg_match($regex, $content)) {
            return $content;
        }

        return preg_replace_callback(
            $regex,
            function ($matches): string {
                if (isset($matches[2])) {
                    return '<livewire:readme.code.generator file=' . $matches[2] . ' lazy />' . "\n";
                }

                return '';
            },
            $content
        );
    }
}
