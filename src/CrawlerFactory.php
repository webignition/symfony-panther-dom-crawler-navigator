<?php

declare(strict_types=1);

namespace webignition\SymfonyDomCrawlerNavigator;

use Facebook\WebDriver\Exception\InvalidSelectorException;
use SmartAssert\DomIdentifier\ElementIdentifierInterface;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidElementPositionException;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidPositionExceptionInterface;
use webignition\SymfonyDomCrawlerNavigator\Exception\UnknownElementException;

class CrawlerFactory
{
    public const DEFAULT_ORDINAL_POSITION = 1;

    private CollectionPositionFinder $collectionPositionFinder;

    public function __construct(CollectionPositionFinder $collectionPositionFinder)
    {
        $this->collectionPositionFinder = $collectionPositionFinder;
    }

    public static function create(): CrawlerFactory
    {
        return new CrawlerFactory(
            new CollectionPositionFinder()
        );
    }

    /**
     * @throws InvalidElementPositionException
     * @throws InvalidLocatorException
     * @throws UnknownElementException
     */
    public function createElementCrawler(ElementIdentifierInterface $elementIdentifier, Crawler $scope): Crawler
    {
        $collection = $this->createFilteredCrawler($elementIdentifier, $scope);

        $ordinalPosition = $elementIdentifier->getOrdinalPosition();
        if (null === $ordinalPosition) {
            return $collection;
        }

        return $this->createSingleElementCrawler($elementIdentifier, $scope);
    }

    /**
     * @throws InvalidElementPositionException
     * @throws InvalidLocatorException
     * @throws UnknownElementException
     */
    public function createSingleElementCrawler(ElementIdentifierInterface $elementIdentifier, Crawler $scope): Crawler
    {
        $collection = $this->createFilteredCrawler($elementIdentifier, $scope);

        try {
            $crawlerPosition = $this->collectionPositionFinder->find(
                $elementIdentifier->getOrdinalPosition() ?? self::DEFAULT_ORDINAL_POSITION,
                count($collection)
            );

            return $collection->eq($crawlerPosition);
        } catch (InvalidPositionExceptionInterface $invalidPositionException) {
            throw new InvalidElementPositionException($elementIdentifier, $invalidPositionException);
        }
    }

    /**
     * @throws InvalidLocatorException
     * @throws UnknownElementException
     */
    private function createFilteredCrawler(ElementIdentifierInterface $elementIdentifier, Crawler $scope): Crawler
    {
        $locator = $elementIdentifier->getLocator();

        try {
            $collection = $elementIdentifier->isCssSelector()
                ? $scope->filter($locator)
                : $scope->filterXPath($locator);
        } catch (InvalidSelectorException $invalidSelectorException) {
            throw new InvalidLocatorException($elementIdentifier, $invalidSelectorException);
        }

        if (0 === count($collection)) {
            throw new UnknownElementException($elementIdentifier);
        }

        return $collection;
    }
}
