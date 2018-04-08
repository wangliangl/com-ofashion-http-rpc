<?php
/**
 * Created by PhpStorm.
 * User: wangliangliang
 * Date: 2018/4/8
 * Time: 下午5:00
 */

class HttpTest extends \PHPUnit\Framework\TestCase
{
    public function testOfashionHttp()
    {
        $ser = array(
            'baseUri'         => 'wlldev.com',
            'route'           => 'user/getShieldSpeechList',
            'format'          => 'json',     //option   def : json
            'timeout'         => 3,         //option    def : 5
            'connect_timeout' => 3,         //option    def : 5
            'debug'           => true,      //option    def : false
            'headers'         => array()    //option

        );

        $input = array();

        $options = array(
            'httpMethod' => 'get',          //option   def : post
            'requestId'  => 123,           //option    def : rand
        );

        $a = new \Ofashion\Http\OfashionHttp(array('userId' => 12312));
        $res = $a->call($ser, $input, $options);
        print_r($res);
    }
}