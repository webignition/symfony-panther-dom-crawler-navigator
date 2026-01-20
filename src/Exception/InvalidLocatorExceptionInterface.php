<?php

declare(strict_types=1);

namespace webignition\SymfonyDomCrawlerNavigator\Exception;

use Facebook\WebDriver\Exception\InvalidSelectorException;
use SmartAssert\DomIdentifier\Enum\Type;

interface InvalidLocatorExceptionInterface
{
    public function getInvalidSelectorException(): InvalidSelectorException;

    /**
     * @return array{'locator': string, 'type': ?value-of<Type>}
     */
    public function getContext(): array;

    public function getLocator(): string;

    public function getTypeString(): ?string;
}
