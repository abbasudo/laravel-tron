<?php

namespace Llabbasmkhll\LaravelTron\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 * @mixin \Llabbasmkhll\LaravelTron\Tron
 *
 * @package Llabbasmkhll\LaravelTron\Facades
 */
class Tron extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Tron';
    }
}
