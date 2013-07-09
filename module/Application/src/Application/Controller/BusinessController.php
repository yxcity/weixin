<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Model\Shop;
use module\Application\src\Model\Tool;

class BusinessController extends AbstractActionController
{
    function indexAction()
    {
    	$openid = $this->openid();
    	$domain = Tool::domain();
        if (! $openid) $this->redirect()->toRoute('user',array('action'=>'error'));
    	$viewData=array();
    	$db = new Shop($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
    	$res = $db->shopAll(sha1($domain));
    	$count = $res->count();
    	$viewData['pageCount']=ceil($count/5);
    	$viewData['openid']=$openid;
    	$viewData['rows']=$db->userShop(sha1($domain));
        $view = new ViewModel($viewData);
    	$view->setTerminal(true);
    	return $view;
    }
    
    function moreAction()
    {
        $openid = $this->openid();
        $domain = Tool::domain();
        if (! $openid) $this->redirect()->toRoute('user',array('action'=>'error'));
        $page = $this->params()->fromQuery('page');
    	$db = new Shop($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
    	$rows=$db->userShop(sha1($domain),5,$page);
    	$html ='';
    	if ($rows)
    	{
    	    foreach ($rows as $val) {
    			$html.="<li>
            			<a href=\"/stores?openid={$openid}&id={$val['id']}\">
            				<img src=\"{$val['thumb']}\" />
            				<h3>{$val['shopname']}</h3>
            			</a>
            			<p class=\"i_shop_phone\">
            				<a href=\"tel:{$val['tel']}\">{$val['tel']}</a>
            			</p>
            			<p class=\"i_shop_address\">
            				<a href=\"\">地址：{$val['address']}</a>
            			</p>
                        <span class=\"gt\"></span></li>";
    		}
    	}
    	echo $html;
    	exit();
    }
    
    function openid()
    {
    	return $this->params()->fromQuery('openid');;
    }
    
}