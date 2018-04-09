<?php
/**
 * Created by PhpStorm.
 * User: wangliangliang
 * Date: 2018/4/8
 * Time: 下午6:26
 */
require_once './vendor/autoload.php';
$ser = array(
    'baseUri'         => 'http://wlldev.com.',
    'route'           => '/user/getShieldSpeechList',
    'format'          => 'json',     //option   def : json
    'timeout'         => 1,         //option    def : 5
    'connect_timeout' => 5,         //option    def : 5
    'debug'           => true,      //option    def : false
    'headers'         => array()    //option

);

$input = array('no_check' => 1);

$options = array(
    'httpMethod' => 'get',          //option   def : post
//    'requestId'  => 123,           //option    def : rand
);

$a = new \Ofashion\Http\OfashionHttp(array('userId' => 12312));
$res = $a->call($ser, $input, $options);
print_r($res);