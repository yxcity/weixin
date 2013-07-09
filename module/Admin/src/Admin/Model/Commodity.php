<?php
namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Sql;

class Commodity
{

    private $adapter;

    function __construct ($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     *
     * @todo 添加商品
     * @param array $data            
     * @return Ambigous <boolean, number>
     */
    function addCommodity ($data)
    {
        $table = new TableGateway('commodity', $this->adapter);
        $table->insert($data);
        $tid = $table->getLastInsertValue();
        return $tid ? $tid : false;
    }

    /**
     *
     * @todo 编辑商品信息
     * @param int $id            
     * @param array $data            
     * @return boolean
     */
    function editCommodity ($id, $data)
    {
        $id = (int) ($id);
        if ($id) {
            $table = new TableGateway('commodity', $this->adapter);
            $table->update($data, array(
                'id' => $id
            ));
            return true;
        }
        return false;
    }

    /**
     *
     * @todo 取得单条商品数据
     * @param int $id            
     * @return Ambigous <boolean, multitype:, ArrayObject, NULL, \ArrayObject, unknown>|boolean
     */
    function getCommodity ($id)
    {
        $id = (int) $id;
        if ($id) {
            $table = new TableGateway('commodity', $this->adapter);
            $rowSet = $table->select(array(
                'id' => $id
            ));
            $row = $rowSet->current();
            return $row ? $row : false;
        }
        return false;
    }
    /**
     * @todo 取得指定门店商品
     * @param int $id
     * @return Ambigous <\Zend\Db\ResultSet\ResultSet, NULL, \Zend\Db\ResultSet\ResultSetInterface>
     */
    function shopCount($id)
    {
    	$id = (int)$id;
    	$table = new TableGateway('commodity', $this->adapter);
    	$rows=$table->select(array('shop'=>$id));
    	return $rows;
    }
    /**
     * @todo 取得指定分类商品
     * @param Int $id
     * @return Ambigous <\Zend\Db\ResultSet\ResultSet, NULL, \Zend\Db\ResultSet\ResultSetInterface>
     */
    function cateCount($domain,$id=null)
    {
        $id = (int)$id;
        $table = new TableGateway('commodity', $this->adapter);
        $where=array('uid'=>$domain);
        if ($id)
        {
        	$where['cateID']=$id;
        }
        $rows=$table->select($where);
        return $rows;
    }
    
    /**
     * @todo 取得指定关键词商品
     * @param Int $id
     * @return Ambigous <\Zend\Db\ResultSet\ResultSet, NULL, \Zend\Db\ResultSet\ResultSetInterface>
     */
    function keywordsCount($domain,$keywords=null)
    {
        $table = new TableGateway('commodity', $this->adapter);
        $where = "uid ='{$domain}'";
        if ($keywords)
        {
    		$where .= " AND name like '%{$keywords}%'";
        }
        $rows=$table->select($where);
        return $rows;
    }

    /**
     *
     * @todo 取得商品列表
     * @param int $page            
     * @param object $user            
     * @return \Zend\Paginator\Paginator
     */
    function commodityList ($page, $user)
    {
        $select = new Select('commodity');
        if ($user->power < 3) {
            $select->where(array(
                'uid' => $user->domain
            ));
        }
        $select->order('id desc');
        $adapter = new DbSelect($select, $this->adapter);
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(30)->setCurrentPageNumber($page);
        return $paginator;
    }
    /**
     * @todo 取得欢迎商品
     * @param String $domain
     * @return Ambigous <\Zend\Db\ResultSet\ResultSet, NULL, \Zend\Db\ResultSet\ResultSetInterface>
     */
    function welcome($domain)
    {
    	$table = new TableGateway('commodity', $this->adapter);
    	$row = $table->select(array('welcome'=>1,'uid'=>$domain));
    	return $row;
    }
    
    /**
     * @todo 按关键字取得商品
     * @param unknown $key
     * @return Ambigous <\Zend\Db\ResultSet\ResultSet, NULL, \Zend\Db\ResultSet\ResultSetInterface>
     */
    function keyList ($key=null,$uid=null,$welcome=null)
    {
        $table = new TableGateway('commodity', $this->adapter);
        $where = '1 ';
        if ($key)
        {
            $where .= "AND name like '%{$key}%'";
        }
       
        if ($uid)
        {
        	$where .= " AND uid='{$uid}'";
        }
        
        if ($welcome)
        {
            $where .= " AND welcome='1'";
        }
        
        $resultSet = $table->select($where);
        return $resultSet;
    }
    /**
     * @todo 按分类域名取得商品
     * @param Int $num
     * @param number $page
     * @param String $domian
     * @param string $tid
     * @return Ambigous <\Zend\Db\Sql\Select, \Zend\Db\Sql\Select>
     */
    function proList($num,$page,$domain,$shop=null,$cateID=null,$keywords=null)
    {
    	$sql = new Sql($this->adapter);
    	$select = $sql->select('commodity');
    	$where = "uid ='{$domain}'";
    	if ($shop)
    	{
    		$where .= " AND shop='{$shop}'";
    	}
    	if ($cateID)
    	{
    		$where.=" AND cateID='{$cateID}'";
    	}
    	if ($keywords)
    	{
    		$where.=" AND name like '%{$keywords}%'";
    	}
    	$select->where($where);
    	$select->order('id DESC');
    	$select->limit($num);
    	$offset = ($page-1)*$num;
    	$select->offset($offset);
    	$rowsSet=$sql->prepareStatementForSqlObject($select);
    	$rows = $rowsSet->execute();
    	return $rows;
    }
    
    /**
     * @todo 删除商品
     * @param unknown $id
     * @return boolean
     */

    function delete ($id)
    {
        $id = (int) $id;
        $table = new TableGateway('commodity', $this->adapter);
        $table->delete(array(
            'id' => $id
        ));
        return true;
    }
}