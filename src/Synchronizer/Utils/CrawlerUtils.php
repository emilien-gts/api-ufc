<?php

namespace App\Synchronizer\Utils;

use Symfony\Component\DomCrawler\Link;

class CrawlerUtils
{
    public static function getTokenFromDomLink(Link $domLink): string
    {
        $uri = $domLink->getUri();
        $segments = \explode('/', $uri);

        return \end($segments);
    }

    /**
     * @param \DOMNode[] $iterable
     */
    public static function getFirstNotEmptyDomTextContentFromIterable(iterable $iterable): ?string
    {
        foreach ($iterable as $element) {
            if ($element instanceof \DOMText && !empty(\trim($element->textContent))) {
                return \trim($element->textContent);
            }
        }

        return null;
    }
}
