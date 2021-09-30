<?php


use PHPUnit\Framework\TestCase;
use Sirius\Container;

class ContainerTest extends TestCase
{
    private Container $entity;

    public function setUp(): void
    {
        $this->entity = Container::getInstance();
    }

    /**
     * @throws Exception
     */
    public function testGetRootPath()
    {
        $actual = $this->entity->getRootPath();

        $this->assertTrue($actual);
    }
}