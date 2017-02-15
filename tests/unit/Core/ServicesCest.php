<?php
use Codeception\Util\Stub;

class CoreServicesCest
{
    protected $container;

    public function _before(UnitTester $I)
    {
        $this->container = Publishpress\Factory::getContainer(true);
    }

    /**
     * Check the Plugin definition
     */
    public function testPluginIsInstanceOfCorrectClass(UnitTester $I)
    {
        $plugin = $this->container['plugin'];

        $I->assertInstanceOf('\\Publishpress\\Core\\Plugin', $plugin);
    }

    public function testConstantSlug(UnitTester $I)
    {
        $path     = $this->container['PUBLISHPRESS_SLUG'];
        $expected = 'publishpress';

        $I->assertEquals($expected, $path);
    }

    public function testConstantRootPath(UnitTester $I)
    {
        $path     = $this->container['PUBLISHPRESS_ROOT_PATH'];
        $expected = realpath(dirname(dirname(dirname(dirname(__FILE__)))) . '/src');

        $I->assertEquals($expected, $path);
    }

    public function testConstantFilePath(UnitTester $I)
    {
        $path     = $this->container['PUBLISHPRESS_FILE_PATH'];
        $expected = realpath(dirname(dirname(dirname(dirname(__FILE__)))) . '/src/publishpress.php');

        $I->assertEquals($expected, $path);
    }

    public function testConstantVersion(UnitTester $I)
    {
        $this->container->setServiceEntry('helper', function() {
            $stub = Stub::make(
                '\\Publishpress\\Core\\Helper',
                array(
                    'getPluginVersion' => 'x.y.z'
                )
            );

            return $stub;
        });

        $version  = $this->container['PUBLISHPRESS_VERSION'];
        $expected = 'x.y.z';

        $I->assertEquals($expected, $version);
    }

    public function testConstantUrl(UnitTester $I)
    {
        $this->container->setServiceEntry('caller', function() {
            $stub = Stub::make(
                '\\Publishpress\\Framework\\FunctionsProvider',
                array(
                    '__call' => function($name, $arguments) {
                        if ('pluginsUrl' === $name) {
                            return 'http://localhost/wp-content/plugins/publishpress';
                        }
                    }
                )
            );

            return $stub;
        });

        $url      = $this->container['PUBLISHPRESS_URL'];
        $expected = 'http://localhost/wp-content/plugins/publishpress';

        $I->assertEquals($expected, $url);
    }
}
