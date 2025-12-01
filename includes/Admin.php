<?php

namespace Pkun;

class Admin
{
    /**
     * Class initialize
     */
    function __construct()
    {
        new Admin\Menu();
        new Admin\Handler();
        new Admin\CMB2();

        // CMB2 example and custom fields
        // new Admin\CMB2_Sample();
        // new Library\CMB2\CMB2_Switch_Button();
        // new Library\CMB2\PW_CMB2_Field_Select2();
        //End CMB2 example and custom fields

    }
}
