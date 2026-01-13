<?php

namespace Result_Spark_Engine;

if (!defined('ABSPATH')) {
    exit;
}

class Admin
{
    /**
     * Class initialize
     */
    function __construct()
    {
        new Admin\Menu();
        new Admin\Handler();
        new Admin\Subject_Metabox();
        new Admin\Subject_No_Major_Metabox();
        new Admin\Grade_Metabox();
        new Admin\Student_Metabox();
        new Admin\Student_Info_Metabox();
        new Admin\Class_Level_Metabox();
        new Admin\Department_Metabox();
    }
}
