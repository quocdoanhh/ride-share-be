<?php

if (!function_exists('generate_random_string')) {
    /**
     * Generate random string with alphabet and numbers
     *
     * @param int $length Length of the string (default: 6)
     *
     * @return string
     */
    function generate_random_string($length = 6)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }
}
