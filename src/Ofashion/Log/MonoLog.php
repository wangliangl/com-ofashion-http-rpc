<?php
/**
 * Created by PhpStorm.
 * User: wangliangliang
 * Date: 2018/4/9
 * Time: 上午11:22
 */

namespace Ofashion\Log;

use Monolog\Logger as OfashionLog;

class MonoLog
{

    public function wirte($log)
    {
        $logger = Logger::getLogger();

        if ($logger->getHandlers()) {
            if (false !== strpos($log, 'INFO: [  log_begin ] --START--')) {
                $logger->addRecord(OfashionLog::EMERGENCY, "\r\n" . $log);
            } else {
                $level = strstr($log, ':', true);
                $msg = ltrim(strstr($log, ':'), ':');
                switch ($level) {
                    case Log::ERR:
                        $level = Mlogger::ERROR;
                        break;
                    case Log::EMERG:
                        $level = Mlogger::EMERGENCY;
                        break;
                    case Log::INFO:
                        $level = Mlogger::INFO;
                        break;
                    case Log::WARN:
                        $level = Mlogger::WARNING;
                        break;
                    case Log::NOTICE:
                        $level = Mlogger::NOTICE;
                        break;
                    case Log::ALERT:
                        $level = Mlogger::ALERT;
                        break;
                    case Log::CRIT:
                        $level = Mlogger::CRITICAL;
                        break;
                    default:
                        $level = Mlogger::DEBUG;
                }
                $logger->addRecord($level, $msg);
            }
        }
    }
}