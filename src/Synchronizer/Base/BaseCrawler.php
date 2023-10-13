<?php

namespace App\Synchronizer\Base;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;
use Symfony\Component\HttpFoundation\Response;

class BaseCrawler
{
    private Crawler $crawler;

    public function __construct()
    {
        $this->crawler = new Crawler();
    }

    /**
     * @throws \Exception
     */
    public function init(string $url): void
    {
        $this->reset(); // Prevent from previous state
        $source = \file_get_contents($url);
        if (false === $source) {
            throw new \Exception('Cannot get content from URL', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->crawler->addHtmlContent($source);
    }

    public function reset(): void
    {
        $this->crawler = new Crawler();
    }

    /**
     * @return Link[]
     */
    public function getLinks(string $selector): array
    {
        return $this->crawler->filter($selector)->links();
    }

    public function getDomElement(string $selector): ?\DOMNode
    {
        return $this->crawler->filter($selector)->getNode(0);
    }
}
