<?php

declare(strict_types=1);

namespace webignition\SymfonyDomCrawlerNavigator\Exception;

use SmartAssert\DomIdentifier\ElementIdentifierInterface;

class UnknownElementException extends AbstractElementException
{
    public function __construct(ElementIdentifierInterface $elementIdentifier)
    {
        parent::__construct($elementIdentifier, 'Unknown element "' . $elementIdentifier->getLocator() . '"');
    }
}
