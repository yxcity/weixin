<?php
namespace Admin\Model;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class User
{

    private $adapter;

    function __construct ($adapter)
    {
        $this->adapter = $adapter;
    }

    function auth ($data)
    {
        $authAdapter = new AuthAdapter($this->adapter);
        $authAdapter->setTableName('users')
            ->setIdentityColumn('username')
            ->setCredentialColumn('password')
            ->setIdentity($data['username'])
            ->setCredential($data['password']);
        $authAdapter->authenticate();
        $res = $authAdapter->getResultRowObject(array(
            'id',
            'realname',
            'domain',
            'power',
            'alipay'
        ));
        return $res;
    }

    /**
     *
     * @todo 添加账号
     * @param unknown $data            
     * @return Ambigous <boolean, number, \Zend\Db\TableGateway\mixed>
     */
    function addUser ($data)
    {
        $table = new TableGateway('users', $this->adapter);
        $table->insert($data);
        $tid = $table->getLastInsertValue();
        return $tid ? $tid : false;
    }

    /**
     *
     * @todo 编辑账号
     * @param Int $id            
     * @param Array $data            
     */
    function editUser ($id, $data)
    {
        $id = (int) $id;
        if ($id) {
            $table = new TableGateway('users', $this->adapter);
            $table->update($data, array('id' => $id));
            return true;
        }
        return false;
    }

    /**
     *
     * @todo 取得单条账号信息
     * @param int $id            
     * @return Ambigous <boolean, multitype:, ArrayObject, NULL, \ArrayObject, unknown>|boolean
     */
    function getUser ($id = null, $username = null)
    {
        $table = new TableGateway('users', $this->adapter);
        if ($id) {
            $where = array(
                'id' => $id
            );
        }
        if ($username) {
            $where = array(
                'username' => $username
            );
        }
        $rowset = $table->select($where);
        $row = $rowset->current();
        return $row ? $row : false;
    }
    /**
     * @todo 按访问地址取得用户
     * @param String $domain
     * @return Ambigous <boolean, multitype:, ArrayObject, NULL, \ArrayObject, unknown>
     */
    function clickDomain($domain){
    	$table = new TableGateway('users', $this->adapter);
    	$rowSet = $table->select("domain = '{$domain}' AND power >= '2'");
    	$row = $rowSet->current();
    	return $row?$row:false;
    }

    /**
     *
     * @todo 取得账号列表
     * @param unknown $page            
     * @return \Zend\Paginator\Paginator
     */
    function userList ($page,$user)
    {
        $select = new Select('users');
        $select->columns(array(
            'id',
            'username',
            'realname',
            'email',
            'addtime',
            'validity',
            'power'
        ));
        if ($user->power < 3)
        {
            $select->where(array('uid' => $user->id));
        }
        if ($user->power==3)
        {
        	$select->where(array('uid'=>0));
        }
        $select->order('id desc');
        $adapter = new DbSelect($select, $this->adapter);
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(30)->setCurrentPageNumber($page);
        return $paginator;
    }
    /**
     * @todo 删除账号
     * @param int $id
     * @return boolean
     */
    function delUser($id)
    {
    	$id = (int)$id;
        $table = new TableGateway('users', $this->adapter);
    	$table->delete(array('id'=>$id));
    	return true;
    }
    
    /**
     * @todo 添加收件地址
     * @param unknown $data
     * @return Ambigous <boolean, number>
     */
    function addaddress($data)
    {
    	$table = new TableGateway('address', $this->adapter);
    	$table->insert($data);
    	$tid = $table->getLastInsertValue();
    	return $tid?$tid:false;
    }
    /**
     * @todo 读取用户收件地址
     * @param String $uid
     * @return Ambigous <\Zend\Db\ResultSet\ResultSet, NULL, \Zend\Db\ResultSet\ResultSetInterface>
     */
    function addressList($uid)
    {
    	$table = new TableGateway('address', $this->adapter);
    	$rows = $table->select(array('uid'=>$uid));
    	return $rows;
    }
    /**
     * @todo 读取地址信息
     * @param int $id
     * @return Ambigous <multitype:, ArrayObject, NULL, \ArrayObject, unknown>
     */
    function getAddress($id)
    {
    	$table = new TableGateway('address', $this->adapter);
    	$rowSet=$table->select(array('id'=>$id));
    	$row  = $rowSet->current();
    	return $row;
    }
    
    /**
     * @todo 修改收件地址
     * @param Array $data
     * @param String $uid
     * @param Int $id
     * @return boolean
     */
    function defaultAddress ($data, $uid, $id = null)
    {
        $table = new TableGateway('address', $this->adapter);
        if ($id) {
            $table->update($data, array(
                'uid' => $uid,
                'id' => $id
            ));
        } else {
            $table->update($data, array(
                'uid' => $uid
            ));
        }
        return true;
    }
    
    /**
     * @todo 取得省市级名
     * @param number $pid
     * @return Ambigous <\Zend\Db\ResultSet\ResultSet, NULL, \Zend\Db\ResultSet\ResultSetInterface>
     */
    function areas ($pid=0)
    {
    	$table = new TableGateway('areas', $this->adapter);
    	$rows=$table->select(array('parentid'=>$pid));
    	return $rows;
    }
    /**
     * @用户登录日志
     * @param Array $data
     */
    function logining($data)
    {
    	$table = new TableGateway('log_login', $this->adapter);
    	$table ->insert($data);
    	$tid = $table->getLastInsertValue();
    }
    /**
     * @todo 提交提问
     * @param Array $data
     * @return number
     */
    function addAsk($data)
    {
    	$table = new TableGateway('faqs', $this->adapter);
    	$table->insert($data);
    	$tid = $table->getLastInsertValue();
    	return $tid;
    }
    /**
     * @todo 问题列表
     * @param int $page
     * @param Object $user
     * @return \Zend\Paginator\Paginator
     */
    function answers ($page,$user)
    {
    	$select = new Select('faqs');
    	if ($user->power < 3)
    	{
    	  $select->where(array('domain' => $user->domain));
    	}
    	$select->order('id desc');
    	$adapter = new DbSelect($select, $this->adapter);
    	$paginator = new Paginator($adapter);
    	$paginator->setItemCountPerPage(30)->setCurrentPageNumber($page);
    	return $paginator;
    }
    /**
     * @todo 取得单个提问
     * @param Int $id
     * @return Ambigous <multitype:, ArrayObject, NULL, \ArrayObject, unknown>
     */
    function getAnswers($id)
    {
    	$table = new TableGateway('faqs', $this->adapter);
    	$rowSet=$table->select(array('id'=>$id));
    	$row=$rowSet->current();
    	return $row;
    }
    
    function editAnswers ($id, $data)
    {
    	$id = (int) $id;
    	if ($id) {
    		$table = new TableGateway('faqs', $this->adapter);
    		$table->update($data, array('id' => $id));
    		return true;
    	}
    	return false;
    }
}