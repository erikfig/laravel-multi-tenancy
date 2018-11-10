<?php

if (!function_exists('get_subdomain')) {
    function get_subdomain() {
        return \Request::route(config('tenancy.route_param'));
    }
}