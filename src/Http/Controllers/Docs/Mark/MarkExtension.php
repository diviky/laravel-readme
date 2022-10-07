<?php

declare(strict_types=1);

/*
 * This file is part of the Deployment package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Diviky\Readme\Http\Controllers\Docs\Mark;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class MarkExtension implements ExtensionInterface
{
    protected $emojis = [];

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addInlineParser(new EmojiParser($this->getEmojis()))
            ->addInlineParser(new FontAwesomeParser($this->getEmojis()));
    }

    protected function getEmojis(): array
    {
        if (empty($this->emojis)) {
            $this->emojis = json_decode(file_get_contents(__DIR__ . '/emoji.json'), true);
        }

        return $this->emojis;
    }
}
