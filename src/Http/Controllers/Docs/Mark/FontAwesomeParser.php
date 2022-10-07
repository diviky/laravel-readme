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

use League\CommonMark\Extension\CommonMark\Node\Inline\HtmlInline;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

/**
 * This is the fontawesome icon parser class.
 *
 * @author sankar <sankar.suda@gmail.com>
 */
class FontAwesomeParser implements InlineParserInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex(':');
    }

    /**
     * Parse a line and determine if it contains an icon.
     *
     * If it does, then we do the necessary.
     *
     * @param \League\CommonMark\InlineParserContext $inlineContext
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
        $handle = $cursor->match('/^[a-z0-9\-_]+:/');
        if (!$handle) {
            $cursor->restoreState($saved);

            return false;
        }

        $next = $cursor->peek(0);
        if (null !== $next && ' ' !== $next && "\n" !== $next) {
            $cursor->restoreState($saved);

            return false;
        }

        $key = substr($handle, 0, 3);
        if ('fa-' !== $key) {
            $cursor->restoreState($saved);

            return false;
        }

        $icon = substr($handle, 0, -1);
        $fa = '<i class="fa ' . $icon . '"></i>';

        $inline = new HtmlInline($fa);
        $inlineContext->getContainer()->appendChild($inline);

        return true;
    }
}
