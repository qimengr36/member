<?php
// 应用公共文件
use app\services\other\UploadService;
use app\services\system\lang\LangCodeServices;
use app\services\system\lang\LangCountryServices;
use app\services\system\lang\LangTypeServices;
use kernel\services\CacheService;
use kernel\services\FormBuilder as Form;
use kernel\services\HttpService;
use think\exception\ValidateException;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\facade\Log;


if (!function_exists('getWorkerManUrl')) {
    /**
     * 获取客服数据
     * @return mixed
     */
    function getWorkerManUrl()
    {
        $ws = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'wss://' : 'ws://';
        $host = $_SERVER['HTTP_HOST'];
        $data['admin'] = $ws.$host.'/notice';
        $data['chat'] = $ws.$host.'/msg';
        return $data;
    }
}
if (!function_exists('object2array')) {
    /**
     * 对象转数组
     * @param $object
     * @return array|mixed
     */
    function object2array($object)
    {
        $array = [];
        if (is_object($object)) {
            foreach ($object as $key => $value) {
                $array[$key] = $value;
            }
        } else {
            $array = $object;
        }
        return $array;
    }
}

if (!function_exists('exception')) {
    /**
     * 抛出异常处理
     * @param $msg
     * @param  int  $code
     * @param  string  $exception
     * @throws \think\Exception
     */
    function exception($msg, $code = 0, $exception = '')
    {
        $e = $exception ?: '\think\Exception';
        throw new $e($msg, $code);
    }
}

if (!function_exists('sys_config')) {
    /**
     * 获取系统单个配置
     * @param  string  $name
     * @param  string  $default
     * @return string
     */
    function sys_config(string $name, $default = '')
    {
        if (empty($name)) {
            return $default;
        }
        $sysConfig = app('sysConfig')->get($name);
        if (is_array($sysConfig)) {
            foreach ($sysConfig as &$item) {
                if (strpos($item, '/uploads/system/') !== false || strpos($item, '/statics/system_images/') !== false) {
                    $item = set_file_url($item);
                }
            }
        } else {
            if (strpos($sysConfig, '/uploads/system/') !== false || strpos(
                    $sysConfig,
                    '/statics/system_images/'
                ) !== false) {
                $sysConfig = set_file_url($sysConfig);
            }
        }
        $config = is_array($sysConfig) ? $sysConfig : trim($sysConfig);
        if ($config === '' || $config === false) {
            return $default;
        } else {
            return trim($config, '"');
        }
    }
}

if (!function_exists('sys_data')) {
    /**
     * 获取系统单个配置
     * @param  string  $name
     * @return string
     */
    function sys_data(string $name, int $limit = 0)
    {
        return app('sysGroupData')->getData($name, $limit);
    }
}

if (!function_exists('filter_emoji')) {
    // 过滤掉emoji表情
    function filter_emoji($str)
    {
        $str = preg_replace_callback(    //执行一个正则表达式搜索并且使用一个回调进行替换
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str
        );
        return $str;
    }
}


if (!function_exists('rtrim_url')) {
    /**去除url斜杠
     * @param $url
     * @return string
     */
    function rtrim_url($url)
    {
        return rtrim($url, '/');
    }
}
if (!function_exists('ltrim_url')) {
    /**去除url斜杠
     * @param $url
     * @return string
     */
    function ltrim_url($url)
    {
        return ltrim($url, '/');
    }
}

