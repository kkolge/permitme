<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //'http://permitmemass.ko-aaham.com/sDevData',
        //'http://permitmemass.ko-aaham.com/vRFID'
        'http://192.168.5.139/sDevData',
        'https://192.168.5.139/vRFID'
	// 'http://192.168.49.123/sDevData',
       // 'http://192.168.49.123/vRFID'
    ];
}
