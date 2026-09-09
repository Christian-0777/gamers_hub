<?php

declare(strict_types=1);

function requestLocation(): string
{
    $country = trim((string) (
        $_SERVER['HTTP_CF_IPCOUNTRY']
        ?? $_SERVER['HTTP_X_COUNTRY_CODE']
        ?? $_SERVER['HTTP_X_GEOIP_COUNTRY']
        ?? ''
    ));

    return $country !== '' ? strtoupper($country) : 'Unknown';
}
