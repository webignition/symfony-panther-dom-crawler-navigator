<?php

declare(strict_types=1);

namespace webignition\SymfonyDomCrawlerNavigator\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use webignition\SymfonyDomCrawlerNavigator\Exception\PositionOutOfBoundsException;

class PositionOutOfBoundsExceptionTest extends TestCase
{
    private const COLLECTION_COUNT = 3;
    private const ORDINAL_POSITION = 4;

    private PositionOutOfBoundsException $exception;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exception = new PositionOutOfBoundsException(self::ORDINAL_POSITION, self::COLLECTION_COUNT);
    }

    public function testGetOrdinalPosition(): void
    {
        self::assertSame(self::ORDINAL_POSITION, $this->exception->getOrdinalPosition());
    }

    public function testGetCollectionCount(): void
    {
        self::assertSame(self::COLLECTION_COUNT, $this->exception->getCollectionCount());
    }
}
