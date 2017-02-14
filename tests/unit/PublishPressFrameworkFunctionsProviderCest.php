<?php

class PublishPressFrameworkFunctionsProviderCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    /**
     *
     */
    public function tryToCallNotExistentMethod(UnitTester $I)
    {
        $I->expectException('\Exception', function() {

        });
    }
}
