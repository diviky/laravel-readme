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

use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

/**
 * This is the emoji parser class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class EmojiParser implements InlineParserInterface
{
    /**
     * The emoji mappings.
     *
     * @var string[]
     */
    protected $map;

    /**
     * Create a new emoji parser instance.
     *
     * @param string[] $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex(':');
    }

    /**
     * Parse a line and determine if it contains an emoji.
     *
     * If it does, then we do the necessary.
     */
    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        $previous = $cursor->peek(-1);
        if (null !== $previous && ' ' !== $previous) {
            return false;
        }
        $saved = $cursor->saveState();
        $cursor->advance();
        $handle = $cursor->match('/^[a-z0-9\+\-_]+:/');
        if (!$handle) {
            $cursor->restoreState($saved);

            return false;
        }
        $next = $cursor->peek(0);
        if (null !== $next && ' ' !== $next) {
            $cursor->restoreState($saved);

            return false;
        }
        $key = substr($handle, 0, -1);
        if (!array_key_exists($key, $this->map)) {
            $cursor->restoreState($saved);

            return false;
        }
        $inline = new Image($this->map[$key], $key);
        $inline->data['attributes'] = ['class' => 'emoji', 'data-emoji' => $key];
        $inlineContext->getContainer()->appendChild($inline);

        return true;
    }
}
