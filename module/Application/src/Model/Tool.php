<?php
namespace module\Application\src\Model;

use Zend\Log\Writer\Stream;
use Zend\Log\Logger;
use Zend\Session\Config\StandardConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Filter\StringTrim;
use Zend\Filter\HtmlEntities;
use Zend\Filter\StripTags;
use Zend\Validator\File\Size;
use Zend\File\Transfer\Adapter\Http;

final class Tool
{

    private static $_instance = null;

    private function __construct ()
    {}

    private function __clone ()
    {}

    public static function getInstance ()
    {
        if (is_null(self::$_instance) || ! isset(self::$_instance)) {
            self::$_instance = new self();
        }
    }

    /**
     *
     * @todo 验证网址
     * @param unknown $url            
     * @return number
     */
    static function checkUrl ($url)
    {
        return preg_match('/^http(s)*:\/\/[_a-zA-Z0-9-]+(.[_a-zA-Z0-9-]+)*$/', $url);
    }
    /**
     * @todo 过滤非汉字
     * @param String $str
     * @return string
     */
    static function hanzi($str)
    {
       // $str = mb_convert_encoding($str, 'UTF-8', 'GB2312');
        preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $str, $matches);
        $str = join('', $matches[0]);
       // $str = mb_convert_encoding($str, 'GB2312', 'UTF-8');
        return $str;
    }
    

    /**
     *
     * @todo 设置 SESSION
     * @param unknown $data            
     * @return boolean
     */
    static function setSession ($name, $data)
    {
        if (! $data || ! is_array($data))
            return false;
        $config = new StandardConfig();
        $config->setOptions(array(
            'remember_me_seconds' => 1800,
            'name' => 'zf2'
        ));
        $manager = new SessionManager($config);
        Container::getDefaultManager($manager);
        $container = new Container($name);
        foreach ($data as $key => $val) {
            $container->$key = $val;
        }
        
        return true;
    }

    /**
     *
     * @todo 取得SESSION
     * @param String $key            
     * @return Ambigous <boolean, unknown>
     */
    static function getSession ($name, $key)
    {
        $container = new Container($name);
        $data = $container->$key;
        return $data ? $data : false;
    }

    /**
     * @文本过滤
     *
     * @param unknown $string            
     * @param string $tag            
     * @return string
     */
    static function filter ($string, $tag = false)
    {
        $trim = new StringTrim();
        $html = new HtmlEntities();
        $string = $trim->filter($string);
        $string = $html->filter($string);
        if ($tag) {
            $tags = new StripTags();
            $string = $tags->filter($string);
        }
        return $string;
    }

    /**
     *
     * @todo 写入日志信息
     * @param string $string            
     * @param string $filename            
     * @throws \Exception
     */
    public static function log ($string, $filename = null)
    {
        $filename = $filename ? $filename : date('Y-m-d', time()) . '_' . rand(10000, 99999) . '.log';
        $stream = @fopen("data/{$filename}", 'w', false);
        if (! $stream) {
            throw new \Exception('文件文件不存在');
        }
        $writer = new Stream($stream);
        $logger = new Logger();
        $logger->addWriter($writer);
        $logger->info(PHP_EOL . $string);
    }

    /**
     *
     * @todo 设置 COOKIE
     * @param String $name            
     * @param unknown $value            
     * @param string $time            
     */
    public static function setCookie ($name, $value, $time = null)
    {
        $domain = isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:'weixin.youtitle.com'; // cookie
        $time = $time ? $time : time() + 7200; // expires for cookie
        if (is_array($value)) {
            $value = json_encode($value);
        }
        setcookie($name, $value, $time, '/', $domain);
    }

    /**
     *
     * @todo 取得COOKIE
     * @param unknown $key            
     * @return Ambigous <boolean, unknown>
     */
    public static function getCookie ($key)
    {
        $data = isset($_COOKIE[$key]) ? $_COOKIE[$key] : false;
        return $data;
    }

    /**
     *
     * @todo 生成目录
     * @param string $string            
     * @return boolean
     */
    static public function mkdir ($string)
    {
        $pattern = '/^([\S]+\/)+/';
        if (preg_match($pattern, $string)) {
            $fullPath = "";
            $dirArray = explode("/", $string);
            foreach ($dirArray as $each_d) {
                $fullPath .= $each_d . "/";
                if (! is_dir($fullPath)) {
                    @mkdir($fullPath, 511);
                    @fclose(@fopen($string . '/index.html', 'w'));
                }
            }
            return true;
        }
        return false;
    }

    static function uploadfile ($field, $dirname = null, $filename = null)
    {
        if (! isset($field['fileField']['name']))
            return array(
                'res' => 0,
                'msg' => '文件未上传'
            );
        $size = new Size(array(
            'size' => 20
        ));
        $adapter = new Http();
        $adapter->setValidators(array(
            $size
        ), $field['fileField']['name']);
        if (! $adapter->isValid()) {
            $dataError = $adapter->getMessages();
            $error = array();
            foreach ($dataError as $key => $row) {
                $error[] = $row;
            }
            return array(
                'res' => 0,
                'msg' => $error
            );
        } else {
        	$dirname = $dirname ? $dirname : '/uploads/temp/';
        	self::mkdir(BASE_PATH . $dirname);
            $adapter->setDestination(BASE_PATH . $dirname);
            $filename=$field['fileField']['name'];
            if ($adapter->receive($filename)) {
                $filename=self::mvFile($dirname . $filename,true);
                return array(
                    'res' => 1,
                    'file' =>$filename
                );
            }
        }
    }

    /**
     * 取得随机数
     *
     * @param int $length            
     * @param boolean $isNum            
     * @return string
     */
    static public function random ($length, $isNum = FALSE)
    {
        $random = '';
        $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $num = '0123456789';
        if ($isNum) {
            $sequece = 'num';
        } else {
            $sequece = 'str';
        }
        $max = strlen($$sequece) - 1;
        for ($i = 0; $i < $length; $i ++) {
            $random .= ${
    			$sequece}{mt_rand(0, $max)};
        }
        return $random;
    }

    /**
     * 截取指定字符串
     *
     * @param string $string            
     * @param int $sublen            
     * @param string $add            
     * @param int $start            
     * @param string $code            
     * @return string
     */
    static function cutStr ($string, $sublen, $add = '&#8230;', $start = 0, $code = 'UTF-8')
    {
        if ($code == 'UTF-8') {
            $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
            preg_match_all($pa, $string, $t_string);
            
            if (count($t_string[0]) - $start > $sublen)
                return join('', array_slice($t_string[0], $start, $sublen)) . $add;
            return join('', array_slice($t_string[0], $start, $sublen));
        } else {
            $start = $start * 2;
            $sublen = $sublen * 2;
            $strlen = strlen($string);
            $tmpstr = '';
            
            for ($i = 0; $i < $strlen; $i ++) {
                if ($i >= $start && $i < ($start + $sublen)) {
                    if (ord(substr($string, $i, 1)) > 129) {
                        $tmpstr .= substr($string, $i, 2);
                    } else {
                        $tmpstr .= substr($string, $i, 1);
                    }
                }
                if (ord(substr($string, $i, 1)) > 129)
                    $i ++;
            }
            if (strlen($tmpstr) < $strlen)
                $tmpstr .= $add;
            return $tmpstr;
        }
    }

    static function isEMAIL ($value)
    {
        return preg_match('~^[a-z0-9]+([._\-\+]*[a-z0-9]+)*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+\.)+[a-z0-9]+$~i', $value);
    }
    
    // 文件后缀
    static function fext ($filename, $all = false)
    {
        $info = pathinfo($filename);
        if ($all)
            return $info;
        return $info['extension'];
    }

    /**
     *
     * @todo 验证文件是否是图片文件
     *      
     * @param string $imgpath            
     * @return boolean
     */
    static function isImg ($imgpath)
    {
        return (strpos($imgpath, '..') !== FALSE || ! file_exists($imgpath) || ! in_array(strtolower(self::Fext($imgpath)), array(
            'jpg',
            'jpeg',
            'bmp',
            'gif',
            'png'
        )) || (function_exists('getimagesize') && ! @getimagesize($imgpath))) ? false : true;
    }

    static function mvFile ($file, $isDel = null, $dirname = null, $filename = null)
    {
        $file = BASE_PATH . $file;
        if (self::isImg($file)) {
            $dirname = $dirname ? $dirname : '/uploads/' . date('Ymd', time()) . '/';
            Tool::mkdir(BASE_PATH.$dirname);
            $filename = $filename ? $filename : self::random(10) . '.' . self::fext($file);
            @copy($file, BASE_PATH . $dirname . $filename);
            if ($isDel) {
                @unlink($file);
            }
            return $dirname . $filename;
        }
        return false;
    }
    
    /*
     * 求两个已知经纬度之间的距离,单位为米 @param lng1,lng2 经度 @param lat1,lat2 纬度 @return float 距离，单位米
     */
    static function getdistance ($lng1, $lat1, $lng2, $lat2) // 根据经纬度计算距离
    {
        
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1);
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2; // 两纬度之差,纬度<90
        $b = $radLng1 - $radLng2; // 两经度之差纬度<180
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        $s = ceil($s);
        /*
         * if($s<1000){ $danwei = "米"; }else{ $s = round(($s/1000),1); $danwei = "公里"; } $s .= $danwei;
         */
        return $s;
    }
    /**
     * @todo 数组排序
     * @param Array $arr
     * @param string $keys
     * @param string $type
     * @return multitype:unknown
     */

    static function arraySort ($arr, $keys, $type = 'asc')
    {
        $keysvalue = $newArray = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if ($type == 'asc') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $newArray[$k] = $arr[$k];
        }
        return $newArray;
    }
    /**
     * @取得二级域名
     * @param String $host
     * @return mixed
     */
    static function domain ($host=null)
    {
        if (!$host)
        {
            $host = self::getHost();
        }
        $d=self::getDomain($host);
        $domain=str_replace(".{$d}",'', $host);
        return $domain;
    }
   /**
    * @todo 取得URl下的域名
    */
    
    static function getDomain($url=null)
    {
        if (!$url)
        {
        	$url = self::getHost();
        }
        $pattern = '/[\w-]+\.(com|net|org|gov|cc|biz|info|cn|me|edu|int|us)(\.(cn|hk|tw))*/';
        preg_match ( $pattern, $url, $matches );
        if (count ( $matches ) > 0) {
        	return $matches [0];
        } else {
        	$rs = parse_url ( $url );
        	$main_url = $rs ["host"];
        	if (! strcmp ( long2ip ( sprintf ( "%u", ip2long ( $main_url ) ) ), $main_url )) {
        		return $main_url;
        	} else {
        		$arr = explode ( ".", $main_url );
        		$count = count ( $arr );
        		$endArr = array (
        				"com",
        				"net",
        				"org",
        		        "edu"
        		); // com.cn net.cn 等情况
        		if (in_array ( $arr [$count - 2], $endArr )) {
        			$domain = $arr [$count - 3] . "." . $arr [$count - 2] . "." . $arr [$count - 1];
        		} else {
        			$domain = $arr [$count - 2] . "." . $arr [$count - 1];
        		}
        		return $domain;
        	}
        }
    }
    
    /**
     * @取得当前HOST地址
     * @return unknown
     */
    static function getHost(){
    	$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME'];
    	return $host;
    }
    
    
    /**
     * @todo 写入文件
     * @param String $filename
     * @param String $content
     * @param string $mode
     * @param number $chmod
     * @return boolean
     */
    static function writeFile($filename, $content, $mode = 'ab', $chmod = 1) {
    	$fp = @fopen ( $filename, $mode );
    	if ($fp) {
    		flock ( $fp, LOCK_EX );
    		fwrite ( $fp, $content );
    		fclose ( $fp );
    		$chmod && @chmod ( $filename, 0666 );
    		return true;
    	}
    	return false;
    }
    /**
     * @todo 读取文件
     * @param String $filename
     * @return string|boolean
     */
    static function readFile($filename) {
    	$fp = @fopen ( $filename, 'r' );
    	if ($fp) {
    		$content = fread ( $fp, filesize ( $filename ) );
    		fclose ( $fp );
    		return $content;
    	}
    	return false;
    }
    /**
     * @todo 取得代码对呀信息
     * @param Int $key
     */
    static function errorCode($key)
    {
    	$ini = include './config/error.php';
    	if (isset($ini[$key]))
    	{
    		return $ini[$key];
    	}
    	return false;
    }
    
    
    
}