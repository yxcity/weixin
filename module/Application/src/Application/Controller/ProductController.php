<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use module\Application\src\Model\Tool;
use Admin\Model\Commodity;
use Admin\Model\Indent;
use Admin\Model\User;
use Admin\Model\Shop;
use Zend\Mvc\Controller\AbstractActionController;

class ProductController extends AbstractActionController
{
    function indexAction ()
    {
        $id = $this->params()->fromQuery('id');
        $openid = $this->openid();
        if (!$openid || !$id) $this->redirect()->toRoute('user',array('action'=>'error'));
        $domain = Tool::domain();
        $viewData = array();
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        // 取得商品信息
        $db = new Commodity($adapter);
        $row = $db->getCommodity($id);
        if (! $row || $row['uid']!=sha1($domain)) $this->redirect()->toRoute('user',array('action'=>'error'));
        $viewData['row'] = $row;
        // 取得门店信息
        //$shop = new Shop($adapter);
        //$viewData['shop'] = $shop->getShop($row['shop']);
        $shop = new Shop($adapter);
    	$res = $shop->shopAll(sha1($domain));
    	$countshop = $res->count();
        //取得其他同类商品
        $rows=$db->proList(5, 1, sha1($domain),$row['shop'],$row['cateID']);
        $viewData['rows']=$rows;
        $viewData['openid'] = $openid;
        $viewData['countshop'] = $countshop;
        $viewModel = new ViewModel($viewData);
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
    function listAction()
    {
        $domain = Tool::domain();
        $openid = $this->openid();
        $id = (int)$this->params()->fromQuery('id');
        if (!$openid || !$id) $this->redirect()->toRoute('user',array('action'=>'error'));
        $viewData=array();
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $db = new Commodity($adapter);
        $c=$db->cateCount(sha1($domain),$id);
        $viewData['pageCount'] = ceil($c->count()/5);
        $viewData['rows'] = $db->proList(5,1, sha1($domain),null,$id);
        $viewData['openid']=$openid;
        $viewData['id']=$id;
        $viewModel = new ViewModel($viewData);
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
    function searchAction()
    {
        $domain = Tool::domain();
        $openid = $this->openid();
        $id = $this->params()->fromQuery('id');
        $request = $this->getRequest();
        if ($request->isPost())
        {
    	    $postData = $request->getPost();
    		$keywords = Tool::filter($postData['keywords']);
        }
        if (!$openid || !$keywords) $this->redirect()->toRoute('user',array('action'=>'error'));
        $viewData=array();
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $db = new Commodity($adapter);
        $c=$db->keywordsCount(sha1($domain),$keywords);
        $viewData['pageCount'] = ceil($c->count()/5);
        $viewData['stotal'] = $c->count();
        $viewData['rows'] = $db->proList(5,1, sha1($domain),null,null,$keywords);
        $viewData['openid'] = $openid;
        $viewData['keywords'] = $keywords;
        $viewModel = new ViewModel($viewData);
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    function indentAction ()
    {
        $openid = $this->openid();
        $domain = Tool::domain();
        $id = $this->params()->fromQuery('id');
        if (! $openid || !$id) $this->redirect()->toRoute('user',array('action'=>'error'));
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $db = new Commodity($adapter);
        $row = $db->getCommodity($id);
        if (! $row || $row['uid']!=sha1($domain)) $this->redirect()->toRoute('user',array('action'=>'error'));
        $request = $this->getRequest();
        $user = new User($adapter);
        if ($request->isPost()) {
            $postData = $request->getPost();
            $in = new Indent($adapter);
            // 验证商品是否有订单
            $inRow = $in->clickIndent(sha1($domain), $openid, $id);
            if ($inRow) {
            	$amount = $inRow['amount'] + $postData['num'];
                $data['amount'] = $amount;
                $sum = $amount * $row['rebate'];
                $data['sum'] = $sum;
                $data['address']=$postData['address'];
                $data['addtime'] = time();
                $serialnumber = date("Ymd") . Tool::random(12, true);
                $data['serialnumber'] = $serialnumber;
                $name = $inRow['name'];
                $in->update($data, $inRow['id']);
            } else {
                //无订单，写入订单
                $name = $row['name'];
                $data['uid'] = $row['uid'];
                $data['buyer'] = $openid;
                $data['shop'] = $row['shop'];
                $data['pid'] = $id;
                $data['name'] = $row['name'];
                $data['address']=$postData['address'];
                $data['thumb'] = $row['thumb'];
                $data['amount'] = $postData['num'];
                $sum = $postData['num'] * $row['rebate'];
                $data['sum'] = $sum;
                $data['status'] = 2;
                $data['addtime'] = time();
                $data['payment'] = $postData['paytype'];
                $data['bank'] = isset($postData['bankType']) ? $postData['bankType'] : null;
                $serialnumber = date("Ymd") . Tool::random(12, true);
                $data['serialnumber'] = $serialnumber;
                $tid = $in->addIndent($data);
            }
            $this->redirect()->toUrl("/user/indent?openid={$openid}");
            /* // 创建支付
            $uRow = $user->clickDomain($row['uid']);
            if ($uRow['PID'] && $uRow['KEY'] && $uRow['alipayEmail']) {
            	
            	$aliapy = new Alipay();
            	$payData = array(
            			'serialnumber' => $serialnumber,
            			'id' => $productid,
            			'openid' => $this->user->username,
            			'do' => $this->user->domain,
            			'title' => Tool::hanzi($name),
            			'sum' => $sum,
            			'body' => Tool::hanzi($name),
            			'PID' => $uRow['PID'],
            			'KEY' => $uRow['KEY'],
            			'alipayEmail' => $uRow['alipayEmail']
            	);
            $aliapy->pay($payData); 
            }*/
        }
        
        $viewData = array();
        // 取得商品信息
        $viewData['row'] = $row;
        // 取得收货地址
        $viewData['rows'] = $user->addressList($openid);
        $viewData['openid']=$openid;
        $viewModel = new ViewModel($viewData);
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    function commentsAction()
    {
        $id = (int)$this->params()->fromQuery('id');
        $openid = $this->openid();
        $domain = Tool::domain();
        if (empty($id) || !$openid) $this->redirect()->toRoute('user',array('action'=>'error'));
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $viewData = array();
        $db = new Commodity($adapter);
        $row = $db->getCommodity($id);
        if (! $row || $row['uid']!=sha1($domain)) $this->redirect()->toRoute('user',array('action'=>'error'));
        $viewData['row']=$row;
        $viewData['openid']=$openid;
        $viewModel = new ViewModel($viewData);
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
    function openid()
    {
    	return $this->params()->fromQuery('openid');;
    }
}