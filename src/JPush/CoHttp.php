<?php
namespace JPush;
use JPush\Exceptions\APIConnectionException;
use JPush\Exceptions\APIRequestException;
use JPush\Exceptions\ServiceNotAvaliable;
use Hyperf\Guzzle\ClientFactory;
final class CoHttp {

    /**
     * @var \Hyperf\Guzzle\ClientFactory
     */
    private $clientFactory;

    public function __construct()
    {
        $this->clientFactory = \Hyperf\Utils\ApplicationContext::getContainer()->get(ClientFactory::class);
    }

    public function httpClient($options=[])
    {
        // $options 等同于 GuzzleHttp\Client 构造函数的 $config 参数
        //$options = [];
        // $client 为协程化的 GuzzleHttp\Client 对象
        return $this->clientFactory->create($options);
    }
    public  function get($client, $url) {
        $response = $this->sendRequest($client, $url, Config::HTTP_GET, $body=null);
        return $this->processResp($response);
    }
    public  function post($client, $url, $body) {
        $response = $this->sendRequest($client, $url, Config::HTTP_POST, $body);
        return $this->processResp($response);
    }
    public  function put($client, $url, $body) {
        $response = $this->sendRequest($client, $url, Config::HTTP_PUT, $body);
        return $this->processResp($response);
    }
    public  function delete($client, $url) {
        $response = $this->sendRequest($client, $url, Config::HTTP_DELETE, $body=null);
        return $this->processResp($response);
    }

    private  function sendRequest($client, $url, $method, $body=null, $times=1) {
        $this->log($client, "Send " . $method . " " . $url . ", body:" . json_encode($body) . ", times:" . $times);
        $options=[
            'auth'=>$client->getAuthArray(),
            'headers'=>[
                'User-Agent' => Config::USER_AGENT,
                'Connection'=>'Keep-Alive',
                'Content-Type'=>'application/json'
            ],
            'version'=>3,
            'connect_timeout'=>Config::CONNECT_TIMEOUT,
            'timeout'=>Config::READ_TIMEOUT,
        ];
        if (!is_null($body)) {
            $options['json']=$body;
        }
        $response=$this->httpClient()->request($method,$url,$options);

        return $response;
    }

    public  function processResp($response) {
        $code=$response->getStatusCode();
        $contents=$response->getBody()->getContents();
        $headers=$response->getHeaders();
        $result = array(
            'http_code'=>$code,
            'headers'=>$headers
        );
        if ($code === 200) {
            $result['body'] = json_decode($contents,true);
            return $result;
        } elseif (is_null($contents)) {
            $result['body'] = $contents;
            throw new ServiceNotAvaliable($result);
        } else {
            $result['body'] = $contents;
            throw new APIRequestException($result);
        }
    }

    public  function log($client, $content) {
        if (!is_null($client->getLogFile())) {
            error_log($content . "\r\n", 3, $client->getLogFile());
        }
    }
}
