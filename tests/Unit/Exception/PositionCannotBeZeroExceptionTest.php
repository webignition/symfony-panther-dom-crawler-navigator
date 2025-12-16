<?php

declare(strict_types=1);

namespace webignition\SymfonyDomCrawlerNavigator\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use webignition\SymfonyDomCrawlerNavigator\Exception\PositionCannotBeZeroException;

class PositionCannotBeZeroExceptionTest extends TestCase
{
    private const COLLECTION_COUNT = 3;

    private PositionCannotBeZeroException $exception;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exception = new PositionCannotBeZeroException(self::COLLECTION_COUNT);
    }

    public function testGetOrdinalPosition(): void
    {
        $this->assertSame(0, $this->exception->getOrdinalPosition());
    }

    public function testGetCollectionCount(): void
    {
        $this->assertSame(self::COLLECTION_COUNT, $this->exception->getCollectionCount());
    }
}
