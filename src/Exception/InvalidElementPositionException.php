<?php

declare(strict_types=1);

namespace webignition\SymfonyDomCrawlerNavigator\Exception;

use SmartAssert\DomIdentifier\ElementIdentifierInterface;

class InvalidElementPositionException extends AbstractElementException
{
    public function __construct(
        ElementIdentifierInterface $elementIdentifier,
        InvalidPositionExceptionInterface $invalidPositionException
    ) {
        $message = sprintf(
            'Invalid position "%d" for locator "%s"',
            $elementIdentifier->getOrdinalPosition(),
            $elementIdentifier->getLocator()
        );

        parent::__construct($elementIdentifier, $message, 0, $invalidPositionException);
    }
}
