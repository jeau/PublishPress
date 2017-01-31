<?php
namespace Core;

use PublishPress\Core\Plugin;
use Codeception\Util\Stub;

class PluginTest extends \Codeception\Test\Unit
{
    private $actions = array();

    public function setUp()
    {
        parent::setUp();

        // Initialize the Container
        $this->container = Stub::make(
            'PublishPress\\Core\\Container',
            array()
        );
    }

    public function tearDown()
    {
        $this->actions = array();

        // then
        parent::tearDown();
    }

    public function testGetInstanceReturnsCorrectInstance()
    {
        $container = Stub::make('PublishPress\\Core\\Container');
        $plugin    = new Plugin($container);
        $instance  = $plugin->getInstance();

        $this->assertTrue(
            $plugin === $instance,
            'The instance should be the same object'
        );
    }

    public function testSetupAddActionPluginsLoaded()
    {
        $container = Stub::make(
            'PublishPress\\Core\\Container',
            array(
                'wpfunc' => Stub::make(
                    'PublishPress\\Core\\FunctionsProvider',
                    array(
                        'addAction' => Stub::exactly(2, function($tag, $callable) {
                            $this->actions[$tag] = $callable;

                            return true;
                        }),
                    )
                )
            )
        );
        $plugin = new Plugin($container);
        $plugin->setup();

        $this->assertArrayHasKey(
            'plugins_loaded',
            $this->actions,
            'The setup should add an action to plugins_loaded'
        );
        $this->assertTrue(
            is_callable($this->actions['plugins_loaded']),
            'The action plugins_loaded needs a callable function/method'
        );
    }

    public function testGetSlug()
    {
        $container = Stub::make('PublishPress\\Core\\Container');
        $plugin    = new Plugin($container);
        $slug      = $plugin->getSlug();

        $this->assertTrue(
            is_string($slug),
            'The slug should always be a string'
        );

        $this->assertEquals(
            'publishpress',
            $slug,
            'The slug should be: publishpress'
        );
    }
}
