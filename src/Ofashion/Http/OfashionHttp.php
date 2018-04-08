<?php
/**
 * Created by PhpStorm.
 * User: wangliangliang
 * Date: 2018/4/8
 * Time: 下午12:43
 */

namespace Ofashion\Http;

use Cascade\Cascade;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Yaml\Exception\ParseException;

class OfashionHttp
{
    const  TIMEOUT = 5;
    const  CONN_TIMEOUT = 5;

    //上下文穿透userId
    private $userId;

    private $header;

    public function __construct(array $context)
    {
        $this->userId = isset($context['userId']) ? $context['userId'] : '';
    }

    /**
     * 自定义请求
     *
     * @param $key     目前只支持自定义header
     * @param $value   要设置的值
     * @return bool
     */
    public function setOption($key, $value)
    {
        $ret = true;
        switch ($key) {
            case 'header' :
                $this->header = $value;
                break;
            default :
                $ret = false;
        }
        return $ret;
    }

    public function call(array $service, array $input = array(), array $options = array())
    {
        $baseUri = $service['baseUri'];
        $route = $service['route'];
        $httpMethod = isset($options['httpMethod']) ? strtoupper($options['httpMethod']) : 'POST';

        if (isset($options['requestId'])) {
            $requestId = $options['requestId'];
        } else {
            $requestId = mt_rand(10000000000, 99999999999);
        }

        $requestParams = $this->buildRequestParams($service, $input, $requestId, $options);
        $httpClient = new Client(array('base_uri' => $baseUri));

        //call
        $start = microtime(true) * 1000;
        try {
            if ('GET' == $httpMethod) {
                $response = $httpClient->get($route, $requestParams);
            } elseif ('PUT' == $httpMethod) {
                $response = $httpClient->put($route, $requestParams);
            } elseif ('DELETE' == $httpMethod) {
                $response = $httpClient->delete($route, $requestParams);
            } else {
                $response = $httpClient->post($route, $requestParams);
            }

            $errno = 200;
            $reError = 'success';
        } catch (ConnectException $e) {
            $errno = 21000;  //连接超时错误码
            $response = false;
            $reError = $e->getMessage();

        } catch (ClientException $e) {
            $errno = 22000; //客户端错误码
            $response = $e->hasResponse() ? $e->getResponse() : false;
            $reError = $e->getMessage();

        } catch (RequestException $e) {
            $errno = 23000; //请求错误码
            $response = $e->hasResponse() ? $e->getResponse() : false;
            $reError = $e->getMessage();

        }

        $end = microtime(true) * 1000;
        $apiCosts = intval($end - $start);

        //log
        $logParams = array(
            'service'  => strval($baseUri),
            'route'    => strval($route),
            'cost'     => $apiCosts,
            'userId'   => $this->userId,
            'httpCode' => false === $response ? 0 : $response->getStatusCode(),
            'type'     => 'call',
            'sendId'   => $requestId
        );

        Cascade::fileConfig('src/Ofashion/Config/Log.yaml');

        if (200 !== $errno) {
            Cascade::getLogger('ofashion-rpc')->info("errorCode:$errno $baseUri$route", $logParams);
            return array('code' => $errno, 'msg' => $reError, 'data' => array());
        }

        try {
            $outPut = $this->getOutPut($service, $response);
            Cascade::getLogger('ofashion-rpc')->info("errorCode:$errno $baseUri$route", $logParams);
        } catch (ParseException $e) {
            $errno = 24000;
            $errmsg = $e->getMessage();
            $responseBody = str_replace("\n", '', $response->getBody()->__toString());
            $errors = sprintf('%s, body: %s...', $errmsg, mb_strcut($responseBody, 0, 200, 'utf-8'));
            Cascade::getLogger('ofashion-rpc')->info("errorCode:$errno $baseUri$route", $logParams);
            return array('code' => $errno, 'msg' => $errors, 'data' => array());
        }

        return $outPut;
    }


    private function buildRequestParams($service, $input, $requestId, $options)
    {
        //build http def params
        $requestParams = array(
            'timeout'         => isset($service['timeout']) ? floatval($service['timeout']) : self::TIMEOUT,
            'connect_timeout' => isset($service['connect_timeout']) ? floatval($service['connect_timeout']) : self::CONN_TIMEOUT,
            'debug'           => isset($service['debug']) ? $service['debug'] : false
        );

        $httpMethod = 'POST';

        if (isset($options['httpMethod'])) {
            $httpMethod = strtoupper($options['httpMethod']);
            unset($options['httpMethod']);
        }

        if (is_array($this->header) && !empty($this->header)) {
            $requestParams['headers'] = is_array($service['headers']) ? array_merge($service['headers'], $this->header) : $this->header;
        } else {
            $requestParams['headers'] = $service['headers'];
        }

        $requestParams['headers']['X_FW_USER_ID'] = $this->userId;
        $requestParams['headers']['X_FW_REQ_ID  '] = $requestId;

        if (!empty($options)) {
            $requestParams['headers']['X_FW_OPTION'] = json_encode($options);
        }

        $format = isset($service['format']) ? $service['format'] : 'json';

        if ('GET' == $httpMethod) {
            $requestParams['query'] = $input;
            $requestParams['body'] = '';
            return $requestParams;
        }

        switch ($format) {

            case 'json' :
                $requestParams['body'] = json_encode($input);
                $requestParams['headers']['Content-Type'] = 'application/json';
                break;
            default :
                $requestParams['body'] = http_build_query($input, '', '&');
                $requestParams['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        return $requestParams;
    }

    private function getOutPut($service, $response)
    {
        $format = isset($service['format']) ? $service['format'] : 'json';

        switch ($format) {
            case 'json' :
                $output = json_decode(strval($response->getBody()), true);
                break;
            default :
                $output = strval($response->getBody());
        }

        return $output;
    }
}