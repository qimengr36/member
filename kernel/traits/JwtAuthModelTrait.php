<?php


namespace kernel\traits;

use Firebase\JWT\JWT;
use think\facade\Env;

/**
 * Trait JwtAuthModelTrait
 * @package pmleb\traits
 */
trait JwtAuthModelTrait
{
    protected $token;

    /**
     * @param  string  $type
     * @param  array  $params
     * @return array
     */
    public function getToken(string $type, array $params = []): array
    {
        $id = $this->{$this->getPk()};
        $host = app()->request->host();
        $time = time();

        $params += [
            'iss' => $host,
            'aud' => $host,
            'iat' => $time,
            'nbf' => $time,
            'exp' => strtotime('+30 days'),
        ];
        $params['jti'] = compact('id', 'type');
        $app_key = Env::get('app.app_key');
        $alg = Env::get('app.app_alg', 'HS256');
        $token = JWT::encode($params, $app_key, $alg);

        return compact('token', 'params');
    }

    /**
     * @param  string  $jwt
     * @return array
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function parseToken(string $jwt): array
    {
        JWT::$leeway = 60;
        $app_key = Env::get('app.app_key');
        $alg = Env::get('app.app_alg', 'HS256');
        $data = JWT::decode($jwt, $app_key, $alg);

        $model = new self();
        return [$model->where($model->getPk(), $data->jti->id)->find(), $data->jti->type];
    }
}
