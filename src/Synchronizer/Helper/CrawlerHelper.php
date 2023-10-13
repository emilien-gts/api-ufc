<?php

namespace App\Synchronizer\Helper;

use Symfony\Component\DomCrawler\Link;

class CrawlerHelper
{
    public function getTokenFromDomLink(Link $domLink): string
    {
        $uri = $domLink->getUri();
        $segments = \explode('/', $uri);

        return \end($segments);
    }
}
