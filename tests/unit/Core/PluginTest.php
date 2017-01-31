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

    public function testInitAddActionPluginsLoaded()
    {
        $container = Stub::make(
            'PublishPress\\Core\\Container',
            array(
                'wpfunc' => Stub::make(
                    'PublishPress\\Core\\FunctionsProvider',
                    array(
                        'addAction' => Stub::once(function($tag, $callable) {
                            $this->actions[$tag] = $callable;

                            return true;
                        }),
                    )
                )
            )
        );
        $plugin = new Plugin($container);
        $plugin->init();

        $this->assertArrayHasKey(
            'plugins_loaded',
            $this->actions,
            'After init it should add an action to plugins_loaded'
        );
        $this->assertTrue(
            is_callable($this->actions['plugins_loaded']),
            'The action needs a callable function/method'
        );
    }
}
