<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use module\Application\src\Model\Api;
use Admin\Model\Commodity;
use Admin\Model\Shop;
use Admin\Model\User;
use module\Application\src\Model\Tool;
class ApiController extends AbstractActionController
{

    private $token;
    private $domain;
    private $post;
    private $adapter;
    
    function __construct ()
    {
        $this->post = $this->getPost();
    }

    function indexAction ()
    {
        $echostr = isset($_GET['echostr'])?$_GET['echostr']:null;
        $domain=Tool::domain();
        $this->adapter=$this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        //接入验证
        if ($echostr)
        {
        	$db = new User($this->adapter);
        	$row = $db->clickDomain(sha1($domain));
        	if ($row)
        	{
        	    $this->token = $row['token'];
        	    echo $echostr;
        	}else 
        	{
        		echo 'Error';
        	}
        	exit();
        }
        if ($this->post) {
            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $this->saveData($this->post, $adapter,$domain);
            $data = array();
            // 关键字
            if ($this->post->MsgType == 'text') {
                $keyword = $this->post->Content;
                $content = $this->getContent($adapter, $keyword,$domain);
                if (!$content)
                {
                    $db = new User($this->adapter);
                    $row = $db->clickDomain(sha1($domain));
                    if($row['nodata'])
                    {
                        $str = $row['nodata'].'直接点击这里进入广场 <a href="'.BASE_URL.'?openid='.$this->post->FromUserName.'">体验</a>';
                    }else{
                        $str = '暂时没有您要的结果，您的问题我们已经收到，我们会尽快处理，请直接点击这里进入广场 <a href="'.BASE_URL.'?openid='.$this->post->FromUserName.'">体验</a>';
                    }
                    $content = $this->postContent($this->post->FromUserName, $this->post->ToUserName, $str);
                }
            }
            
            // 地理位置
            if ($this->post->MsgType == 'location') {
                $y = $this->post->Location_X;
                $x = $this->post->Location_Y;
                $db = new Shop($adapter);
                $rows=$db ->location($x,$y,sha1($domain));
                if ($rows)
                {
                    $i = 0;
                    foreach ($rows as $key=>$val) {
                        if ($i>=10) continue;
                        if($key==0){
                		$data[$key]['title']=$val['shopname'];
                        }else{
                            if($val['range']<1000){
                            	$danwei = "米";
                            }else{
                            	$val['range'] = round(($val['range']/1000),1);
                            	$danwei = "公里";
                            }
                		$data[$key]['title']=$val['shopname']."\n距离:{$val['range']}{$danwei}";
                        }
                		$data[$key]['description']=strip_tags(htmlspecialchars_decode(stripcslashes($val['content'])));
                		$data[$key]['pic']=Tool::isImg(BASE_PATH.$val['thumb'])?BASE_URL."{$val['thumb']}":BASE_URL.'/images/shop.jpg';
                		$data[$key]['url']=BASE_URL."/stores?id={$val['id']}&openid={$this->post->FromUserName}";
                		$i++;
                	}
                	$content = $this->postPic($this->post->FromUserName, $this->post->ToUserName, $data);
                }
            }
            
            // 事件处理
            
            if ($this->post->MsgType == 'event' && $this->post->Event == 'subscribe')
            {
            	$user = new User($this->adapter);
            	$row = $user->clickDomain(sha1($domain));
            	if ($row['wc']==2)
            	{
            	    $content = $this->getContent($adapter, null, $domain,true);
            	    if (!$content)
            	    {
                        if($row['nodata'])
                        {
                        	$str = $row['nodata'].'直接点击这里进入广场 <a href="'.BASE_URL.'?openid='.$this->post->FromUserName.'">体验</a>';
                        }else{
                        	$str = '暂时没有您要的结果，您的问题我们已经收到，我们会尽快处理，请直接点击这里进入广场 <a href="'.BASE_URL.'?openid='.$this->post->FromUserName.'">体验</a>';
                        }
                        $content = $this->postContent($this->post->FromUserName, $this->post->ToUserName, $str);
            	    }
            	}else 
            	{
            		$str = $row['welcome'] ? htmlspecialchars_decode(stripcslashes($row['welcome'])) : '欢迎关注，精彩从现在开始';
            		$content = $this->postContent($this->post->FromUserName, $this->post->ToUserName, $str);
            	}
                
            }
            echo $content;
        }
        exit();
    }

