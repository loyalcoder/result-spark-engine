<?php
namespace Result_Spark_Engine\Traits;

if (!defined('ABSPATH')) {
    exit;
}


/**
 * Test Trait
 */
trait Test
{
    /**
     * Check for name
     *
     * @return string
     */
    public function has_name()
    {
        return 'Roman Ul Ferdosh';
    }
}
