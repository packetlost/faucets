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

session_start();

class bs_cache extends blockstrap
{   
    public static $options;
    
    // SET DEFAULT OPTIONS
    private function defaults($settings = array())
    {
        $defaults = array(
            'ttl' => 60 // 60 seconds equals one minute
        );
        $options = array_merge($defaults, $settings);
        return $options;
    }
    
    // INITIATE CLASS
    function __construct($settings = array())
    {
        $this::$options = $this->defaults($settings);
    }
    
    // GET CACHED RESULTS
    public function read($key, $term)
    {
        $value = false;
        $date = new DateTime();
        $now = $date->format('U');
        if(isset($_SESSION) && isset($_SESSION[$key]))
        {
            $record = json_decode($_SESSION[$key], true);
            if(isset($record['ts']) && isset($record['data']))
            {
                if((int) $record['ts'] + (int) $this::$options['ttl'] < $now)
                {
                    unset($_SESSION[$key]);
                }
                else
                {
                    $value = $record['data'];
                }
            }
        }
        return $value;
    }
    
    // SAVE RESULTS TO CACHE
    public function write($key, $value, $term)
    {
        $date = new DateTime();
        $now = $date->format('U');
        $_SESSION[$key] = json_encode(array(
            'ts' => $now,
            'data' => $value
        ));
    }
}