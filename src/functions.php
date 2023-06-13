<?php
if (!function_exists( 'print_ln' )) {
    /**
     * print_ln
     * @param $expression
     */
    function print_ln($expression)
    {
        print_r( $expression );
        print_r( PHP_EOL );
    }
}

if (!function_exists( 'print_success' )) {
    /**
     * print_success
     * @param $expression
     */
    function print_success($expression)
    {
        print_ln( "\033[0m\033[32m".print_r( $expression,1 )."\033[0m" );
    }
}

if (!function_exists( 'print_error' )) {
    /**
     * print_error
     * @param $expression
     */
    function print_error($expression)
    {
        print_ln( "\033[0m\033[31m".print_r( $expression,1 )."\033[0m" );
    }
}
