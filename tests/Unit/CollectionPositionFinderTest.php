<?php

declare(strict_types=1);

namespace webignition\SymfonyDomCrawlerNavigator\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\SymfonyDomCrawlerNavigator\CollectionPositionFinder;
use webignition\SymfonyDomCrawlerNavigator\Exception\PositionCannotBeZeroException;
use webignition\SymfonyDomCrawlerNavigator\Exception\PositionOutOfBoundsException;

class CollectionPositionFinderTest extends TestCase
{
    private CollectionPositionFinder $collectionPositionFinder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collectionPositionFinder = new CollectionPositionFinder();
    }

    #[DataProvider('findSuccessDataProvider')]
    public function testFindSuccess(
        int $ordinalPosition,
        int $collectionCount,
        int $expectedPosition
    ): void {
        self::assertSame($expectedPosition, $this->collectionPositionFinder->find($ordinalPosition, $collectionCount));
    }

    /**
     * @return array<mixed>
     */
    public static function findSuccessDataProvider(): array
    {
        return [
            'first of three' => [
                'ordinalPosition' => 1,
                'collectionCount' => 3,
                'expectedPosition' => 0,
            ],
            'second of three' => [
                'ordinalPosition' => 2,
                'collectionCount' => 3,
                'expectedPosition' => 1,
            ],
            'third of three' => [
                'ordinalPosition' => 3,
                'collectionCount' => 3,
                'expectedPosition' => 2,
            ],
            'last of three' => [
                'ordinalPosition' => -1,
                'collectionCount' => 3,
                'expectedPosition' => 2,
            ],
            'second to last of three' => [
                'ordinalPosition' => -2,
                'collectionCount' => 3,
                'expectedPosition' => 1,
            ],
            'third to last of three' => [
                'ordinalPosition' => -3,
                'collectionCount' => 3,
                'expectedPosition' => 0,
            ],
        ];
    }

    #[DataProvider('findThrowsExceptionDataProvider')]
    public function testFindThrowsException(
        int $ordinalPosition,
        int $collectionCount,
        \Exception $expectedException
    ): void {
        $this->expectExceptionObject($expectedException);

        $this->collectionPositionFinder->find($ordinalPosition, $collectionCount);
    }

    /**
     * @return array<mixed>
     */
    public static function findThrowsExceptionDataProvider(): array
    {
        return [
            'ordinalPosition: zero, collectionCount: non-zero' => [
                'ordinalPosition' => 0,
                'collectionCount' => 1,
                'expectedException' => new PositionCannotBeZeroException(1),
            ],
            'ordinalPosition: positive, collectionCount: zero' => [
                'ordinalPosition' => 1,
                'collectionCount' => 0,
                'expectedException' => new PositionOutOfBoundsException(1, 0),
            ],
            'ordinalPosition greater than collection count, collection count non-zero' => [
                'ordinalPosition' => 3,
                'collectionCount' => 2,
                'expectedException' => new PositionOutOfBoundsException(3, 2),
            ],
            'ordinalPosition greater than collection -count, collection count non-zero' => [
                'ordinalPosition' => -3,
                'collectionCount' => 2,
                'expectedException' => new PositionOutOfBoundsException(-3, 2),
            ],
        ];
    }
}
