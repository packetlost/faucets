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

class bs_api extends blockstrap
{   
    public static $cache;
    public static $options;
    public static $blockchain = 'btc';
    
    // SET DEFAULT OPTIONS
    private function defaults($settings = array())
    {
        $defaults = array(
            'blockchains' => array(
                'btc' => array(
                    'base' => 'http://api.blockstrap.com/v0/btc/',
                    'name' => 'Bitcoin'
                ),
                'ltc' => array(
                    'base' => 'http://api.blockstrap.com/v0/ltc/',
                    'name' => 'Litecoin'
                ),
                'dash' => array(
                    'base' => 'http://api.blockstrap.com/v0/dash/',
                    'name' => 'DashPay'
                ),
                'doge' => array(
                    'base' => 'http://api.blockstrap.com/v0/doge/',
                    'name' => 'Dogecoin'
                ),
                'btct' => array(
                    'base' => 'http://api.blockstrap.com/v0/btct/',
                    'name' => 'BTC Testnet'
                ),
                'ltct' => array(
                    'base' => 'http://api.blockstrap.com/v0/ltct/',
                    'name' => 'LTC Testnet'
                ),
                'dasht' => array(
                    'base' => 'http://api.blockstrap.com/v0/dasht/',
                    'name' => 'DashPay Testnet'
                ),
                'doget' => array(
                    'base' => 'http://api.blockstrap.com/v0/dogt/',
                    'name' => 'DOGE Testnet'
                ),
                'multi' => array(
                    'base' => 'http://api.blockstrap.com/v0/multi/',
                    'name' => 'Multiple Currencies'
                )
            )
        );
        $options = array_merge($defaults, $settings);
        return $options;
    }
    
    // SET DEFAULT API URL PARAMETERS
    private function parameters($options = array())
    {
        $defaults = array(
            'showtxn' => 0,
            'showtxnio' => 0,
            'records' => 500,
            'skip' => 0,
            'currency' => 'USD',
            'prettyprint' => 0,
            'app_id' => 'php_sdk_v1'
        );
        $settings = array_merge($defaults, $options);
        return $settings;
    }
    
    // INITIATE CURL REQUEST
    private function get($options = array()) 
    {
        $parameters = $this->parameters($options);
        $url = $this->priv($parameters);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        $response = curl_exec($ch);
        curl_close($ch);
        $ret= json_decode($response, true);
        return $ret;
    }

    // SHOW PUBLIC URL
    private function pub($options = array()) 
    {
        $parameters = $this->parameters($options);
        $parameters['prettyprint'] = 1;
        unset($parameters['api_key']);
        unset($parameters['api_sig']);
        return $this->url($parameters);
    }

    // SHOW PRIVATE URL
    private function priv($options = array()) 
    {
        $parameters = $this->parameters($options);
        $parameters['prettyprint'] = 0;
        return $this->url($parameters);
    }

    // FORM URL FOR API REQUEST
    private function url($options = array()) 
    {
        $blockchain_to_try = self::$blockchain;
        if(!$blockchain_to_try) $blockchain_to_try = 'btc';
        $parameters = $this->parameters($options);
        if(isset($parameters['chain'])) $blockchain_to_try = $parameters['chain'];
        if(
            isset($this::$options['blockchains'])
            && isset($this::$options['blockchains'][$blockchain_to_try])
            && isset($this::$options['blockchains'][$blockchain_to_try]['base'])
            && isset($parameters['method'])
        ){
            $url = $this::$options['blockchains'][$blockchain_to_try]['base'].$parameters['method'];
            if(isset($parameters['id'])) 
            {
                $url .= '/' . $parameters['id'];
            }
            $url .= '?';
            if($parameters['showtxn']) 
            {
                $url .= 'showtxn=' . $parameters['showtxn'] . '&';
            }
            if($parameters['showtxnio']) 
            {
                $url .= 'showtxnio=' . $parameters['showtxnio'] . '&';
            }
            if(500 != $parameters['records']) 
            {
                $url .= 'records=' . $parameters['records'] . '&';
            }
            if($parameters['skip']) 
            {
                $url .= 'skip=' . $parameters['skip'] . '&';
            }
            if('USD' != $parameters['currency']) 
            {
                $url .= 'currency=' . $parameters['currency'] . '&';
            }
            if($parameters['prettyprint']) 
            {
                $url .= 'prettyprint=' . $parameters['prettyprint'] . '&';
            }
            if(isset($parameters['app_id']))
            {
                $url .= 'app_id=' . $parameters['app_id'];
            }
            if(isset($parameters['debug']) && $parameters['debug'])
            {
                $this->debug($url);
            }
            return trim($url, "\u0026");
        }
        else
        {
            return false;
        }
    }
    