    function getContent($adapter,$keyword=null,$domain=null,$welcome=null)
    {
        $data = array();
        $db = new Commodity($adapter);
        $rows = $db->keyList($keyword,sha1($domain),$welcome);
        if ($welcome && !$rows->count()) return false;
        if ($rows->count()) {
        	$i=0;
        	foreach ($rows as $key => $val) {
        		if($i>=10) continue;
        		$data[$key]['title'] = $val['name']."[商品]";
        		$data[$key]['description'] = strip_tags(htmlspecialchars_decode(stripcslashes($val['weixin'])));
        		$data[$key]['pic'] = Tool::isImg(BASE_PATH.$val['thumb'])?BASE_URL. $val['thumb']:BASE_URL.'/images/29.jpg';
        		$data[$key]['url'] = BASE_URL."/product?openid={$this->post->FromUserName}&id={$val['id']}";
        		$i++;
        	}
        	
        	if ($welcome)
        	{
        		$content = $this->postPic($this->post->FromUserName, $this->post->ToUserName, $data);
        		return $content;
        	}
        }
        if($rows->count()<='10'){
        	$dbs = new shop($adapter);
        	$rowss = $dbs->keyList($keyword,sha1($domain));
        	if ($rowss->count()) {
        		$i=0;
        		foreach ($rowss as $key => $val) {
        			if(($i+$rows->count())>=10) continue;
        			$datalist[$key]['title'] = $val['shopname']."[门店]";
        			$datalist[$key]['description'] = strip_tags(htmlspecialchars_decode(stripcslashes($val['content'])));
        			$datalist[$key]['pic'] = Tool::isImg(BASE_PATH.$val['thumb'])?BASE_URL . $val['thumb']:BASE_URL.'/images/29.jpg';
        			$datalist[$key]['url'] = BASE_URL."/stores?openid={$this->post->FromUserName}&id={$val['id']}";
        			$i++;
        		}
        		$data=array_merge($data,$datalist);
        	}
        }
        $content = $this->postPic($this->post->FromUserName, $this->post->ToUserName, $data);
        
        if(!$rows->count() && !$rowss->count()) return false;
        return $content;
    }

    private function postContent ($to, $form, $string)
    {
        $content = "<xml><ToUserName><![CDATA[{$to}]]></ToUserName><FromUserName><![CDATA[{$form}]]></FromUserName><CreateTime>" . time() . "</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[{$string}]]></Content><FuncFlag>0</FuncFlag></xml>";
        return $content;
    }

    private function postMusic ($to, $form, $data)
    {
        $content = " <xml><ToUserName><![CDATA[{$to}]]></ToUserName><FromUserName><![CDATA[{$form}]]></FromUserName><CreateTime>" . time() . "</CreateTime><MsgType><![CDATA[music]]></MsgType><Music><Title><![CDATA[{$data['title']}]]></Title><Description><![CDATA[{$data['description']}]]></Description><MusicUrl><![CDATA[{$data['url']}]]></MusicUrl><HQMusicUrl><![CDATA[{$data['HQurl']}]]></HQMusicUrl></Music><FuncFlag>0</FuncFlag></xml>";
        return $content;
    }

    private function postPic ($to, $form, $data)
    {
        $content = "<xml><ToUserName><![CDATA[{$to}]]></ToUserName><FromUserName><![CDATA[{$form}]]></FromUserName><CreateTime>" . time() . "</CreateTime><MsgType><![CDATA[news]]></MsgType><ArticleCount>" . count($data) . "</ArticleCount><Articles>";
        foreach ($data as $val) {
            $content .= "<item><Title><![CDATA[{$val['title']}]]></Title><Description><![CDATA[{$val['description']}]]></Description><PicUrl><![CDATA[{$val['pic']}]]></PicUrl><Url><![CDATA[{$val['url']}]]></Url></item>";
        }
        $content .= "</Articles><FuncFlag>1</FuncFlag></xml>";
        return $content;
    }
    /**
     * @todo 保存用户提交
     * @param array $data
     * @param array $adapter
     */
    private function saveData ($data,$adapter,$uid=null)
    {
        $apiDb = new Api($adapter);
        return $apiDb->saveData($data,sha1($uid));
    }

    /**
     *
     * @todo 取得微信服务器POST的数据
     * @return SimpleXMLElement boolean
     */
    private function getPost ()
    {
        $post = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : null;
        if (! empty($post)) {
            $post = simplexml_load_string($post, 'SimpleXMLElement', LIBXML_NOCDATA);
            return $post;
        }
        return false;
    }

    /**
     * 验证是否来自微信服务器
     *
     * @return boolean
     */
    private function checkSignature ()
    {
        $signature = isset($_GET["signature"]) ? $_GET["signature"] : null;
        $timestamp = isset($_GET["timestamp"]) ? $_GET["signature"] : null;
        $nonce = isset($_GET["nonce"]) ? $_GET["nonce"] : null;
        $tmpArr = array(
            $this->token,
            $timestamp,
            $nonce
        );
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
    
   
}