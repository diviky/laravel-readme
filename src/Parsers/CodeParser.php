<?php

declare(strict_types=1);

namespace Diviky\Readme\Parsers;

use Diviky\Readme\Helper\CodeGen;

class CodeParser
{
    public function parse(string $content, array $variables, array $config)
    {
        return $this->parseCode($content);
    }

    protected function parseCode(string $content): string
    {
        $regex = '/\#code ([\"\'])?([^\"\s\']+)([\"\'])?/m';

        if (!preg_match($regex, $content)) {
            return $content;
        }

        $path = config('readme.docs.path');
        $codegen = new CodeGen();

        try {
            $languages = $codegen->getAvailableLanguages();
        } catch (\Exception) {
            return preg_replace($regex, '', $content);
        }

        return preg_replace_callback(
            $regex,
            function ($matches) use ($path, $languages): string {
                if (isset($matches[2])) {
                    return $this->snippet($path . '/' . $matches[2], $languages);
                }

                return '';
            },
            $content
        );
    }

    protected function snippet($file, $languages): string
    {
        $snippets = '';
        $content = file_get_contents($file);
        $request = json_decode(str_replace(['{{', '}}'], ['{', '}'], $content), true);
        $codegen = new CodeGen();

        foreach ($languages as $language) {
            foreach ($language['variants'] as $variant) {
                $snippet = '';
                $snippet .= '##### ' . $language['label'] . ' - ' . $variant['key'] . "\n";
                $snippet .= '{.code-block-' . $language['syntax_mode'] . '}' . "\n";
                $snippet .= '```' . $language['syntax_mode'] . "\n";

                try {
                    $snippet .= $codegen->generateCode($request, $language['key'], $variant['key']);
                } catch (\Exception) {
                    continue;
                }
                $snippet .= "\n" . '```' . "\n";

                $snippets .= str_replace('<?php', '&gt;?php', $snippet);
            }
        }

        return $snippets;
    }
}