if (!function_exists('curl_file_exist')) {
    /**
     * CURL 检测远程文件是否在
     * @param $url
     * @return bool
     */
    function curl_file_exist($url)
    {
        $ch = curl_init();
        try {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            $contents = curl_exec($ch);
            if (preg_match("/404/", $contents)) {
                return false;
            }
            if (preg_match("/403/", $contents)) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
if (!function_exists('set_file_url')) {
    /**
     * 设置附加路径
     * @param $url
     * @return bool
     */
    function set_file_url($image, $siteUrl = '')
    {
        if (!strlen(trim($siteUrl))) {
            $siteUrl = sys_config('site_url');
        }
        $siteUrl = rtrim($siteUrl, "/").'/';
        if (!$image) {
            return $image;
        }
        if (is_array($image)) {
            foreach ($image as &$item) {
                $domainTop1 = substr($item, 0, 4);
                $domainTop2 = substr($item, 0, 2);
                if ($domainTop1 != 'http' && $domainTop2 != '//') {
                    $image_file = str_replace('\\', '/', $image);
                    $image_file = ltrim($image_file, "/");
                    $item = $siteUrl.$image_file;
                }
            }
        } else {
            $domainTop1 = substr($image, 0, 4);
            $domainTop2 = substr($image, 0, 2);
            if ($domainTop1 != 'http' && $domainTop2 != '//') {
                $image_file = str_replace('\\', '/', $image);
                $image_file = ltrim($image_file, "/");
                $image = $siteUrl.$image_file;
            }
        }
        return $image;
    }
}

if (!function_exists('set_http_type')) {
    /**
     * 修改 https 和 http
     * @param $url  $url 域名
     * @param  int  $type  0 返回https 1 返回 http
     * @return string
     */
    function set_http_type($url, $type = 0)
    {
        $domainTop = substr($url, 0, 5);
        if ($type) {
            if ($domainTop == 'https') {
                $url = 'http'.substr($url, 5, strlen($url));
            }
        } else {
            if ($domainTop != 'https') {
                $url = 'https:'.substr($url, 5, strlen($url));
            }
        }
        return $url;
    }
}
if (!function_exists('check_link')) {
    /**
     * 地址验证
     * @param  string  $link
     * @return false|int
     */
    function check_link(string $link)
    {
        return preg_match(
            "/^(http|https|ftp):\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_]+[\/=\?%\-&_~`@[\]\’:+!]*([^<>\”])*$/",
            $link
        );
    }
}
if (!function_exists('check_phone')) {
    /**
     * 手机号验证
     * @param $phone
     * @return false|int
     */
    function check_phone($phone)
    {
        return preg_match("/^1[3456789]\d{9}$/", $phone);
    }
}
if (!function_exists('anonymity')) {
    /**
     * 匿名处理处理用户昵称
     * @param $name
     * @return string
     */
    function anonymity($name, $type = 1)
    {
        if ($type == 1) {
            return mb_substr($name, 0, 1, 'UTF-8').'**'.mb_substr($name, -1, 1, 'UTF-8');
        } else {
            $strLen = mb_strlen($name, 'UTF-8');
            $min = 3;
            if ($strLen <= 1) {
                return '*';
            }
            if ($strLen <= $min) {
                return mb_substr($name, 0, 1, 'UTF-8').str_repeat('*', $min - 1);
            } else {
                return mb_substr($name, 0, 1, 'UTF-8').str_repeat('*', $strLen - 1).mb_substr($name, -1, 1, 'UTF-8');
            }
        }
    }
}
if (!function_exists('sort_list_tier')) {
    /**
     * 分级排序
     * @param $data
     * @param  int  $pid
     * @param  string  $field
     * @param  string  $pk
     * @param  string  $html
     * @param  int  $level
     * @param  bool  $clear
     * @return array
     */
    function sort_list_tier($data, $pid = 0, $field = 'pid', $pk = 'id', $html = '|-----', $level = 1, $clear = true)
    {
        static $list = [];
        if ($clear) {
            $list = [];
        }
        foreach ($data as $k => $res) {
            if ($res[$field] == $pid) {
                $res['html'] = str_repeat($html, $level);
                $list[] = $res;
                unset($data[$k]);
                sort_list_tier($data, $res[$pk], $field, $pk, $html, $level + 1, false);
            }
        }
        return $list;
    }
}

if (!function_exists('time_tran')) {
    /**
     * 时间戳人性化转化
     * @param $time
     * @return string
     */
    function time_tran($time)
    {
        $t = time() - $time;
        $f = array(
            '31536000' => '年',
            '2592000'  => '个月',
            '604800'   => '星期',
            '86400'    => '天',
            '3600'     => '小时',
            '60'       => '分钟',
            '1'        => '秒'
        );
        foreach ($f as $k => $v) {
            if (0 != $c = floor($t / (int)$k)) {
                return $c.$v.'前';
            }
        }
    }
}

if (!function_exists('url_to_path')) {
    /**
     * url转换路径
     * @param $url
     * @return string
     */
    function url_to_path($url)
    {
        $path = trim(str_replace('/', DS, $url), DS);
        if (0 !== strripos($path, 'public')) {
            $path = 'public'.DS.$path;
        }
        return app()->getRootPath().$path;
    }
}

if (!function_exists('path_to_url')) {
    /**
     * 路径转url路径
     * @param $path
     * @return string
     */
    function path_to_url($path)
    {
        return trim(str_replace(DS, '/', $path), '.');
    }
}

if (!function_exists('image_to_base64')) {
    /**
     * 获取图片转为base64
     * @param  string  $avatar
     * @return bool|string
     */
    function image_to_base64($avatar = '', $timeout = 9)
    {
        $avatar = str_replace('https', 'http', $avatar);
        try {
            $url = parse_url($avatar);
            if ($url['scheme'].'://'.$url['host'] == sys_config('site_url')) {
                return "data:image/jpeg;base64,".base64_encode(
                        file_get_contents(public_path().substr($url['path'], 1))
                    );
            }
            $url = $url['host'];
            $header = [
                'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
                'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
                'Accept-Encoding: gzip, deflate, br',
                'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'Host:'.$url
            ];
            $dir = pathinfo($url);
            $host = $dir['dirname'];
            $refer = $host.'/';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_REFERER, $refer);
            curl_setopt($curl, CURLOPT_URL, $avatar);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $data = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($code == 200) {
                return "data:image/jpeg;base64,".base64_encode($data);
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('put_image')) {
    /**
     * 获取图片转为base64
     * @param  string  $avatar
     * @return bool|string
     */
    function put_image($url, $filename = '')
    {
        if ($url == '') {
            return false;
        }
        try {
            if ($filename == '') {
                $ext = pathinfo($url);
                if ($ext['extension'] != "jpg" && $ext['extension'] != "png" && $ext['extension'] != "jpeg") {
                    return false;
                }
                $filename = time().".".$ext['extension'];
            }

            //文件保存路径
            ob_start();
            $url = str_replace('phar://', '', $url);
            readfile($url);
            $img = ob_get_contents();
            ob_end_clean();
            $path = 'uploads/qrcode';
            $fp2 = fopen($path.'/'.$filename, 'a');
            fwrite($fp2, $img);
            fclose($fp2);
            return $path.'/'.$filename;
        } catch (\Exception $e) {
            return false;
        }
    }
}


if (!function_exists('sql_filter')) {
    /**
     * sql 参数过滤
     * @param  string  $str
     * @return mixed
     */
    function sql_filter(string $str)
    {
        $filter = [
            'select ',
            'insert ',
            'update ',
            'delete ',
            'drop',
            'truncate ',
            'declare',
            'xp_cmdshell',
            '/add',
            ' or ',
            'exec',
            'create',
            'chr',
            'mid',
            ' and ',
            'execute'
        ];
        $toupper = array_map(function ($str) {
            return strtoupper($str);
        }, $filter);
        return str_replace(array_merge($filter, $toupper, ['%20']), '', $str);
    }
}


if (!function_exists('array_unique_fb')) {
    /**
     * 二维数组去掉重复值
     * @param $array
     * @return array
     */
    function array_unique_fb($array)
    {
        $out = array();
        foreach ($array as $key => $value) {
            if (!in_array($value, $out)) {
                $out[$key] = $value;
            }
        }
        $out = array_values($out);
        return $out;
    }
}


if (!function_exists('get_file_link')) {
    /**
     * 获取文件带域名的完整路径
     * @param  string  $link
     * @return string
     */
    function get_file_link(string $link)
    {
        if (!$link) {
            return '';
        }
        if (substr($link, 0, 4) === "http" || substr($link, 0, 2) === "//") {
            return $link;
        } else {
            return app()->request->domain().$link;
        }
    }
}

if (!function_exists('tidy_tree')) {
    /**
     * 格式化分类
     * @param $menusList
     * @param  int  $pid
     * @param  array  $navList
     * @return array
     */
    function tidy_tree($menusList, $pid = 0, $navList = [])
    {
        foreach ($menusList as $k => $menu) {
            if ($menu['parent_id'] == $pid) {
                unset($menusList[$k]);
                $menu['children'] = tidy_tree($menusList, $menu['id']);
                if ($menu['children']) {
                    $menu['expand'] = true;
                }
                $navList[] = $menu;
            }
        }
        return $navList;
    }
}

if (!function_exists('create_form')) {
    /**
     * 表单生成方法
     * @param  string  $title
     * @param  array  $field
     * @param $url
     * @param  string  $method
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    function create_form(string $title, array $field, $url, string $method = 'POST')
    {
        $form = Form::createForm((string)$url);//提交地址
        $form->setMethod($method);//提交方式
        $form->setRule($field);//表单字段
        $form->setTitle($title);//表单标题
        $rules = $form->formRule();
        $title = $form->getTitle();
        $action = $form->getAction();
        $method = $form->getMethod();
        $info = '';
        $status = true;
        $methodData = ['POST', 'PUT', 'GET', 'DELETE'];
        if (!in_array(strtoupper($method), $methodData)) {
            throw new ValidateException('请求方式有误');
        }
        return compact('rules', 'title', 'action', 'method', 'info', 'status');
    }
}

if (!function_exists('msectime')) {
    /**
     * 获取毫秒数
     * @return float
     */
    function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }
}


if (!function_exists('array_bc_sum')) {
    /**
     * 获取一维数组的总合高精度
     * @param  array  $data
     * @return string
     */
    function array_bc_sum(array $data)
    {
        $sum = '0';
        foreach ($data as $item) {
            $sum = bcadd($sum, (string)$item, 2);
        }
        return $sum;
    }
}

if (!function_exists('get_tree_children')) {
    /**
     * tree 子菜单
     * @param  array  $data  数据
     * @param  string  $childrenname  子数据名
     * @param  string  $keyName  数据key名
     * @param  string  $pidName  数据上级key名
     * @return array
     */
    function get_tree_children(
        array $data,
        string $childrenname = 'children',
        string $keyName = 'id',
        string $pidName = 'pid'
    ) {
        $list = array();
        foreach ($data as $value) {
            $list[$value[$keyName]] = $value;
        }
        static $tree = array(); //格式化好的树
        foreach ($list as $item) {
            if (isset($list[$item[$pidName]])) {
                $list[$item[$pidName]][$childrenname][] = &$list[$item[$keyName]];
            } else {
                $tree[] = &$list[$item[$keyName]];
            }
        }
        return $tree;
    }
}

if (!function_exists('get_tree_children_value')) {
    function get_tree_children_value(array $data, $value, string $childrenname = 'children', string $keyName = 'id')
    {
        static $childrenValue = [];
        foreach ($data as $item) {
            $childrenData = $item[$childrenname] ?? [];
            if (count($childrenData)) {
                return get_tree_children_value($childrenData, $childrenname, $keyName);
            } else {
                if ($item[$keyName] == $value) {
                    $childrenValue[] = $item['value'];
                }
            }
        }
        return $childrenValue;
    }
}


if (!function_exists('get_tree_value')) {
    /**
     * 获取
     * @param  array  $data
     * @param  int|string  $value
     * @return array
     */
    function get_tree_value(array $data, $value)
    {
        static $childrenValue = [];
        foreach ($data as &$item) {
            if ($item['value'] == $value) {
                $childrenValue[] = $item['value'];
                if ($item['pid']) {
                    $value = $item['pid'];
                    unset($item);
                    return get_tree_value($data, $value);
                }
            }
        }
        return $childrenValue;
    }
}

if (!function_exists('get_image_thumb')) {
    /**
     * 获取缩略图
     * @param $filePath
     * @param  string  $type  all|big|mid|small
     * @param  bool  $is_remote_down
     * @return mixed|string|string[]
     */
    function get_image_thumb($filePath, string $type = 'all', bool $is_remote_down = false)
    {
        if (!$filePath || !is_string($filePath) || strpos($filePath, '?') !== false) {
            return $filePath;
        }
        try {
            $upload = UploadService::getOssInit($filePath, $is_remote_down);
            $fileArr = explode('/', $filePath);
            $data = $upload->thumb($filePath, end($fileArr), $type);
            $image = $type == 'all' ? $data : $data[$type] ?? $filePath;
        } catch (\Throwable $e) {
            $image = $filePath;
            \think\facade\Log::error(
                '获取缩略图失败，原因：'.$e->getMessage().'----'.$e->getFile().'----'.$e->getLine().'----'.$filePath
            );
        }
        $data = parse_url($image);
        if (!isset($data['host']) && (substr($image, 0, 2) == './' || substr($image, 0, 1) == '/')) {//不是完整地址
            $image = sys_config('site_url').$image;
        }
        //请求是https 图片是http 需要改变图片地址
        if (strpos(request()->domain(), 'https:') !== false && strpos($image, 'https:') === false) {
            $image = str_replace('http:', 'https:', $image);
        }
        return $image;
    }
}
if (!function_exists('make_path')) {
    /**
     * 上传路径转化,默认路径
     * @param $path
     * @param  int  $type
     * @param  bool  $force
     * @return string
     */
    function make_path($path, int $type = 2, bool $force = false)
    {
        $path = DS.ltrim(rtrim($path));
        switch ($type) {
            case 1:
                $path .= DS.date('Y');
                break;
            case 2:
                $path .= DS.date('Y').DS.date('m');
                break;
            case 3:
                $path .= DS.date('Y').DS.date('m').DS.date('d');
                break;
        }
        try {
            if (is_dir(app()->getRootPath().'public'.DS.'uploads'.$path) || mkdir(
                    app()->getRootPath().'public'.DS.'uploads'.$path,
                    0777,
                    true
                )) {
                return trim(str_replace(DS, '/', $path), '.');
            } else {
                return '';
            }
        } catch (\Exception $e) {
            if ($force) {
                throw new \Exception($e->getMessage());
            }
            return '';
        }
    }
}
if (!function_exists('get_thumb_water')) {
    /**
     * 处理数组获取缩略图、水印
     * @param $list
     * @param  string  $type
     * @param  array|string[]  $field  1、['image','images'] type 取值参数:type 2、['small'=>'image','mid'=>'images'] type 取field数组的key
     * @param  bool  $is_remote_down
     * @return array|mixed|string|string[]
     */
    function get_thumb_water($list, string $type = 'small', array $field = ['image'], bool $is_remote_down = false)
    {
        if (!$list || !$field) {
            return $list;
        }
        $baseType = $type;
        $data = $list;
        if (is_string($list)) {
            $field = [$type => 'image'];
            $data = ['image' => $list];
        }
        if (is_array($data)) {
            foreach ($field as $type => $key) {
                if (is_integer($type)) {//索引数组，默认type
                    $type = $baseType;
                }
                //一维数组
                if (isset($data[$key])) {
                    if (is_array($data[$key])) {
                        $path_data = [];
                        foreach ($data[$key] as $k => $path) {
                            $path_data[] = get_image_thumb($path, $type, $is_remote_down);
                        }
                        $data[$key] = $path_data;
                    } else {
                        $data[$key] = get_image_thumb($data[$key], $type, $is_remote_down);
                    }
                } else {
                    foreach ($data as &$item) {
                        if (!isset($item[$key])) {
                            continue;
                        }
                        if (is_array($item[$key])) {
                            $path_data = [];
                            foreach ($item[$key] as $k => $path) {
                                $path_data[] = get_image_thumb($path, $type, $is_remote_down);
                            }
                            $item[$key] = $path_data;
                        } else {
                            $item[$key] = get_image_thumb($item[$key], $type, $is_remote_down);
                        }
                    }
                }
            }
        }
        return is_string($list) ? ($data['image'] ?? '') : $data;
    }
}

if (!function_exists('getLangRange')) {
    function getLangRange()
    {
        /** @var LangTypeServices $langTypeServices */
        $langTypeServices = app()->make(LangTypeServices::class);
        $request = app()->request;
        //获取接口传入的语言类型
        if (!$range = $request->header(config('lang.cookie_var'))) {
            //没有传入则使用系统默认语言显示
            if ($request->header('accept-language') !== null) {
                $range = explode(',', $request->header('accept-language'))[0];
            } else {
                $range = CacheService::remember('range_name', function () use ($langTypeServices) {
                    return $langTypeServices->value(['is_default' => 1], 'file_name');
                });
                if (!$range) {
                    $range = Config::get('lang.default_lang');
                }
            }
        }
        return $range;
    }
}
if (!function_exists('getLang')) {
    /**
     * 多语言
     * @param $code
     * @param  array  $replace
     * @return array|string|string[]
     */
    function getLang($code, array $replace = [], $env = 0)
    {
        $tag = Config::get('lang.tag').$env;
        //确保获取语言的时候不会报错
        try {
            /** @var LangCountryServices $langCountryServices */
            $langCountryServices = app()->make(LangCountryServices::class);
            /** @var LangTypeServices $langTypeServices */
            $langTypeServices = app()->make(LangTypeServices::class);
            /** @var LangCodeServices $langCodeServices */
            $langCodeServices = app()->make(LangCodeServices::class);
            $range = getLangRange();

            // 获取type_id
            $typeId = CacheService::remember('type_id_'.$range, function () use ($langCountryServices, $range) {
                return $langCountryServices->value(['code' => $range], 'type_id') ?: 1;
            }, 3600, $tag);

            // 获取类型
            $langData = CacheService::remember('lang_type_data', function () use ($langTypeServices) {
                return $langTypeServices->getColumn(['status' => 1, 'is_del' => 0], 'file_name', 'id');
            }, 3600, $tag);
            // 获取缓存key
            $langStr = 'lang_'.str_replace('-', '_', $langData[$typeId] ?? 'zh_CN');

            //读取当前语言的语言包
            $lang = CacheService::remember($langStr, function () use ($typeId, $range, $langCodeServices, $env) {
                return $langCodeServices->getColumn(['type_id' => $typeId, 'env' => $env], 'lang_explain', 'code');
            }, 3600, $tag);
            //获取返回文字

            if (!isset($lang[$code])) {
                $lang = $langCodeServices->getColumn(['type_id' => $typeId, 'env' => $env], 'lang_explain', 'code');
                if (!isset($lang[$code])) {
                    $typeId = 1;
                    $lang = $langCodeServices->getColumn(['type_id' => $typeId, 'env' => $env], 'lang_explain', 'code');
                }
            }
            $message = (string)($lang[$code] ?? 'Code Error');

            //替换变量
            if (!empty($replace) && is_array($replace)) {
                // 关联索引解析
                $key = array_keys($replace);
                foreach ($key as &$v) {
                    $v = "{:{$v}}";
                }
                $message = str_replace($key, $replace, $message);
            }

            return $message;
        } catch (\Throwable $e) {
            Log::error(
                '获取语言code：'.$code.'发成错误，错误原因是：'.json_encode([
                    'file' => $e->getFile(),
                    'message' => $e->getMessage(),
                    'line' => $e->getLine()
                ])
            );
            return $code;
        }
    }
}


if (!function_exists('out_push')) {
    /**
     * 默认数据推送
     * @param  string  $pushUrl
     * @param  array  $data
     * @param  string  $tip
     * @return bool
     */
    function out_push(string $pushUrl, array $data, string $tip = ''): bool
    {
        $param = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = HttpService::postRequest(
            $pushUrl,
            $param,
            ['Content-Type:application/json', 'Content-Length:'.strlen($param)]
        );
        $res = $res ? json_decode($res, true) : [];
        if (!$res || !isset($res['code']) || $res['code'] != 0) {
            \think\facade\Log::error(['msg' => $tip.'推送失败', 'data' => $res]);
            return false;
        }
        return true;
    }
}

if (!function_exists('dump_sql')) {
    /**
     * 默认数据推送
     * @param  string  $pushUrl
     * @param  array  $data
     * @param  string  $tip
     * @return bool
     */
    function dump_sql()
    {
        Db::listen(function ($sql) {
            var_dump($sql);
        });
    }
}
if (!function_exists('stringToIntArray')) {
    /**
     * 处理ids等并过滤参数
     * @param  string  $string
     * @param  string  $separator
     * @return array
     */
    function stringToIntArray(string $string, string $separator = ',')
    {
        return !empty($string) ? array_unique(array_diff(array_map('intval', explode($separator, $string)), [0])) : [];
    }
}
if (!function_exists('present_data')) {
    /**保留的数据
     * @param  array  $data
     * @param  array  $keys
     * @return array
     */
    function present_data(array $data, array $keys)
    {
        if ($data && $keys) {
            foreach ($data as $k => $v) {
                if (!in_array($k, $keys)) {
                    unset($data[$k]);
                }
            }
            return $data;
        }
        return [];
    }
}
if (!function_exists('order_status')) {
    /**设置缓存数据的key
     * @return array
     */
    function order_status()
    {
        //订单状态（0：待发货；1：待收货；2：已收货；3：部分发货;4关闭）
        return [
            '0' => getLang('200172', [], 1),//待发货
            '1' => getLang('200546', [], 1),//待收货
            '2' => getLang('200547', [], 1),//已收货
            '3' => getLang('200276', [], 1),//部分发货
            '4' => getLang('200655', [], 1),//关闭
        ];
    }
}
if (!function_exists('child_order_status')) {
    /**设置缓存数据的key
     * @return array
     */
    function child_order_status()
    {
        //订单状态（0：待发货；1：发货；2：收货 5：退款；6：取消,8关闭）
        return [
            '0' => getLang('200172', [], 1),//待发货
            '1' => getLang('200546', [], 1),//待收货
            '2' => getLang('200547', [], 1),//已收货
            '5' => getLang('200183', [], 1),//已退款
            '6' => getLang('200184', [], 1),//已取消
            '8' => getLang('200655', [], 1),//关闭
        ];
    }
}
if (!function_exists('paid_status')) {
    /**设置缓存数据的key
     * @return array
     */
    function paid_status()
    {
        //支付状态 0.已创建 1.已支付 2.支付失败  4.部分退款  5：已退款；6：已取消；7：部分取消；8.关闭
        return [
            '0' => getLang('200652', [], 1),//待支付
            '1' => getLang('200651', [], 1),//已支付
            '2' => getLang('200653', [], 1),//支付失败
            '4' => getLang('200076', [], 1),//部分退款
            '5' => getLang('200183', [], 1),//已退款
            '6' => getLang('200184', [], 1),//已取消
            '7' => getLang('200654', [], 1),//部分取消
            '8' => getLang('200655', [], 1),//已关闭
        ];
    }
}
if (!function_exists('get_snowflake_id')) {
    function get_snowflake_id($prefix = '')
    {
        $snowflake = new \Godruoyi\Snowflake\Snowflake();
        $is_callable = function ($currentTime) {
            $redis = Cache::store('redis');
            $swooleSequenceResolver = new \Godruoyi\Snowflake\RedisSequenceResolver($redis->handler());
            return $swooleSequenceResolver->sequence($currentTime);
        };
        //32位
        if (PHP_INT_SIZE == 4) {
            $id = abs($snowflake->setSequenceResolver($is_callable)->id());
        } else {
            $id = $snowflake->setStartTimeStamp(strtotime('2020-06-05') * 1000)->setSequenceResolver($is_callable)->id(
            );
        }
        return $prefix.$id;
    }
}
if (!function_exists('get_random_num')) {
    function get_random_num($length = 8)
    {
        $randomNumber = '';
        for ($i = 0; $i < $length; $i++) {
            if ($i) {
                $min = 0;
            } else {
                $min = 1;
            }
            $randomNumber .= mt_rand($min, 9);
        }
        return $randomNumber;
    }
}

if (!function_exists('split_str_by_length')) {
    function split_str_by_length($str, $length = 20, $separator = '<br>')
    {
        $result = '';
        $strLen = mb_strlen($str); // 获取字符串长度
        // 循环截取
        for ($i = 0; $i < $strLen; $i += $length) {
            $result .= mb_substr($str, $i, $length).$separator;
        }
        // 移除最后一个多余的分隔符
        $result = rtrim($result, $separator);
        return $result;
    }
}
if (!function_exists('hide_str_by_length')) {
    function hide_str_by_length($str, $length = 20, $separator = '...')
    {
        $strLen = mb_strlen($str); // 获取字符串长度
        if ($strLen < $length) {
            $separator = '';
        }
        return mb_substr($str, 0, ($length - mb_strlen($separator))).$separator;
    }
}