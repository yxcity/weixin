<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Model\User;
use module\Application\src\Model\Tool;
use Admin\Model\Indent;
use module\Application\src\Model\Alipay;

class UserController extends AbstractActionController
{

    /**
     *
     * @todo 用户首页
     *       (non-PHPdoc)
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    function indexAction ()
    {
        $openid = $this->openid();
        if (! $openid)
            $this->redirect()->toRoute('user', array(
                'action' => 'error'
            ));
        $viewModel['openid'] = $openid;
        $view = new ViewModel($viewModel);
        $view->setTerminal(true);
        return $view;
    }

    /**
     *
     * @todo 错误提示
     * @return \Zend\View\Model\ViewModel
     */
    function addressAction ()
    {
        $openid = $this->openid();
        if (! $openid)
            $this->redirect()->toRoute('user', array(
                'action' => 'error'
            ));
        $request = $this->getRequest();
        $viewData = array();
        $id = (int) $this->params()->fromQuery('id');
        $viewData['id'] = $id;
        if ($request->isPost()) {
            $postData = $request->getPost();
            $data['uid'] = $openid;
            $data['name'] = $postData['name'];
            $data['phone'] = $postData['phone'];
            $data['province'] = $postData['province'];
            $data['city'] = $postData['city'];
            $data['area'] = $postData['area'];
            $data['address'] = $postData['address'];
            $data['zipcode'] = $postData['zipcode'];
            $default = isset($postData['default']) ? 1 : 0;
            $data['default'] = $default;
            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $db = new User($adapter);
            if ($default) {
                $db->defaultAddress(array(
                    'default' => 0
                ), $openid);
            }
            $tid = $db->addaddress($data);
            if ($tid) {
                $this->redirect()->toUrl("/product/indent?openid={$openid}&id={$id}");
            }
        }
        $viewData['openid'] = $openid;
        $view = new ViewModel($viewData);
        $view->setTerminal(true);
        return $view;
    }

    /**
     *
     * @todo 用户错误提示
     * @return \Zend\View\Model\ViewModel
     */
    function errorAction ()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    /**
     *
     * @todo 联系我们
     * @return \Zend\View\Model\ViewModel
     */
    function contactAction ()
    {
        $openid = $this->openid();
        if (! $openid)
        	$this->redirect()->toRoute('user', array(
        		'action' => 'error'
        	));
        $viewModel['openid'] = $openid;
        $view = new ViewModel($viewModel);
        $view->setTerminal(true);
        return $view;
    }

    /**
     *
     * @todo 关于我们
     * @return \Zend\View\Model\ViewModel
     */
    function aboutAction ()
    {
        $openid = $this->openid();
        if (! $openid)
        	$this->redirect()->toRoute('user', array(
        		'action' => 'error'
        	));
        $viewModel['openid'] = $openid;
        $view = new ViewModel($viewModel);
        $view->setTerminal(true);
        return $view;
    }

    function indentAction ()
    {
        $openid = $this->openid();
        $domain = Tool::domain();
        if (! $openid)
            $this->redirect()->toRoute('user', array(
                'action' => 'error'
            ));
        $viewData = array();
        $db = new Indent($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        $viewData['status'] = $db->indentStatus();
        $s = $this->params()->fromQuery('s', null);
        $viewData['s'] = $s;
        if ($s == 1) {
            $s = "'1','4'";
        }
        $rows = $db->userIndent(sha1($domain), $openid, $s);
        $viewData['rows'] = $rows;
        $viewData['count'] = $rows->count();
        $viewData['openid'] = $openid;
        $view = new ViewModel($viewData);
        $view->setTerminal(true);
        return $view;
    }

    /**
     *
     * @todo 订单详情
     * @return \Zend\View\Model\ViewModel
     */
    function waybillAction ()
    {
        $openid = $this->openid();
        $domain = Tool::domain();
        $id = (int) $this->params()->fromQuery('id');
        if (! $openid || !$id)
            $this->redirect()->toRoute('user', array(
                'action' => 'error'
            ));
        $viewData = array();
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $db = new Indent($adapter);
        $row = $db->getIndent($id);
        if ($row['uid'] != sha1($domain) || $row['buyer'] != $openid)
            $this->redirect()->toRoute('user', array(
                'action' => 'error'
            ));
        $viewData['row'] = $row;
        if ($row['address']) {
            $user = new User($adapter);
            $viewData['address'] = $user->getAddress($row['address']);
        }
        $viewData['status'] = $db->indentStatus();
        $viewData['express'] = $db->express();
        $viewData['openid'] = $openid;
        $view = new ViewModel($viewData);
        $view->setTerminal(true);
        return $view;
    }

    /**
     * @支付宝 支付回调
     */
    function alipayAction ()
    {
        $serialnumber = $this->params('serialnumber');
        if (! $serialnumber)
            exit();
        $db = new Indent($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        $db->update(array(
            'status' => 1,
            'payTime' => time()
        ), null, $serialnumber);
        $this->redirect()->toRoute('user', array(
            'action' => 'indent'
        ));
        // exit();
    }

    /**
     *
     * @todo 订单支付
     */
    function payAction ()
    {
        $id = (int) $this->params()->fromQuery('id');
        $openid = $this->openid();
        if (! $id || ! $openid)
            $this->redirect()->toRoute('user', array(
                'action' => 'error'
            ));
        $domain = Tool::domain();
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $db = new Indent($adapter);
        $row = $db->getIndent($id);
        if (! $row || $row['uid'] != sha1($domain) || $row['buyer'] != $openid)
            $this->redirect()->toRoute('user', array(
                'action' => 'error'
            ));
        
        $user = new User($adapter);
        $uRow = $user->clickDomain(sha1($domain));
        if ($uRow['PID'] && $uRow['KEY'] && $uRow['alipayEmail']) {
            // 创建支付
           
            $payData = array(
                'serialnumber' => $row['serialnumber'],
                'title' => Tool::hanzi($row['name']),
                'sum' => $row['sum'],
                'body' => Tool::hanzi($row['name']),
                'PID' => $uRow['PID'],
                'KEY' => $uRow['KEY'],
                'alipayEmail' => $uRow['alipayEmail'],
                'notify_url'=>BASE_URL."/alipay/{$row['serialnumber']}",
                'return_url'=>BASE_URL."/alipay/{$row['serialnumber']}",
                'show_url'=>BASE_URL."/product?openid={$openid}&id={$row['pid']}"
            );
            $aliapy = new Alipay();
            $aliapy->pay($payData);
        } else {
            $this->redirect()->toUrl("/user/indent?openid={$openid}");
        }
    }

    function openid ()
    {
        return $this->params()->fromQuery('openid');
    }
}