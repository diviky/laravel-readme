<?php

declare(strict_types=1);

namespace Diviky\Readme\Helper;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class CodeGen
{
    private static ?string $customWorkingDirPath = null;

    public static function setCustomWorkingDirPath(?string $path): void
    {
        static::$customWorkingDirPath = $path;
    }

    public static function generate(
        array $request,
        ?string $language = null,
        ?string $variant = null,
    ): string {
        $language = $language ?? 'cURL';
        $variant = $variant ?? 'cURL';

        return (new static())->generateCode($request, $language, $variant);
    }

    public function getAvailableLanguages(): array
    {
        $results = $this->callCodeGen('languages');

        return json_decode($results, true);
    }

    public function getAvailableOptions(): array
    {
        $results = $this->callCodeGen('options');

        return json_decode($results, true);
    }

    public function languageIsAvailable(string $language): bool
    {
        return in_array($language, $this->getAvailableLanguages());
    }

    public function generateCode(array $request, ?string $language = null, ?string $variant = null): string
    {
        return $this->callCodeGen('convert', ['request' => $request, 'language' => $language, 'variant' => $variant]);
    }

    public function getWorkingDirPath(): string
    {
        if (null !== static::$customWorkingDirPath && ($path = realpath(static::$customWorkingDirPath)) !== false) {
            return $path;
        }

        return realpath(dirname(__DIR__) . '/../bin');
    }

    protected function callCodeGen(...$arguments): string
    {
        $root = '';

        $command = [
            (new ExecutableFinder())->find('node', 'node', [
                '/usr/local/bin',
                '/usr/bin',
                '/opt/homebrew/bin',
            ]),
            'codegen.js',
            json_encode(array_values(array_merge([$root], $arguments))),
        ];

        $process = new Process(
            $command,
            $this->getWorkingDirPath(),
            null,
        );

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}
