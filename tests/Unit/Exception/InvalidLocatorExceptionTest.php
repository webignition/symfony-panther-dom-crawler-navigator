<?php

declare(strict_types=1);

namespace webignition\SymfonyDomCrawlerNavigator\Tests\Unit\Exception;

use Facebook\WebDriver\Exception\InvalidSelectorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\DomElementIdentifier\ElementIdentifierInterface;
use webignition\DomElementIdentifier\Enum\Type;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

class InvalidLocatorExceptionTest extends TestCase
{
    /**
     * @param array<mixed> $expected
     */
    #[DataProvider('getContextDataProvider')]
    public function testGetContext(InvalidLocatorException $exception, array $expected): void
    {
        self::assertSame($expected, $exception->getContext());
    }

    /**
     * @return array<mixed>
     */
    public static function getContextDataProvider(): array
    {
        return [
            'css' => [
                'exception' => new InvalidLocatorException(
                    (function () {
                        $identifier = \Mockery::mock(ElementIdentifierInterface::class);
                        $identifier
                            ->shouldReceive('isCssSelector')
                            ->andReturnTrue()
                        ;
                        $identifier
                            ->shouldReceive('getLocator')
                            ->andReturn('.css-locator')
                        ;
                        $identifier
                            ->shouldReceive('getType')
                            ->andReturn(Type::CSS)
                        ;

                        return $identifier;
                    })(),
                    new InvalidSelectorException(''),
                ),
                'expected' => [
                    'locator' => '.css-locator',
                    'type' => 'css',
                ],
            ],
            'xpath' => [
                'exception' => new InvalidLocatorException(
                    (function () {
                        $identifier = \Mockery::mock(ElementIdentifierInterface::class);
                        $identifier
                            ->shouldReceive('isCssSelector')
                            ->andReturnFalse()
                        ;
                        $identifier
                            ->shouldReceive('getLocator')
                            ->andReturn('//xpath-locator')
                        ;
                        $identifier
                            ->shouldReceive('getType')
                            ->andReturn(Type::XPATH)
                        ;

                        return $identifier;
                    })(),
                    new InvalidSelectorException(''),
                ),
                'expected' => [
                    'locator' => '//xpath-locator',
                    'type' => 'xpath',
                ],
            ],
            'null' => [
                'exception' => new InvalidLocatorException(
                    (function () {
                        $identifier = \Mockery::mock(ElementIdentifierInterface::class);
                        $identifier
                            ->shouldReceive('isCssSelector')
                            ->andReturnFalse()
                        ;
                        $identifier
                            ->shouldReceive('getLocator')
                            ->andReturn('')
                        ;
                        $identifier
                            ->shouldReceive('getType')
                            ->andReturnNull()
                        ;

                        return $identifier;
                    })(),
                    new InvalidSelectorException(''),
                ),
                'expected' => [
                    'locator' => '',
                    'type' => null,
                ],
            ],
        ];
    }
}
