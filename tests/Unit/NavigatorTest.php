<?php

declare(strict_types=1);

namespace webignition\SymfonyDomCrawlerNavigator\Tests\Unit;

use Facebook\WebDriver\WebDriver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\SymfonyDomCrawlerNavigator\CrawlerFactory;
use webignition\SymfonyDomCrawlerNavigator\Navigator;

class NavigatorTest extends TestCase
{
    public function testSetCrawler(): void
    {
        $crawler = new Crawler([], \Mockery::mock(WebDriver::class));
        $crawlerFactory = \Mockery::mock(CrawlerFactory::class);

        $navigator = new Navigator($crawler, $crawlerFactory);

        $reflector = new \ReflectionObject($navigator);
        $property = $reflector->getProperty('crawler');
        $property->setAccessible(true);

        self::assertSame($property->getValue($navigator), $crawler);

        $newCrawler = new Crawler([], \Mockery::mock(WebDriver::class));
        $navigator->setCrawler($newCrawler);
        self::assertSame($property->getValue($navigator), $newCrawler);
    }
}
