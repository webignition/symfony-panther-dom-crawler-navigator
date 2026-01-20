<?php

declare(strict_types=1);

namespace webignition\SymfonyDomCrawlerNavigator\Tests\Functional;

use Facebook\WebDriver\WebDriverElement;
use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\DomIdentifier\ElementIdentifier;
use SmartAssert\DomIdentifier\ElementIdentifierInterface;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\SymfonyDomCrawlerNavigator\CrawlerFactory;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidElementPositionException;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidPositionExceptionInterface;
use webignition\SymfonyDomCrawlerNavigator\Exception\UnknownElementException;

class CrawlerFactoryTest extends AbstractBrowserTestCase
{
    #[DataProvider('createElementCrawlerSuccessDataProvider')]
    public function testCreateElementCrawlerSuccess(
        ElementIdentifierInterface $elementIdentifier,
        callable $assertions
    ): void {
        $crawler = self::$client->request('GET', '/basic.html');

        $crawlerFactory = CrawlerFactory::create();

        $elementCrawler = $crawlerFactory->createElementCrawler($elementIdentifier, $crawler);

        $assertions($elementCrawler);
    }

    /**
     * @return array<mixed>
     */
    public static function createElementCrawlerSuccessDataProvider(): array
    {
        return [
            'first h1 with css selector, position null' => [
                'elementIdentifier' => new ElementIdentifier('h1'),
                'assertions' => function (Crawler $crawler) {
                    self::assertCount(2, $crawler);

                    $expectedElementGetText = [
                        'Hello',
                        'Main',
                    ];

                    /** @var WebDriverElement $element */
                    foreach ($crawler as $index => $element) {
                        self::assertSame($expectedElementGetText[$index], $element->getText());
                    }
                },
            ],
            'first h1 with css selector, position 1' => [
                'elementIdentifier' => new ElementIdentifier('h1', 1),
                'assertions' => function (Crawler $crawler) {
                    self::assertCount(1, $crawler);
                    self::assertSame('Hello', $crawler->getText());
                },
            ],
            'first h1 with xpath expression' => [
                'elementIdentifier' => new ElementIdentifier('//h1', 1),
                'assertions' => function (Crawler $crawler) {
                    self::assertCount(1, $crawler);
                    self::assertSame('Hello', $crawler->getText());
                },
            ],
            'second h1 with css selector' => [
                'elementIdentifier' => new ElementIdentifier('h1', 2),
                'assertions' => function (Crawler $crawler) {
                    self::assertCount(1, $crawler);
                    self::assertSame('Main', $crawler->getText());
                },
            ],
        ];
    }

    #[DataProvider('createSingleElementCrawlerSuccessDataProvider')]
    public function testCreateSingleElementCrawlerSuccess(
        ElementIdentifierInterface $elementIdentifier,
        callable $assertions
    ): void {
        $crawler = self::$client->request('GET', '/basic.html');
        $crawlerFactory = CrawlerFactory::create();

        $elementCrawler = $crawlerFactory->createSingleElementCrawler($elementIdentifier, $crawler);
        self::assertCount(1, $elementCrawler);

        $assertions($elementCrawler);
    }

    /**
     * @return array<mixed>
     */
    public static function createSingleElementCrawlerSuccessDataProvider(): array
    {
        return [
            'first h1 with css selector, position null' => [
                'elementIdentifier' => new ElementIdentifier('h1'),
                'assertions' => function (Crawler $crawler) {
                    self::assertSame('Hello', $crawler->getText());
                },
            ],
            'first h1 with css selector, position 1' => [
                'elementIdentifier' => new ElementIdentifier('h1', 1),
                'assertions' => function (Crawler $crawler) {
                    self::assertSame('Hello', $crawler->getText());
                },
            ],
            'second h1 with css selector, position 2' => [
                'elementIdentifier' => new ElementIdentifier('h1', 2),
                'assertions' => function (Crawler $crawler) {
                    self::assertSame('Main', $crawler->getText());
                },
            ],
        ];
    }

    public function testCreateElementCrawlerThrowsUnknownElementException(): void
    {
        $crawler = self::$client->request('GET', '/basic.html');
        $crawlerFactory = CrawlerFactory::create();

        $elementIdentifier = new ElementIdentifier('.does-not-exist', 1);

        try {
            $crawlerFactory->createElementCrawler($elementIdentifier, $crawler);
            $this->fail('UnknownElementException not thrown');
        } catch (UnknownElementException $unknownElementException) {
            self::assertSame($elementIdentifier, $unknownElementException->getElementIdentifier());
        }
    }

    #[DataProvider('createElementCrawlerThrowsInvalidElementPositionDataProvider')]
    public function testCreateElementCrawlerThrowsInvalidElementPositionException(
        string $cssLocator,
        int $ordinalPosition
    ): void {
        $crawler = self::$client->request('GET', '/basic.html');
        $crawlerFactory = CrawlerFactory::create();

        $elementIdentifier = new ElementIdentifier($cssLocator, $ordinalPosition);

        try {
            $crawlerFactory->createElementCrawler($elementIdentifier, $crawler);
            $this->fail('InvalidPositionExceptionInterface instance not thrown');
        } catch (InvalidElementPositionException $invalidElementPositionException) {
            self::assertSame($elementIdentifier, $invalidElementPositionException->getElementIdentifier());

            $previousException = $invalidElementPositionException->getPrevious();
            self::assertInstanceOf(InvalidPositionExceptionInterface::class, $previousException);

            self::assertSame($previousException->getOrdinalPosition(), $elementIdentifier->getOrdinalPosition());
        }
    }

    /**
     * @return array<mixed>
     */
    public static function createElementCrawlerThrowsInvalidElementPositionDataProvider(): array
    {
        return [
            'ordinalPosition zero, collection count non-zero' => [
                'cssLocator' => 'h1',
                'ordinalPosition' => 0,
            ],
            'ordinalPosition greater than collection count' => [
                'cssLocator' => 'h1',
                'ordinalPosition' => 3,
            ],
            'ordinalPosition less than negative collection count' => [
                'cssLocator' => 'h1',
                'ordinalPosition' => -3,
            ],
        ];
    }

    #[DataProvider('createElementCrawlerThrowsInvalidLocatorExceptionDataProvider')]
    public function testCreateElementCrawlerThrowsInvalidLocatorException(string $locator): void
    {
        $crawler = self::$client->request('GET', '/index.html');
        $crawlerFactory = CrawlerFactory::create();

        $elementIdentifier = new ElementIdentifier($locator);

        try {
            $crawlerFactory->createElementCrawler($elementIdentifier, $crawler);
            $this->fail('InvalidLocatorException instance not thrown');
        } catch (InvalidLocatorException $invalidLocatorException) {
            self::assertSame($elementIdentifier, $invalidLocatorException->getElementIdentifier());
        }
    }

    /**
     * @return array<mixed>
     */
    public static function createElementCrawlerThrowsInvalidLocatorExceptionDataProvider(): array
    {
        return [
            'invalid CSS selector' => [
                'locator' => 'a[href=/basic.html]',
            ],
            'invalid XPath expression' => [
                'locator' => 'a[href=/basic.html]',
            ],
        ];
    }
}
