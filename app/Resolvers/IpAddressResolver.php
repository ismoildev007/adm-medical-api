<?php

namespace App\Resolvers;

use Illuminate\Support\Facades\Request;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class IpAddressResolver implements Resolver
{
    /**
     * @return string|null
     */
    public static function resolve(Auditable $auditable)
    {
        $ip = $auditable->preloadedResolverData['ip_address'] ?? Request::ip();

        $regex = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';

        return preg_match($regex, $ip) ? $ip : null;
    }
}