    // INITIATE API CLASS
    function __construct($settings = array())
    {
        $this::$options = $this->defaults($settings);
        $this::$cache = new bs_cache();
    }
    
    // GET ADDRESS USING PUBLIC KEY
    public function address($settings = array())
    {
        $data = false;
        // MAKE API CALL
        $defaults = array(
            'debug' => false,
            'details' => false,
            'method' => 'address/transactions',
            'chain' => $this::$blockchain,
            'base' => $this::$options['blockchains'][$this::$blockchain]['base'],
            'id' => false,
            'showtxn' => 0,
            'showtxnio' => 1
        );
        $options = array_merge($defaults, $settings);
        $key = 'address_'.$options['chain'].'_'.$options['id'];
        $results = $this::$cache->read($key, 'shortterm');
        if(!$results){
            $results = $this->get($options);
            $this::$cache->write($key, $results, 'shortterm');
        }
        elseif($options['debug'] === true)
        {
            $this->url($options);
        }
        if($options['details'] === true)
        {
            $data = $results;
        }
        else if(isset($results['status']) && $results['status'] == 'success')
        {
            $address = $results['data']['address'];
            $data = $address;
        }
        return $data;
    }
    
    // GET BLOCK USING HASH
    public function block($settings = array())
    {     
        $data = false;
        // MAKE API CALL
        $defaults = array(
            'debug' => false,
            'details' => false,
            'method' => 'block',
            'chain' => $this::$blockchain,
            'base' => $this::$options['blockchains'][$this::$blockchain]['base'],
            'id' => 0,
            'showtxn' => 0,
            'showtxnio' => 1
        );
        $options = array_merge($defaults, $settings);
        $key = 'block_'.$options['chain'].'_'.$options['id'];
        $results = $this::$cache->read($key, 'shortterm');
        if(!$results){
            $results = $this->get($options);
            $this::$cache->write($key, $results, 'shortterm');
        }
        elseif($options['debug'] === true)
        {
            $this->url($options);
        }
        if($options['details'] === true)
        {
            $data = $results;
        }
        else if(isset($results['status']) && $results['status'] == 'success')
        {
            $block = $results['data']['block'];
            $data = $block;
        }
        return $data;
    }
    
    // GET LATEST BLOCKS
    // ID EQUALS NUMBER TO RETURN
    public function blocks($settings = array())
    {           
        $data = false;
        // MAKE API CALL
        $defaults = array(
            'debug' => false,
            'details' => false,
            'method' => 'block/latest',
            'chain' => $this::$blockchain,
            'base' => $this::$options['blockchains'][$this::$blockchain]['base'],
            'id' => 5,
            'showtxn' => 1,
            'showtxnio' => 0
        );
        $options = array_merge($defaults, $settings);
        $key = 'blocks_'.$options['chain'].'_'.$options['id'];
        $results = $this::$cache->read($key, 'shortterm');
        if(!$results){
            $results = $this->get($options);
            $this::$cache->write($key, $results, 'shortterm');
        }
        elseif($options['debug'] === true)
        {
            $this->url($options);
        }
        if($options['details'] === true)
        {
            $data = $results;
        }
        else if(isset($results['status']) && $results['status'] == 'success')
        {
            $blocks = $results['data']['blocks'];
            $data = $blocks;
        }
        return $data;
    }
    
    // DECODE RAW TRANSACTION
    public function decode($settings = array())
    {           
        $data = false;
        // MAKE API CALL
        $defaults = array(
            'debug' => false,
            'details' => false,
            'method' => 'transaction/decode',
            'chain' => $this::$blockchain,
            'base' => $this::$options['blockchains'][$this::$blockchain]['base'],
            'id' => 0,
            'showtxn' => 0,
            'showtxnio' => 0
        );
        $options = array_merge($defaults, $settings);
        $key = 'decode_'.$options['chain'].'_'.$options['id'];
        $results = $this::$cache->read($key, 'shortterm');
        if(!$results){
            $results = $this->get($options);
            $this::$cache->write($key, $results, 'shortterm');
        }
        elseif($options['debug'] === true)
        {
            $this->url($options);
        }
        if($options['details'] === true)
        {
            $data = $results;
        }
        else if(isset($results['status']) && $results['status'] == 'success')
        {
            $tx = $results['data'];
            $data = $tx;
        }
        return $data;
    }
    
