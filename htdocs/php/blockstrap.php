<?php

/*
 * 
 *  Blockstrap PHP SDK v1
 *  http://blockstrap.com
 *
 *  Designed, Developed and Maintained by Neuroware.io Inc
 *  All Work Released Under MIT License
 *  
 */

class blockstrap 
{
    public static $options;
    
    // SET DEFAULT OPTIONS
    private function defaults($settings = array())
    {
        $defaults = array(
            
        );
        $options = array_merge($defaults, $settings);
        return $options;
    }
    
    // INITIATE CLASS
    function __construct($settings = array())
    {
        $this::$options = $this->defaults($settings);
    }
    
    // BETTER PRINT_R
    public function debug($obj)
    {
        echo '<pre>';
        print_r($obj);
        echo '</pre>';
    }
    
    // GET VALUE OF $_GET WHILST SETTING DEFAULT
    public function get_var($variable, $default = false)
    {
        $value = $default;
        if(isset($variable) && isset($_GET) && isset($_GET[$variable]))
        {
            $value = $_GET[$variable];
        }
        return $value;
    }
}