<?php
/**
 * Created by PhpStorm.
 * User: wangliangliang
 * Date: 2018/4/9
 * Time: 上午11:06
 */

namespace Ofashion\Log;

use Monolog\Logger as OfashionLog;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class Logger
{
    const DEBUG = 100;
    const INFO = 200;
    const NOTICE = 250;
    const WARNING = 300;
    const ERROR = 400;
    const CRITICAL = 500;
    const ALERT = 550;
    const EMERGENCY = 600;

    static protected $logger;

    static public function init()
    {
        if (!self::$logger instanceof OfashionLog) {
            self::$logger = new OfashionLog('OfashionLog');
            $handler = new StreamHandler('src/storage/logs/' . date('Y-m-d') . '.log', OfashionLog::DEBUG);
            self::$logger->pushHandler($handler);
            self::$logger->pushHandler(new FirePHPHandler());
        }
    }

    static public function getLogger()
    {
        self::init();
        return self::$logger;
    }

    static public function __callStatic($method, $paramters)
    {
        self::init();

        if (method_exists(self::$logger, $method)) {
            return call_user_func_array(array(self::$logger, $method), $paramters);
        }

        if (method_exists('OfashionLog', $method)) {
            return forward_static_call_array(array('OfashionLog', $method), $paramters);
        } else {
            throw new RuntimeException('methods do not exist');
        }
    }
}