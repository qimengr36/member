<?php


namespace kernel\utils;


use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use kernel\exceptions\AdminException;
use kernel\services\CacheService;
use think\facade\Env;

/**
 * Jwt
 * Class JwtAuth
 * @package pmleb\utils
 */
class JwtAuth
{

    /**
     * token
     * @var string
     */
    protected $token;

    /**
     * 获取token
     * @param  int|string  $id
     * @param  string  $type
     * @param  array  $params
     * @return array
     */
    public function getToken($id, string $type, array $params = []): array
    {
        $host = app()->request->host();
        $time = time();
        $exp_time = strtotime('+ 30day');
        $params += [
            'iss' => $host,
            'aud' => $host,
            'iat' => $time,
            'nbf' => $time,
            'exp' => $exp_time,
        ];
        $params['jti'] = compact('id', 'type');
        $app_key = Env::get('app.app_key');
        $alg = Env::get('app.app_alg', 'HS256');
        $token = JWT::encode($params, $app_key, $alg);

        return compact('token', 'params');
    }

    /**
     * 解析token
     * @param  string  $jwt
     * @return array
     */
    public function parseToken(string $jwt): array
    {
        $this->token = $jwt;
        list($headb64, $bodyb64, $cryptob64) = explode('.', $this->token);
        $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
        return [$payload->jti->id, $payload->jti->type, $payload->pwd ?? ''];
    }

    /**
     * 验证token
     */
    public function verifyToken()
    {
        JWT::$leeway = 60;
        $app_key = Env::get('app.app_key');
        $alg = Env::get('app.app_alg', 'HS256');
        JWT::decode($this->token, new Key($app_key, $alg));

        $this->token = null;
    }

    /**
     * 获取token并放入令牌桶
     * @param $id
     * @param  string  $type
     * @param  array  $params
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function createToken($id, string $type, array $params = [])
    {
        $tokenInfo = $this->getToken($id, $type, $params);
        $exp = $tokenInfo['params']['exp'] - $tokenInfo['params']['iat'] + 60;
        $key = md5($tokenInfo['token']);
        $value = ['uid' => $id, 'type' => $type, 'token' => $tokenInfo['token'], 'exp' => $exp];
        $res = CacheService::set($key, $value, (int)$exp, $type);
        if (!$res) {
            throw new AdminException(100023);
        }
        return $tokenInfo;
    }
}
