<?php

declare(strict_types=1);

namespace webignition\SymfonyDomCrawlerNavigator\Tests\Functional;

use Facebook\WebDriver\WebDriverElement;
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
    /**
     * @dataProvider findSuccessDataProvider
     */
    public function testFindSuccess(ElementIdentifierInterface $elementIdentifier, callable $assertions): void
    {
        $crawler = self::$client->request('GET', '/basic.html');
        $navigator = Navigator::create($crawler);

        $element = $navigator->find($elementIdentifier);

        $assertions($element);
    }

    /**
     * @return array<mixed>
     */
    public function findSuccessDataProvider(): array
    {
        return [
            'first h1 with css selector' => [
                'elementIdentifier' => new ElementIdentifier('h1', 1),
                'assertions' => function (WebDriverElementCollection $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->get(0);
                    $this->assertInstanceOf(WebDriverElement::class, $element);

                    $this->assertSame('Hello', $element->getText());
                },
            ],
            'first h1 with xpath expression' => [
                'elementIdentifier' => new ElementIdentifier('//h1', 1),
                'assertions' => function (WebDriverElementCollection $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->get(0);
                    $this->assertInstanceOf(WebDriverElement::class, $element);

                    $this->assertSame('Hello', $element->getText());
                },
            ],
            'second h1 with css selector' => [
                'elementIdentifier' => new ElementIdentifier('h1', 2),
                'assertions' => function (WebDriverElementCollection $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->get(0);
                    $this->assertInstanceOf(WebDriverElement::class, $element);

                    $this->assertSame('Main', $element->getText());
                },
            ],
            'css-selector input scoped to css-selector second form' => [
                'elementIdentifier' => (new ElementIdentifier('input', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('form[action="/action2"]', 1)
                    ),
                'assertions' => function (WebDriverElementCollection $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->get(0);
                    $this->assertInstanceOf(WebDriverElement::class, $element);

                    $this->assertSame('input-2', $element->getAttribute('name'));
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
                    $this->assertCount(1, $collection);

                    $element = $collection->get(0);
                    $this->assertInstanceOf(WebDriverElement::class, $element);

                    $this->assertSame('two', $element->getText());
                },
            ],
            'css-selector input scoped to xpath-expression second form' => [
                'elementIdentifier' => (new ElementIdentifier('input', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('//form', 2)
                    ),
                'assertions' => function (WebDriverElementCollection $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->get(0);
                    $this->assertInstanceOf(WebDriverElement::class, $element);

                    $this->assertSame('input-2', $element->getAttribute('name'));
                },
            ],
            'radio group' => [
                'elementIdentifier' => new ElementIdentifier('[name="radio-group-name"]'),
                'assertions' => function (RadioButtonCollection $collection) {
                    $this->assertCount(3, $collection);

                    foreach ($collection as $elementIndex => $element) {
                        \assert(is_int($elementIndex));
                        $this->assertSame((string) ($elementIndex + 1), $element->getAttribute('value'));
                    }
                },
            ],
            'select options' => [
                'elementIdentifier' => new ElementIdentifier('select option'),
                'assertions' => function (SelectOptionCollection $collection) {
                    $this->assertCount(3, $collection);

                    foreach ($collection as $elementIndex => $element) {
                        \assert(is_int($elementIndex));
                        $this->assertSame((string) ($elementIndex + 1), $element->getAttribute('value'));
                    }
                },
            ],
        ];
    }

    /**
     * @dataProvider findOneSuccessDataProvider
     */
    public function testFindOneSuccess(ElementIdentifierInterface $elementIdentifier, callable $assertions): void
    {
        $crawler = self::$client->request('GET', '/basic.html');
        $navigator = Navigator::create($crawler);

        $element = $navigator->findOne($elementIdentifier);

        $assertions($element);
    }

    /**
     * @return array<mixed>
     */
    public function findOneSuccessDataProvider(): array
    {
        return [
            'first h1 with css selector' => [
                'elementIdentifier' => new ElementIdentifier('h1', 1),
                'assertions' => function (WebDriverElement $element) {
                    $this->assertSame('Hello', $element->getText());
                },
            ],
            'first h1 with xpath expression' => [
                'elementIdentifier' => new ElementIdentifier('//h1', 1),
                'assertions' => function (WebDriverElement $element) {
                    $this->assertSame('Hello', $element->getText());
                },
            ],
            'second h1 with css selector' => [
                'elementIdentifier' => new ElementIdentifier('h1', 2),
                'assertions' => function (WebDriverElement $element) {
                    $this->assertSame('Main', $element->getText());
                },
            ],
            'css-selector input scoped to css-selector second form' => [
                'elementIdentifier' => (new ElementIdentifier('input', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('form[action="/action2"]', 1)
                    ),
                'assertions' => function (WebDriverElement $element) {
                    $this->assertSame('input-2', $element->getAttribute('name'));
                },
            ],
            'css-selector input scoped to xpath-expression second form' => [
                'elementIdentifier' => (new ElementIdentifier('input', 1))
                    ->withParentIdentifier(
                        new ElementIdentifier('//form', 2)
                    ),
                'assertions' => function (WebDriverElement $element) {
                    $this->assertSame('input-2', $element->getAttribute('name'));
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
                    $this->assertSame('two', $element->getText());
                },
            ],
        ];
    }

    /**
     * @dataProvider hasSuccessDataProvider
     */
    public function testHasSuccess(ElementIdentifierInterface $elementIdentifier, bool $expectedHas): void
    {
        $crawler = self::$client->request('GET', '/basic.html');
        $navigator = Navigator::create($crawler);

        $this->assertSame($expectedHas, $navigator->has($elementIdentifier));
    }

    /**
     * @return array<mixed>
     */
    public function hasSuccessDataProvider(): array
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

    /**
     * @dataProvider hasOneSuccessDataProvider
     */
    public function testHasOneSuccess(ElementIdentifierInterface $elementIdentifier, bool $expectedHas): void
    {
        $crawler = self::$client->request('GET', '/basic.html');
        $navigator = Navigator::create($crawler);

        $this->assertSame($expectedHas, $navigator->hasOne($elementIdentifier));
    }

    /**
     * @return array<mixed>
     */
    public function hasOneSuccessDataProvider(): array
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

    /**
     * @dataProvider findThrowsUnknownElementExceptionDataProvider
     */
    public function testFindThrowsUnknownElementException(
        ElementIdentifierInterface $elementIdentifier,
        ElementIdentifierInterface $expectedExceptionElementIdentifier
    ): void {
        $crawler = self::$client->request('GET', '/basic.html');
        $navigator = Navigator::create($crawler);

        try {
            $navigator->find($elementIdentifier);
            $this->fail('UnknownElementException not thrown');
        } catch (UnknownElementException $unknownElementException) {
            $this->assertEquals($expectedExceptionElementIdentifier, $unknownElementException->getElementIdentifier());
        }
    }

    /**
     * @return array<mixed>
     */
    public function findThrowsUnknownElementExceptionDataProvider(): array
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
                'scopeLocator' => new ElementIdentifier('.does-not-exist', 1),
                'expectedExceptionElementIdentifier' => new ElementIdentifier('.does-not-exist', 1),
            ],
        ];
    }

    /**
     * @dataProvider findThrowsInvalidPositionExceptionDataProvider
     */
    public function testFindThrowsInvalidPositionException(string $cssLocator, int $ordinalPosition): void
    {
        $crawler = self::$client->request('GET', '/basic.html');
        $navigator = Navigator::create($crawler);

        $elementLocator = new ElementIdentifier($cssLocator, $ordinalPosition);

        try {
            $navigator->find($elementLocator);
            $this->fail('InvalidPositionExceptionInterface instance not thrown');
        } catch (InvalidElementPositionException $invalidElementPositionException) {
            $this->assertSame($elementLocator, $invalidElementPositionException->getElementIdentifier());

            $previousException = $invalidElementPositionException->getPrevious();
            $this->assertInstanceOf(InvalidPositionExceptionInterface::class, $previousException);

            $this->assertSame($previousException->getOrdinalPosition(), $elementLocator->getOrdinalPosition());
        }
    }

    /**
     * @return array<mixed>
     */
    public function findThrowsInvalidPositionExceptionDataProvider(): array
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

    /**
     * @dataProvider findOneThrowsOverlyBroadLocatorExceptionDataProvider
     */
    public function testFindOneThrowsOverlyBroadLocatorException(
        ElementIdentifierInterface $elementIdentifier,
        int $expectedCollectionCount
    ): void {
        $crawler = self::$client->request('GET', '/basic.html');
        $navigator = Navigator::create($crawler);

        try {
            $navigator->findOne($elementIdentifier);
            $this->fail('OverlyBroadLocatorException not thrown');
        } catch (OverlyBroadLocatorException $overlyBroadLocatorException) {
            $this->assertCount($expectedCollectionCount, $overlyBroadLocatorException->getCollection());
        }
    }

    /**
     * @return array<mixed>
     */
    public function findOneThrowsOverlyBroadLocatorExceptionDataProvider(): array
    {
        return [
            'collection locator overly broad, no scope' => [
                'elementIdentifier' => new ElementIdentifier('h1'),
                'expectedCollectionCount' => 2,
            ],
        ];
    }
}
