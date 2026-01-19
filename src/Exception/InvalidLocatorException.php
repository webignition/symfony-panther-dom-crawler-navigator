<?php

declare(strict_types=1);

namespace webignition\SymfonyDomCrawlerNavigator\Exception;

use Facebook\WebDriver\Exception\InvalidSelectorException;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class InvalidLocatorException extends AbstractElementException implements InvalidLocatorExceptionInterface
{
    private InvalidSelectorException $invalidSelectorException;

    public function __construct(
        ElementIdentifierInterface $elementIdentifier,
        InvalidSelectorException $invalidSelectorException
    ) {
        $message = sprintf(
            'Invalid %s locator %s',
            ($elementIdentifier->isCssSelector()) ? 'CSS selector' : 'XPath expression',
            $elementIdentifier->getLocator()
        );

        parent::__construct($elementIdentifier, $message, 0, $invalidSelectorException);

        $this->invalidSelectorException = $invalidSelectorException;
    }

    public function getInvalidSelectorException(): InvalidSelectorException
    {
        return $this->invalidSelectorException;
    }

    public function getContext(): array
    {
        return [
            'locator' => $this->getElementIdentifier()->getLocator(),
            'type' => $this->getElementIdentifier()->getType()?->value,
        ];
    }
}
