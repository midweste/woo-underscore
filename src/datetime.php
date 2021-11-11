<?php

namespace _woo;

use _;

/**
 * Return WC_DateTime in the site timezone
 */
function wc_datetime(string $datetime = 'now'): \WC_DateTime
{
    return new \WC_DateTime($datetime, _\datetimezone());
}
