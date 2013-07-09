<?php
namespace module\Application\src\Model;

class Alipay
{

    function createLinkstring ($para)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . $val . "&";
        }
        // 去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);
        
        // 如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }
        
        return $arg;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     * 
     * @param $para 需要拼接的数组
     *            return 拼接完成以后的字符串
     */
    function createLinkstringUrlencode ($para)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . urlencode($val) . "&";
        }
        // 去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);
        
        // 如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }
        
        return $arg;
    }

    /**
     * 除去数组中的空值和签名参数
     * 
     * @param $para 签名参数组
     *            return 去掉空值与签名参数后的新签名参数组
     */
    function paraFilter ($para)
    {
        $para_filter = array();
        while (list ($key, $val) = each($para)) {
            if ($key == "sign" || $key == "sign_type" || $val == "")
                continue;
            else
                $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    /**
     * 对数组排序
     * 
     * @param $para 排序前的数组
     *            return 排序后的数组
     */
    function argSort ($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
     * 注意：服务器需要开通fopen配置
     * 
     * @param $word 要写入日志里的文本内容
     *            默认值：空值
     */
    function logResult ($word = '')
    {
        $fp = fopen("log.txt", "a");
        flock($fp, LOCK_EX);
        fwrite($fp, "执行日期：" . strftime("%Y%m%d%H%M%S", time()) . "\n" . $word . "\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * 远程获取数据，POST模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * 
     * @param $url 指定URL完整路径地址            
     * @param $cacert_url 指定当前工作目录绝对路径            
     * @param $para 请求的数据            
     * @param $input_charset 编码格式。默认值：空值
     *            return 远程输出的数据
     */
    function getHttpResponsePOST ($url, $cacert_url, $para, $input_charset = '')
    {
        if (trim($input_charset) != '') {
            $url = $url . "_input_charset=" . $input_charset;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); // SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 严格认证
        curl_setopt($curl, CURLOPT_CAINFO, $cacert_url); // 证书地址
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
        curl_setopt($curl, CURLOPT_POST, true); // post传输数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $para); // post传输数据
        $responseText = curl_exec($curl);
        // var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);
        
        return $responseText;
    }

    /**
     * 远程获取数据，GET模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * 
     * @param $url 指定URL完整路径地址            
     * @param $cacert_url 指定当前工作目录绝对路径
     *            return 远程输出的数据
     */
    function getHttpResponseGET ($url, $cacert_url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); // SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 严格认证
        curl_setopt($curl, CURLOPT_CAINFO, $cacert_url); // 证书地址
        $responseText = curl_exec($curl);
        // var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);
        
        return $responseText;
    }

    /**
     * 实现多种字符编码方式
     * 
     * @param $input 需要编码的字符串            
     * @param $_output_charset 输出的编码格式            
     * @param $_input_charset 输入的编码格式
     *            return 编码后的字符串
     */
    function charsetEncode ($input, $_output_charset, $_input_charset)
    {
        $output = "";
        if (! isset($_output_charset))
            $_output_charset = $_input_charset;
        if ($_input_charset == $_output_charset || $input == null) {
            $output = $input;
        } elseif (function_exists("mb_convert_encoding")) {
            $output = mb_convert_encoding($input, $_output_charset, $_input_charset);
        } elseif (function_exists("iconv")) {
            $output = iconv($_input_charset, $_output_charset, $input);
        } else
            die("sorry, you have no libs support for charset change.");
        return $output;
    }

    /**
     * 实现多种字符解码方式
     * 
     * @param $input 需要解码的字符串            
     * @param $_output_charset 输出的解码格式            
     * @param $_input_charset 输入的解码格式
     *            return 解码后的字符串
     */
    function charsetDecode ($input, $_input_charset, $_output_charset)
    {
        $output = "";
        if (! isset($_input_charset))
            $_input_charset = $_input_charset;
        if ($_input_charset == $_output_charset || $input == null) {
            $output = $input;
        } elseif (function_exists("mb_convert_encoding")) {
            $output = mb_convert_encoding($input, $_output_charset, $_input_charset);
        } elseif (function_exists("iconv")) {
            $output = iconv($_input_charset, $_output_charset, $input);
        } else
            die("sorry, you have no libs support for charset changes.");
        return $output;
    }
    
    
    function md5Sign($prestr, $key) {
    	$prestr = $prestr . $key;
    	return md5($prestr);
    }
    
    /**
     * 验证签名
     * @param $prestr 需要签名的字符串
     * @param $sign 签名结果
     * @param $key 私钥
     * return 签名结果
     */
    function md5Verify($prestr, $sign, $key) {
    	$prestr = $prestr . $key;
    	$mysgin = md5($prestr);
    
    	if($mysgin == $sign) {
    		return true;
    	}
    	else {
    		return false;
    	}
    }
    
    function pay($payData)
    {
        //合作身份者id，以2088开头的16位纯数字
        $alipay_config['partner']		= trim($payData['PID']);//'2088002691290115';
        
        //安全检验码，以数字和字母组成的32位字符
        $alipay_config['key']			= trim($payData['KEY']);//'4w3k3a58zw885itiuerm6q0f77mi4xe2';
        
        
        //↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        
        
        //签名方式 不需修改
        $alipay_config['sign_type']    = strtoupper('MD5');
        
        //字符编码格式 目前支持 gbk 或 utf-8
        $alipay_config['input_charset']= strtolower('utf-8');
        
        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        $alipay_config['cacert']    = getcwd().'\\cacert.pem';
        
        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $alipay_config['transport']    = 'http';
        
        //支付类型
        $payment_type = "1";
        //必填，不能修改
        //服务器异步通知页面路径
        $notify_url = $payData['notify_url'];  //  BASE_URL."/alipay/{$payData['serialnumber']}";
        //需http://格式的完整路径，不能加?id=123这类自定义参数
        
        //页面跳转同步通知页面路径(支付成功跳转)
        $return_url = $payData['return_url']; //BASE_URL."/alipay/{$payData['serialnumber']}";
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
        
        //卖家支付宝帐户
        $seller_email = trim($payData['alipayEmail']); //'ryalong@163.com';
        //必填
        
        //账号订单号
        $out_trade_no = trim($payData['serialnumber']);
        //账号网站订单系统中唯一订单号，必填
        
        //订单名称
        $subject = trim($payData['title']);
        //必填
        
        //付款金额
        $total_fee = trim($payData['sum']);
        //必填
        
        //订单描述
        
        $body = trim($payData['body']);
        //商品展示地址
        $show_url = $payData['show_url']; //BASE_URL."/product?openid={$payData['openid']}&id={$payData['id']}";
        //需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html
        
        //防钓鱼时间戳
        $anti_phishing_key = "";
        //若要使用请调用类文件submit中的query_timestamp函数
        
        //客户端的IP地址
        $exter_invoke_ip = "";
        //非局域网的外网IP地址，如：221.0.0.1
        
        
        /************************************************************/
        
        //构造要请求的参数数组，无需改动
        $parameter = array(
        		"service" => "create_direct_pay_by_user",
        		"partner" => trim($alipay_config['partner']),
        		"payment_type"	=> $payment_type,
        		"notify_url"	=> $notify_url,
        		"return_url"	=> $return_url,
        		"seller_email"	=> $seller_email,
        		"out_trade_no"	=> $out_trade_no,
        		"subject"	=> $subject,
        		"total_fee"	=> $total_fee,
        		"body"	=> $body,
        		"show_url"	=> $show_url,
        		"anti_phishing_key"	=> $anti_phishing_key,
        		"exter_invoke_ip"	=> $exter_invoke_ip,
        		"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
        );
        
        //建立请求
        $alipaySubmit = new Alipaysubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
        echo $html_text;
    }
}