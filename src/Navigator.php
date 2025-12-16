<?php

declare(strict_types=1);

namespace webignition\SymfonyDomCrawlerNavigator;

use Facebook\WebDriver\WebDriverElement;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;
use webignition\DomElementIdentifier\InvalidJsonException;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidElementPositionException;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;
use webignition\SymfonyDomCrawlerNavigator\Exception\OverlyBroadLocatorException;
use webignition\SymfonyDomCrawlerNavigator\Exception\UnknownElementException;
use webignition\WebDriverElementCollection\RadioButtonCollection;
use webignition\WebDriverElementCollection\SelectOptionCollection;
use webignition\WebDriverElementCollection\WebDriverElementCollection;
use webignition\WebDriverElementCollection\WebDriverElementCollectionInterface;

class Navigator
{
    private Crawler $crawler;
    private CrawlerFactory $crawlerFactory;

    public function __construct(Crawler $crawler, CrawlerFactory $crawlerFactory)
    {
        $this->crawler = $crawler;
        $this->crawlerFactory = $crawlerFactory;
    }

    public static function create(Crawler $crawler): Navigator
    {
        return new Navigator(
            $crawler,
            CrawlerFactory::create()
        );
    }

    public function setCrawler(Crawler $crawler): void
    {
        $this->crawler = $crawler;
    }

    /**
     * @throws InvalidElementPositionException
     * @throws InvalidLocatorException
     * @throws UnknownElementException
     * @throws InvalidJsonException
     */
    public function findFromJson(string $json): WebDriverElementCollectionInterface
    {
        return $this->find(ElementIdentifier::fromJson($json));
    }

    /**
     * @throws InvalidElementPositionException
     * @throws InvalidJsonException
     * @throws InvalidLocatorException
     * @throws OverlyBroadLocatorException
     * @throws UnknownElementException
     */
    public function findOneFromJson(string $json): WebDriverElement
    {
        return $this->findOne(ElementIdentifier::fromJson($json));
    }

    /**
     * @throws InvalidJsonException
     * @throws InvalidLocatorException
     */
    public function hasFromJson(string $json): bool
    {
        return $this->has(ElementIdentifier::fromJson($json));
    }

    /**
     * @throws InvalidJsonException
     * @throws InvalidLocatorException
     */
    public function hasOneFromJson(string $json): bool
    {
        $examiner = function (WebDriverElementCollectionInterface $collection): bool {
            return 1 === count($collection);
        };

        return $this->examineCollectionCount(ElementIdentifier::fromJson($json), $examiner);
    }

    /**
     * @throws InvalidLocatorException
     */
    private function has(ElementIdentifierInterface $elementIdentifier): bool
    {
        $examiner = function (WebDriverElementCollectionInterface $collection): bool {
            return count($collection) > 0;
        };

        return $this->examineCollectionCount($elementIdentifier, $examiner);
    }

    /**
     * @throws InvalidElementPositionException
     * @throws UnknownElementException
     * @throws OverlyBroadLocatorException
     * @throws InvalidLocatorException
     */
    private function findOne(ElementIdentifierInterface $elementIdentifier): WebDriverElement
    {
        $collection = $this->find($elementIdentifier);

        if (1 === count($collection)) {
            $element = $collection->get(0);

            if ($element instanceof WebDriverElement) {
                return $element;
            }
        }

        throw new OverlyBroadLocatorException($elementIdentifier, $collection);
    }

    /**
     * @throws InvalidElementPositionException
     * @throws UnknownElementException
     * @throws InvalidLocatorException
     */
    private function find(ElementIdentifierInterface $elementIdentifier): WebDriverElementCollectionInterface
    {
        $scopeCrawler = $this->createScopeCrawler($elementIdentifier);

        $elementCrawler = $this->crawlerFactory->createElementCrawler($elementIdentifier, $scopeCrawler);

        $elements = [];

        foreach ($elementCrawler as $remoteWebElement) {
            $elements[] = $remoteWebElement;
        }

        if (RadioButtonCollection::is($elements)) {
            return new RadioButtonCollection($elements);
        }

        if (SelectOptionCollection::is($elements)) {
            return new SelectOptionCollection($elements);
        }

        return new WebDriverElementCollection($elements);
    }

    /**
     * @param callable(WebDriverElementCollectionInterface): bool $examiner
     *
     * @throws InvalidLocatorException
     */
    private function examineCollectionCount(ElementIdentifierInterface $elementIdentifier, callable $examiner): bool
    {
        try {
            $collection = $this->find($elementIdentifier);

            return $examiner($collection);
        } catch (InvalidElementPositionException|UnknownElementException $exception) {
            return false;
        }
    }

    /**
     * @throws InvalidElementPositionException
     * @throws UnknownElementException
     * @throws InvalidLocatorException
     */
    private function createScopeCrawler(ElementIdentifierInterface $elementIdentifier): Crawler
    {
        $scope = $elementIdentifier->getScope();

        $crawler = $this->crawler;
        foreach ($scope as $parentIdentifier) {
            $crawler = $this->crawlerFactory->createSingleElementCrawler($parentIdentifier, $crawler);
        }

        return $crawler;
    }
}
