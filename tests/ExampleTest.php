<?php

namespace Motor\Core\Test;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Motor\Core\Filter\Filter;
use Motor\Core\Filter\Renderers\PerPageRenderer;
use Motor\Core\Filter\Renderers\SelectRenderer;
use Motor\Core\Filter\Renderers\WhereRenderer;
use Motor\Core\Helpers\GeneratorHelper;

/**
 * Class ExampleTest
 */
class ExampleTest extends TestCase
{
    protected function setUp(): void
    {
        if (! $this->app) {
            $this->refreshApplication();
        }

        $this->setUpTraits();

        foreach ($this->afterApplicationCreatedCallbacks as $callback) {
            call_user_func($callback);
        }

        Facade::clearResolvedInstances();

        Model::setEventDispatcher($this->app['events']);

        $this->setUpHasRun = true;
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
    }

    /** @test */
    public function it_can_get_the_path()
    {
        $namespace = GeneratorHelper::getPath('test', 'src', $this->app);
        $this->assertEquals(realpath('./').'/src/test.php', $namespace);
    }

    /** @test */
    public function it_can_get_the_namespace()
    {
        $namespace = GeneratorHelper::getNamespace('Motor\Core', null, $this->app);
        $this->assertEquals('Motor', $namespace);
    }

    /** @test */
    public function it_can_get_the_root_namespace()
    {
        $namespace = GeneratorHelper::getRootNamespace(null, $this->app);
        $this->assertEquals('App\\', $namespace);

        $namespace = GeneratorHelper::getRootNamespace('Motor\Core', $this->app);
        $this->assertEquals('Motor\Core\\', $namespace);
    }

    /** @test */
    public function it_can_instantiate_the_filter_class()
    {
        $filter = new Filter(null);
        $this->assertInstanceOf(Filter::class, $filter);
    }

    /** @test */
    public function it_can_add_a_where_filter()
    {
        //  Test name
        $filter = new WhereRenderer('where');
        $this->assertEquals('where', $filter->getName());
    }

    /** @test */
    public function it_can_add_a_per_page_filter()
    {
        //  Test name
        $filter = new PerPageRenderer('per_page');
        $filter->setup();
        $this->assertEquals('per_page', $filter->getName());
        $this->assertEquals(25, $filter->getDefaultValue());
        $this->assertEquals([25 => 25, 50 => 50, 100 => 100, 200 => 200], $filter->getOptions());
    }

    /** @test */
    public function it_can_add_a_select_filter()
    {
        //  Test name
        $selectFilter = new SelectRenderer('select_filter');
        $this->assertEquals('select_filter', $selectFilter->getName());

        // Test disallow null
        $selectFilter->setAllowNull(false);
        $this->assertFalse($selectFilter->getAllowNull());

        // Test allow null
        $selectFilter->setAllowNull(true);
        $this->assertTrue($selectFilter->getAllowNull());

        // Test default value
        $selectFilter->setDefaultValue('test');
        $this->assertEquals('test', $selectFilter->getDefaultValue());
        $this->assertNotEquals('toast', $selectFilter->getDefaultValue());

        // Test operator
        $selectFilter->setOperator('=');
        $this->assertEquals('=', $selectFilter->getOperator());

        // Test empty option
        $selectFilter->setEmptyOption('test');
        $this->assertEquals('test', $selectFilter->getEmptyOption());

        // Test options
        $selectFilter->setOptions(['test' => 'toast', 'a' => 'b']);
        $this->assertEquals(['test' => 'toast', 'a' => 'b'], $selectFilter->getOptions());

        // Test option prefix
        $selectFilter->setOptionPrefix('test');
        $this->assertEquals('test', $selectFilter->getOptionPrefix());

        // Test join
        $selectFilter->setJoin('users');
        $this->assertEquals('users', $selectFilter->getJoin());

        // Test field
        $selectFilter->setField('name');
        $this->assertEquals('name', $selectFilter->getField());

        // Test visibility
        $selectFilter->isVisible(true);
        $this->assertTrue($selectFilter->getVisible());

        // Test invisibility
        $selectFilter->isVisible(false);
        $this->assertFalse($selectFilter->getVisible());

        // Test if default value is returned if visibility is false
        $selectFilter->isVisible(false);
        $selectFilter->setOptions(null);
        $selectFilter->setOptionPrefix(null);
        $selectFilter->setDefaultValue('default');
        $selectFilter->setValue('changed_value');
        $this->assertEquals('default', $selectFilter->getValue());

        // Test if value is not one of the options
        $selectFilter->isVisible(false);
        $selectFilter->setOptions(['a' => true, 'c' => true]);
        $selectFilter->setOptionPrefix(null);
        $selectFilter->setDefaultValue('default');
        $selectFilter->setValue('illegal_value');
        $this->assertNull($selectFilter->getValue());

        // Test if value is one of the options
        $selectFilter->isVisible(true);
        $selectFilter->setOptions(['allowed_value' => true, 'c' => true]);
        $selectFilter->setOptionPrefix(null);
        $selectFilter->setDefaultValue('default');
        $selectFilter->setValue('allowed_value');
        $this->assertEquals('allowed_value', $selectFilter->getValue());

        // Test setting session value
        $selectFilter->updateValues();

        $filter = new Filter(null);

        $filter->add($selectFilter);
        $this->assertCount(1, $filter->filters());
    }

    /** @test */
    public function it_can_return_empty_filter_array()
    {
        $filter = new Filter(null);
        $this->assertIsArray($filter->filters());
    }

    /** @test */
    public function it_can_get_a_spefific_filter()
    {
        $selectRenderer = new SelectRenderer('select_filter');
        $filter = new Filter(null);
        $filter->add($selectRenderer);
        $this->assertInstanceOf(SelectRenderer::class, $filter->get('select_filter'));

        $this->assertIsArray($filter->filters());
    }

    // /** @test */
    // public function it_can_add_the_client_filter()
    // {
    //    Auth::onceUsingId(1);
    //    $filter = new Filter(null);
    //    $filter->addClientFilter();
    //    $this->assertTrue(true);
    // }
}
