<?php

declare(strict_types=1);

namespace webignition\SymfonyDomCrawlerNavigator\Exception;

use Facebook\WebDriver\Exception\InvalidSelectorException;
use webignition\DomElementIdentifier\Enum\Type;

interface InvalidLocatorExceptionInterface
{
    public function getInvalidSelectorException(): InvalidSelectorException;

    /**
     * @return array{'locator': string, 'type': ?value-of<Type>}
     */
    public function getContext(): array;
}
