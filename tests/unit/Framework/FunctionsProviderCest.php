<?php

use Publishpress\Factory;

class FrameworkFunctionsProviderCest
{
    public function _before(UnitTester $I)
    {
        $this->container = Publishpress\Factory::getContainer(true);
    }

    /**
     * Check if an exception is throwned when we call a function which
     * is not defined.
     */
    public function checkExceptionCallingNotExistentMethod(UnitTester $I)
    {
        $I->expectException('\Exception', function() {
            $this->container->caller->anyUnknownMethod();
        });
    }
}
