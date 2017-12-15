<?php

namespace Yansongda\Pay\Gateways;

use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Contracts\GatewayApplicationInterface;
use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Log;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;
use Yansongda\Supports\Traits\HasHttpRequest;

class Alipay implements GatewayApplicationInterface
{
    use HasHttpRequest;

    /**
     * Config.
     *
     * @var Config
     */
    protected $config;

    /**
     * Alipay payload.
     *
     * @var array
     */
    protected $payload;

    /**
     * Alipay gateway.
     *
     * @var string
     */
    protected $baseUri = 'https://openapi.alipay.com/gateway.do?charset=utf-8';

    /**
     * Bootstrap.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->payload = [
            'app_id'      => $this->config->get('app_id'),
            'method'      => '',
            'format'      => 'JSON',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'version'     => '1.0',
            'return_url'  => $this->config->get('return_url', ''),
            'notify_url'  => $this->config->get('notify_url', ''),
            'timestamp'   => date('Y-m-d H:i:s'),
            'sign'        => '',
            'biz_content' => '',
        ];
    }

    /**
     * Pay a order.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $gateway
     * @param array $params
     *
     * @return [type]
     */
    public function pay($gateway, $params)
    {
        $this->payload['biz_content'] = json_encode($params);

        $gateway = get_class($this) . "\\" . Str::studly($gateway) . "Gateway";
        
        if (class_exists($gateway)) {
            return $this->makeGateway($gateway);
        }

        throw new GatewayException("Pay Gateway [{$gateway}] not exists", 1);
    }

    public function verify($data = null)
    {
        # code...
    }

    public function find()
    {
        # code...
    }

    public function refund()
    {
        # code...
    }

    public function cancel()
    {
        # code...
    }

    public function close()
    {
        # code...
    }

    /**
     * Reply success to alipay.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return Response
     */
    public function success()
    {
        return Response::create('success');
    }

    /**
     * Make pay gateway.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $gateway
     *
     * @return [return]
     */
    protected function makeGateway($gateway)
    {
        $app = new $gateway($this->config);

        if ($app instanceof GatewayInterface) {
            return $app->pay($this->payload);
        }

        throw new GatewayException("Pay Gateway [{$gateway}] must be a instance of GatewayInterface", 2);
    }

    /**
     * Magic pay.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $method
     * @param array $params
     *
     * @return [type]
     */
    public function __call($method, $params)
    {
        return $this->pay($method, ...$params);
    }
}
