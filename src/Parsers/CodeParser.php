<?php

declare(strict_types=1);

namespace Diviky\Readme\Parsers;

use Diviky\Readme\Helper\CodeGen;

class CodeParser
{
    protected $mappings = [
        'objectivec' => 'objective-c',
        'c_cpp' => 'c',
        'golang' => 'go',
        'text' => 'http',
    ];

    protected array $config = [];
    protected array $variables = [];

    public function parse(string $content, array $variables, array $config)
    {
        $this->config = $config;
        $this->variables = $variables;

        return $this->parseCode($content);
    }

    protected function parseCode(string $content): string
    {
        $regex = '/\#code ([\"\'])?([^\"\s\']+)([\"\'])?/m';

        if (!preg_match($regex, $content)) {
            return $content;
        }

        $codegen = new CodeGen();

        $languages = $this->config['snippets'] ?? null;

        if (false === $languages) {
            return preg_replace($regex, '', $content);
        }

        if (!is_array($languages)) {
            try {
                $languages = $codegen->getAvailableLanguages();
            } catch (\Exception) {
                return preg_replace($regex, '', $content);
            }
        }

        $path = $this->config['docs']['path'];

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

        $code = $this->config['code'] ?? [];

        foreach ($languages as $language) {
            if (!in_array($language['key'], $code)) {
                continue;
            }

            foreach ($language['variants'] as $variant) {
                $syntax = $language['syntax_mode'];
                $syntax = $this->mappings[$syntax] ?? $syntax;

                $snippet = '';
                $snippet .= '{.code-block .code-block-' . $syntax . '}' . "\n";
                $snippet .= '##### ' . $language['label'] . ' - ' . $variant['key'] . "\n";
                $snippet .= '{.code-block .code-block-' . $syntax . '}' . "\n";
                $snippet .= '```' . $syntax . "\n";

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
