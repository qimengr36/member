<?php

namespace kernel\utils;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use kernel\services\robot\DingTalkService;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use think\facade\Log;

/**
 * 网络请求 服务
 */
class HttpClient
{
    /**
     * 是否可访问
     * @param $url
     * @return bool
     */
    public function checkUrl($url): bool
    {
        try {
            file_get_contents($url);
        } catch (\Exception $e) {
            $content = [
                'url' => $url,
                'error' => $e->getMessage(),
            ];
            $this->talk($content);
            return false;
        }
        return true;
    }

    public bool $is_inform = true;

    /**
     * http(s)请求 支持 GET POST PUT DEL 等请求方式
     * 支持 form json xml 等请求类型
     *
     * @param string $url
     * @param array $params
     * @param string $method
     * @param array $cfg
     * @param array $headers
     * @param string $type
     * @return array
     */
    public function request(string $url, array $params, string $method, array $headers = [], string $type = 'json', array $cfg = []): array
    {
        $body = null;
        // 参数句柄
        $options = $cfg;
        // 没设定默认请求响应一分钟超时
        if (!isset($cfg['timeout'])) {
            $options['timeout'] = 60;
        }
        if (!isset($cfg['verify'])) {
            $options['verify'] = false;
        }
        $method = strtoupper($method);
        if ($method == 'GET') {
            $options['query'] = $params;
        }
        // 请求body
        $type = strtolower($type);
        switch ($type) {
            case 'xml'://xml
                $body = Transformer::toXml($params);
                break;
            case 'form_params'://application/x-www-form-urlencoded
                $options['form_params'] = $params;
                break;
            case 'json':
                $options['json'] = $params;
                break;
            case 'form_data'://multipart/form-data
                $return_param = [];
                foreach ($params as $key => $val) {
                    $param_array = [
                        'name' => $key,
                    ];
                    $value = $val['value'];
                    if ($val['type'] == 'file') {
                        $param_array['filename'] = $value;
                        $param_array['headers'] = [
                            'Content-Type' => '<Content-type header>',
                        ];
                    }
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                    $param_array['contents'] = $value;

                    $return_param[] = $param_array;
                }
                $options['multipart'] = $return_param;
                break;
            default:
                $body = '';
        }
        // 实例化
        $client = new Client();
        // 实例化请求
        $request = new Request($method, $url, $headers, $body);
        $promise = $client->sendAsync($request, $options);
        $success_data = [];
        $promise->then(
            function (ResponseInterface $res) use (&$success_data) {
                $contents = $res->getBody()->getContents();
                if ($contents) {
                    if (Str::isJson($contents)) {
                        $success_data = Str::json2arr($contents);
                    }
                    if (Str::isXml($contents)) {
                        $success_data = Transformer::toArray($contents);
                    }
                } else {
                    $contents = '';
                }
                if (!$success_data) {
                    $success_data = $contents;
                }
            }
        );

        $fail_data = [];
        try {
            $promise->wait();
        } catch (RequestException $e) {
            $message = $e->getMessage();
            if (!$message) {
                $message = $e->getResponse()->getBody()->getContents();
            }
            if (Str::isJson($message)) {
                $var_message = Str::json2arr($message);
            }
            if (Str::isXml($message)) {
                $var_message = Transformer::toArray($message);
            }
            $array['message'] = $var_message ?? $message;
            $fail_data = $array;
            $fail_data['msg_code'] = 'fail';
        }

        if ($fail_data) {
            $msg_code = 'fail';
            $data = $fail_data;
        } else {
            $msg_code = 'success';
            $data = $success_data;
        }
        if ($msg_code == 'success') {
            return ['code' => 0, 'data' => $data];
        } else {
            $item = compact('url', 'params', 'method', 'headers', 'type', 'cfg', 'fail_data');
            $content = [
                'url' => $url,
                'error' => $fail_data['message']['body'] ?? $fail_data['message'],
            ];
            $this->talk($content);
            Log::write(['type' => 'http_send', '`item' => json_encode($item, JSON_UNESCAPED_UNICODE), 'location' => dirname(__FILE__)], 'serve_error');
            if (isset($data['message'])) {
                return ['code' => 1, 'msg' => $data['message']];
            } else {
                return ['code' => 1, 'msg' => '连接超时'];
            }
        }
    }

    public function talk($content)
    {
        if ($this->is_inform) {
            $this->sendError($content);
        } else {
            Log::write(['type' => 'http_send', '`item' => json_encode($content, JSON_UNESCAPED_UNICODE), 'location' => dirname(__FILE__)], 'serve_error');
        }
    }

    public function sendError($content)
    {
        $talk = app()->make(DingTalkService::class);
        $talk->send($content);
    }
}