    // GET BLOCK USING HEIGHT
    public function height($settings = array())
    {           
        $data = false;
        // MAKE API CALL
        $defaults = array(
            'debug' => false,
            'details' => false,
            'method' => 'block/height',
            'chain' => $this::$blockchain,
            'base' => $this::$options['blockchains'][$this::$blockchain]['base'],
            'id' => false,
            'showtxn' => 0,
            'showtxnio' => 1
        );
        $options = array_merge($defaults, $settings);
        $key = 'height_'.$options['chain'].'_'.$options['id'];
        $results = $this::$cache->read($key, 'shortterm');
        if(!$results){
            $results = $this->get($options);
            $this::$cache->write($key, $results, 'shortterm');
        }
        elseif($options['debug'] === true)
        {
            $this->url($options);
        }
        if($options['details'] === true)
        {
            $data = $results;
        }
        else if(isset($results['status']) && $results['status'] == 'success')
        {
            $block = $results['data']['blocks'][0];
            $data = $block;
        }
        return $data;
    }
    
    // GET MARKET CONDITIONS
    public function market($settings = array())
    {          
        $data = false;
        // MAKE API CALL
        $defaults = array(
            'debug' => false,
            'details' => false,
            'method' => 'market/stats',
            'chain' => $this::$blockchain,
            'base' => $this::$options['blockchains'][$this::$blockchain]['base'],
            'id' => false,
            'showtxn' => 0,
            'showtxnio' => 0
        );
        $options = array_merge($defaults, $settings);
        $key = 'market_'.$options['chain'].'_'.$options['id'];
        $results = $this::$cache->read($key, 'shortterm');
        if(!$results){
            $results = $this->get($options);
            $this::$cache->write($key, $results, 'shortterm');
        }
        elseif($options['debug'] === true)
        {
            $this->url($options);
        }
        if($options['details'] === true)
        {
            $data = $results;
        }
        else if(isset($results['status']) && $results['status'] == 'success')
        {
            $market = $results['data']['markets'];
            $data = $market;
        }
        return $data;
    }
    
    // RELAY RAW TRANSACTION
    public function relay($settings = array())
    {
        $data = false;
        // MAKE API CALL
        $defaults = array(
            'debug' => false,
            'details' => false,
            'method' => 'transaction/relay',
            'chain' => $this::$blockchain,
            'base' => $this::$options['blockchains'][$this::$blockchain]['base'],
            'id' => 0,
            'showtxn' => 0,
            'showtxnio' => 0
        );
        $options = array_merge($defaults, $settings);
        $key = 'relay_'.$options['chain'].'_'.$options['id'];
        $results = $this::$cache->read($key, 'shortterm');
        if(!$results){
            $results = $this->get($options);
            $this::$cache->write($key, $results, 'shortterm');
        }
        elseif($options['debug'] === true)
        {
            $this->url($options);
        }
        if($options['details'] === true)
        {
            $data = $results;
        }
        else if(isset($results['status']) && $results['status'] == 'success')
        {
            $tx = $results['data'];
            $data = $tx;
        }
        return $data;
    }
    
    // GET TRANSACTION USING TXID
    public function transaction($settings = array())
    {           
        $data = false;
        // MAKE API CALL
        $defaults = array(
            'debug' => false,
            'details' => false,
            'method' => 'transaction/id',
            'chain' => $this::$blockchain,
            'base' => $this::$options['blockchains'][$this::$blockchain]['base'],
            'id' => 0,
            'showtxn' => 0,
            'showtxnio' => 1
        );
        $options = array_merge($defaults, $settings);
        $key = 'transacton_'.$options['chain'].'_'.$options['id'];
        $results = $this::$cache->read($key, 'shortterm');
        $this->url($options);
        if(!$results){
            $results = $this->get($options);
            $this::$cache->write($key, $results, 'shortterm');
        }
        elseif($options['debug'] === true)
        {
            $this->url($options);
        }
        if($options['details'] === true)
        {
            $data = $results;
        }
        else if(isset($results['status']) && $results['status'] == 'success')
        {
            $tx = $results['data']['transaction'];
            $data = $tx;
        }
        return $data;
    }
}