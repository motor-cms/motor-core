<?php

namespace Motor\Core\Test;

class ExampleTest extends TestCase
{
    public function setUp()
    {
    }

    /** @test */
    public function it_can_do_basic_tests()
    {
        $basicTest = "String";
        $this->assertEquals("String", $basicTest);
    }
}