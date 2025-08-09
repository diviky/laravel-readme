<?php

declare(strict_types=1);

namespace Diviky\Readme\Parsers;

use Diviky\Readme\Helpers\CodeGen;

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

    public function setVariables(array $variables)
    {
        $this->variables = $variables;

        return $this;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

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

        $languages = $this->getAvailableLanguages();

        if (empty($languages)) {
            return preg_replace($regex, '', $content);
        }

        return preg_replace_callback(
            $regex,
            function ($matches) use ($languages): string {
                if (isset($matches[2])) {
                    return $this->snippets($matches[2], $languages);
                }

                return '';
            },
            $content
        );
    }

    public function getAvailableLanguages(): ?array
    {
        $languages = $this->config['snippets'] ?? null;

        if ($languages === false) {
            return null;
        }

        $codegen = new CodeGen;

        if (!is_array($languages)) {
            try {
                $languages = $codegen->getAvailableLanguages();
            } catch (\Exception $e) {
                return null;
            }
        }

        return $languages;
    }

    public function snippets($file, $languages): string
    {
        $snippets = '';
        $code = $this->config['code'] ?? [];
        $path = $this->config['docs']['path'];
        $file = $path . '/' . $file;

        $content = file_get_contents($file);
        $request = json_decode(str_replace(['{{', '}}'], ['{', '}'], $content), true);

        foreach ($languages as $language) {
            if (!in_array($language['key'], $code)) {
                continue;
            }

            $snippets .= $this->snippet($request, $language);
        }

        return $snippets;
    }

    protected function snippet($request, $language): string
    {
        $codegen = new CodeGen;

        $snippets = '';

        foreach ($language['variants'] as $variant) {
            $syntax = $language['syntax_mode'];
            $syntax = $this->mappings[$syntax] ?? $syntax;

            try {
                $code = $codegen->generateCode($request, $language['key'], $variant['key']);
            } catch (\Exception) {
                continue;
            }

            $snippet = '';
            $snippet .= '{.code-block .code-block-' . $syntax . '}' . "\n";
            $snippet .= '##### ' . $language['label'] . ' - ' . $variant['key'] . "\n";
            $snippet .= '{.code-block .code-block-' . $syntax . '}' . "\n";
            $snippet .= '```' . $syntax . "\n";
            $snippet .= $code;
            $snippet .= "\n" . '```' . "\n";

            $snippets .= str_replace('<?php', '&gt;?php', $snippet);
        }

        return $snippets;
    }
}
