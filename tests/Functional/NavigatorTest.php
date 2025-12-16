<?php

declare(strict_types=1);

namespace webignition\SymfonyDomCrawlerNavigator\Tests\Functional;

use Facebook\WebDriver\WebDriverElement;
use PHPUnit\Framework\Attributes\DataProvider;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidElementPositionException;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidPositionExceptionInterface;
use webignition\SymfonyDomCrawlerNavigator\Exception\OverlyBroadLocatorException;
use webignition\SymfonyDomCrawlerNavigator\Exception\UnknownElementException;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\WebDriverElementCollection\RadioButtonCollection;
use webignition\WebDriverElementCollection\SelectOptionCollection;
use webignition\WebDriverElementCollection\WebDriverElementCollection;

class NavigatorTest extends AbstractBrowserTestCase
{
    #[DataProvider('findSuccessDataProvider')]
    public function testFindSuccess(ElementIdentifierInterface $elementIdentifier, callable $assertions): void
    {
        $crawler = self::$client->request('GET', '/basic.html');
        $navigator = Navigator::create($crawler);

        $element = $navigator->find((string) json_encode($elementIdentifier));

        $assertions($element);
    }

    /**
     * @return array<mixed>
     */
    public static function findSuccessDataProvider(): array
    {
        return [
            'first h1 with css selector' => [
                'elementIdentifier' => new ElementIdentifier('h1', 1),
                'assertions' => function (WebDriverElementCollection $collection) {
                    self::assertCount(1, $collection);

                    $element = $collection->get(0);
                    self::assertInstanceOf(WebDriverElement::class, $element);

                    self::assertSame('Hello', $element->getText());
                },
            ],
            'first h1 with xpath expression' => [
                'elementIdentifier' => new ElementIdentifier('//h1', 1),
                'assertions' => function (WebDriverElementCollection $collection) {
                    self::assertCount(1, $collection);

                    $element = $collection->get(0);
                    self::assertInstanceOf(WebDriverElement::class, $element);

                    self::assertSame('Hello', $element->getText());
                },
            ],
            'second h1 with css selector' => [
                'elementIdentifier' => new ElementIdentifier('h1', 2),
                'assertions' => function (WebDriverElementCollection $collection) {
                    self::assertCount(1, $collection);

                    $element = $collection->get(0);
                    self::assertInstanceOf(WebDriverElement::class, $element);

                    self::assertSame('Main', $element->getText());
                },
            ],
            'css-selector input scoped to css-selector second form' => [
                'elementIdentifier' => (new ElementIdentifier('input', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('form[action="/action2"]', 1)
                    ),
                'assertions' => function (WebDriverElementCollection $collection) {
                    self::assertCount(1, $collection);

                    $element = $collection->get(0);
                    self::assertInstanceOf(WebDriverElement::class, $element);

                    self::assertSame('input-2', $element->getAttribute('name'));
                },
            ],
            'deep nested descendant' => [
                'elementIdentifier' => (new ElementIdentifier('option[value="2"]'))
                    ->withParentIdentifier(
                        (new ElementIdentifier('form[action="/action2"]', 1))
                            ->withParentIdentifier(
                                new ElementIdentifier('body')
                            )
                    ),
                'assertions' => function (SelectOptionCollection $collection) {
                    self::assertCount(1, $collection);

                    $element = $collection->get(0);
                    self::assertInstanceOf(WebDriverElement::class, $element);

                    self::assertSame('two', $element->getText());
                },
            ],
            'css-selector input scoped to xpath-expression second form' => [
                'elementIdentifier' => (new ElementIdentifier('input', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('//form', 2)
                    ),
                'assertions' => function (WebDriverElementCollection $collection) {
                    self::assertCount(1, $collection);

                    $element = $collection->get(0);
                    self::assertInstanceOf(WebDriverElement::class, $element);

                    self::assertSame('input-2', $element->getAttribute('name'));
                },
            ],
            'radio group' => [
                'elementIdentifier' => new ElementIdentifier('[name="radio-group-name"]'),
                'assertions' => function (RadioButtonCollection $collection) {
                    self::assertCount(3, $collection);

                    foreach ($collection as $elementIndex => $element) {
                        \assert(is_int($elementIndex));
                        self::assertSame((string) ($elementIndex + 1), $element->getAttribute('value'));
                    }
                },
            ],
            'select options' => [
                'elementIdentifier' => new ElementIdentifier('select option'),
                'assertions' => function (SelectOptionCollection $collection) {
                    self::assertCount(3, $collection);

                    foreach ($collection as $elementIndex => $element) {
                        \assert(is_int($elementIndex));
                        self::assertSame((string) ($elementIndex + 1), $element->getAttribute('value'));
                    }
                },
            ],
        ];
    }

    #[DataProvider('findOneSuccessDataProvider')]
    public function testFindOneSuccess(
        ElementIdentifierInterface $elementIdentifier,
        callable $assertions
    ): void {
        $crawler = self::$client->request('GET', '/basic.html');
        $navigator = Navigator::create($crawler);

        $element = $navigator->findOne((string) json_encode($elementIdentifier));

        $assertions($element);
    }

    /**
     * @return array<mixed>
     */
    public static function findOneSuccessDataProvider(): array
    {
        return [
            'first h1 with css selector' => [
                'elementIdentifier' => new ElementIdentifier('h1', 1),
                'assertions' => function (WebDriverElement $element) {
                    self::assertSame('Hello', $element->getText());
                },
            ],
            'first h1 with xpath expression' => [
                'elementIdentifier' => new ElementIdentifier('//h1', 1),
                'assertions' => function (WebDriverElement $element) {
                    self::assertSame('Hello', $element->getText());
                },
            ],
            'second h1 with css selector' => [
                'elementIdentifier' => new ElementIdentifier('h1', 2),
                'assertions' => function (WebDriverElement $element) {
                    self::assertSame('Main', $element->getText());
                },
            ],
            'css-selector input scoped to css-selector second form' => [
                'elementIdentifier' => (new ElementIdentifier('input', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('form[action="/action2"]', 1)
                    ),
                'assertions' => function (WebDriverElement $element) {
                    self::assertSame('input-2', $element->getAttribute('name'));
                },
            ],
            'css-selector input scoped to xpath-expression second form' => [
                'elementIdentifier' => (new ElementIdentifier('input', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('//form', 2)
                    ),
                'assertions' => function (WebDriverElement $element) {
                    self::assertSame('input-2', $element->getAttribute('name'));
                },
            ],
            'deep nested descendant' => [
                'elementIdentifier' => (new ElementIdentifier('option[value="2"]'))
                    ->withParentIdentifier(
                        (new ElementIdentifier('form[action="/action2"]', 1))
                            ->withParentIdentifier(
                                new ElementIdentifier('body')
                            )
                    ),
                'assertions' => function (WebDriverElement $element) {
                    self::assertSame('two', $element->getText());
                },
            ],
        ];
    }

    #[DataProvider('hasSuccessDataProvider')]
    public function testHasSuccess(ElementIdentifierInterface $elementIdentifier, bool $expectedHas): void
    {
        $crawler = self::$client->request('GET', '/basic.html');
        $navigator = Navigator::create($crawler);

        self::assertSame($expectedHas, $navigator->has((string) json_encode($elementIdentifier)));
    }

    /**
     * @return array<mixed>
     */
    public static function hasSuccessDataProvider(): array
    {
        return [
            'existent element without scope' => [
                'elementIdentifier' => new ElementIdentifier('h1', 1),
                'expectedHas' => true,
            ],
            'existent collection without scope' => [
                'elementIdentifier' => new ElementIdentifier('[name="radio-group-name"]', 1),
                'expectedHas' => true,
            ],
            'existent element inside scope' => [
                'elementIdentifier' => (new ElementIdentifier('input', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('form[action="/action2"]', 1)
                    ),
                'expectedHas' => true,
            ],
            'existent scope contains non-existent element' => [
                'elementIdentifier' => (new ElementIdentifier('.does-not-exist', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('main', 1)
                    ),
                'expectedHas' => false,
            ],
            'non-existent scope' => [
                'elementIdentifier' => (new ElementIdentifier('input', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('.does-not-exist', 1)
                    ),
                'expectedHas' => false,
            ],
            'deep nested descendant' => [
                'elementIdentifier' => (new ElementIdentifier('option[value="2"]'))
                    ->withParentIdentifier(
                        (new ElementIdentifier('form[action="/action2"]', 1))
                            ->withParentIdentifier(
                                new ElementIdentifier('body')
                            )
                    ),
                'expectedHas' => true,
            ],
        ];
    }

    #[DataProvider('hasOneSuccessDataProvider')]
    public function testHasOneSuccess(ElementIdentifierInterface $elementIdentifier, bool $expectedHas): void
    {
        $crawler = self::$client->request('GET', '/basic.html');
        $navigator = Navigator::create($crawler);

        self::assertSame($expectedHas, $navigator->hasOne((string) json_encode($elementIdentifier)));
    }

    /**
     * @return array<mixed>
     */
    public static function hasOneSuccessDataProvider(): array
    {
        return [
            'existent element without scope' => [
                'elementIdentifier' => new ElementIdentifier('h1', 1),
                'expectedHas' => true,
            ],
            'existent element inside scope' => [
                'elementIdentifier' => (new ElementIdentifier('input', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('form[action="/action2"]', 1)
                    ),
                'expectedHas' => true,
            ],
            'existent scope contains non-existent element' => [
                'elementIdentifier' => (new ElementIdentifier('.does-not-exist', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('main', 1)
                    ),
                'expectedHas' => false,
            ],
            'non-existent scope' => [
                'elementIdentifier' => (new ElementIdentifier('input', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('.does-not-exist', 1)
                    ),
                'expectedHas' => false,
            ],
            'deep nested descendant' => [
                'elementIdentifier' => (new ElementIdentifier('option[value="2"]'))
                    ->withParentIdentifier(
                        (new ElementIdentifier('form[action="/action2"]', 1))
                            ->withParentIdentifier(
                                new ElementIdentifier('body')
                            )
                    ),
                'expectedHas' => true,
            ],
        ];
    }

    #[DataProvider('findThrowsUnknownElementExceptionDataProvider')]
    public function testFindThrowsUnknownElementException(
        ElementIdentifierInterface $elementIdentifier,
        ElementIdentifierInterface $expectedExceptionElementIdentifier
    ): void {
        $crawler = self::$client->request('GET', '/basic.html');
        $navigator = Navigator::create($crawler);

        try {
            $navigator->find((string) json_encode($elementIdentifier));
            $this->fail('UnknownElementException not thrown');
        } catch (UnknownElementException $unknownElementException) {
            self::assertEquals($expectedExceptionElementIdentifier, $unknownElementException->getElementIdentifier());
        }
    }

    /**
     * @return array<mixed>
     */
    public static function findThrowsUnknownElementExceptionDataProvider(): array
    {
        return [
            'identifier refers to unknown element, no scope' => [
                'elementIdentifier' => new ElementIdentifier('.does-not-exist', 1),
                'expectedExceptionElementIdentifier' => new ElementIdentifier('.does-not-exist', 1),
            ],
            'identifier refers to unknown element, with parent scope' => [
                'elementIdentifier' => (new ElementIdentifier('.does-not-exist', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('main', 1)
                    ),
                'expectedExceptionElementIdentifier' => (new ElementIdentifier('.does-not-exist', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('main', 1)
                    ),
            ],
            'parent refers to unknown element' => [
                'elementIdentifier' => (new ElementIdentifier('input', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('.does-not-exist', 1)
                    ),
                'expectedExceptionElementIdentifier' => new ElementIdentifier('.does-not-exist', 1),
            ],
        ];
    }

    #[DataProvider('findThrowsInvalidPositionExceptionDataProvider')]
    public function testFindThrowsInvalidPositionException(string $cssLocator, int $ordinalPosition): void
    {
        $crawler = self::$client->request('GET', '/basic.html');
        $navigator = Navigator::create($crawler);

        $elementLocator = new ElementIdentifier($cssLocator, $ordinalPosition);

        try {
            $navigator->find((string) json_encode($elementLocator));
            $this->fail('InvalidPositionExceptionInterface instance not thrown');
        } catch (InvalidElementPositionException $invalidElementPositionException) {
            self::assertEquals($elementLocator, $invalidElementPositionException->getElementIdentifier());

            $previousException = $invalidElementPositionException->getPrevious();
            self::assertInstanceOf(InvalidPositionExceptionInterface::class, $previousException);

            self::assertSame($previousException->getOrdinalPosition(), $elementLocator->getOrdinalPosition());
        }
    }

    /**
     * @return array<mixed>
     */
    public static function findThrowsInvalidPositionExceptionDataProvider(): array
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

    #[DataProvider('findOneThrowsOverlyBroadLocatorExceptionDataProvider')]
    public function testFindOneThrowsOverlyBroadLocatorException(
        ElementIdentifierInterface $elementIdentifier,
        int $expectedCollectionCount
    ): void {
        $crawler = self::$client->request('GET', '/basic.html');
        $navigator = Navigator::create($crawler);

        try {
            $navigator->findOne((string) json_encode($elementIdentifier));
            $this->fail('OverlyBroadLocatorException not thrown');
        } catch (OverlyBroadLocatorException $overlyBroadLocatorException) {
            self::assertCount($expectedCollectionCount, $overlyBroadLocatorException->getCollection());
        }
    }

    /**
     * @return array<mixed>
     */
    public static function findOneThrowsOverlyBroadLocatorExceptionDataProvider(): array
    {
        return [
            'collection locator overly broad, no scope' => [
                'elementIdentifier' => new ElementIdentifier('h1'),
                'expectedCollectionCount' => 2,
            ],
        ];
    }
}
