<?php

namespace App\Services;

/**
 * Class EnjoyUrlService
 *
 * This service provides methods for retrieving base URLs
 * based on the current environment and configuration settings.
 *
 * @package App\Services
 */
class EnjoyUrlService
{
    /**
     * Get the base HTTP URL.
     *
     * @return string The base HTTP URL.
     */
    public function getHttpUrl()
    {
        return env('APP_ENV') === 'local' || env('APP_DEBUG') === true
            ? str_replace('https', 'http', env('ENJOY_URL_HOMOLOGATION'))
            : str_replace('https', 'http', env('ENJOY_URL_PRODUCTION'));
    }

    /**
     * Get the base HTTPS URL.
     *
     * @return string The base HTTPS URL.
     */
    public function getHttpsUrl()
    {
        return env('APP_ENV') === 'local' || env('APP_DEBUG') === true
            ? env('ENJOY_URL_HOMOLOGATION')
            : env('ENJOY_URL_PRODUCTION');
    }
}
