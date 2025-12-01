<?php

namespace Result_Spark_Engine;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend class
 */
class Frontend
{
    /**
     * Initialize class
     */
    public function __construct()
    {
        new Frontend\Shortcode();
    }
}
