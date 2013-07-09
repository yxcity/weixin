<?php
namespace Admin\Model;

use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
class Indent
{

    private $adapter;

    function __construct ($adapter)
    {
    	$this->adapter = $adapter;
    }
    /**
     * @todo 取得订单列表
     * @param int $page
     * @param object $user
     * @return \Zend\Paginator\Paginator
     */
    function indentList($page,$user,$shop=null,$status=null)
    {
    	$select = new Select('indent');
    	$where="uid  = '{$user->domain}' AND display='1'";
    	
    	if ($status)
    	{
    	    $where.=" AND status IN({$status})";
    	}
    	
    	if ($user->power==1 && $shop)
    	{
    		$where.=" AND shop IN({$shop})";
    	}
    	$select->where($where);
    	$select->order('id desc');
    	$adapter =  new DbSelect($select, $this->adapter);
    	$paginator = new Paginator($adapter);
    	$paginator->setItemCountPerPage(30)->setCurrentPageNumber($page);
    	return $paginator;
    }
    /**
     * @todo 添加订单
     * @return Ambigous <boolean, number>
     */
    function addIndent($data)
    {
    	$table = new TableGateway('indent', $this->adapter);
    	$table->insert($data);
    	$tid = $table->getLastInsertValue();
    	return $tid?$tid:false;
    }
    
    function indentAll($uid)
    {
    	$table = new TableGateway('indent', $this->adapter);
    	$rows = $table->select(array('uid'=>$uid));//
    	return $rows;
    }
    
    function getIndent ($id)
    {
    	$id = (int) $id;
    	if ($id) {
    		$table = new TableGateway('indent', $this->adapter);
    		$rowset = $table->select(array(
    				'id' => $id
    		));
    		$row = $rowset->current();
    		return $row ? $row : false;
    	}
    	return false;
    }
    /**
     * @todo 修改订单
     * @param array $data
     * @param string $id
     * @param string $serialnumber
     * @return boolean
     */
    function update($data,$id=null,$serialnumber=null)
    {
        if ($id || $serialnumber) {
            if ($id)
            {
            	$where=array('id'=>$id);
            }
            if ($serialnumber)
            {
                $where=array('serialnumber'=>$serialnumber);
            }
            $table = new TableGateway('indent', $this->adapter);
            $table->update($data, $where);
            return true;
        }
        return false;
    }
    /**
     * @todo 取得用户订单
     * @param String $domain
     * @param String $buyer
     * @return Ambigous <\Zend\Db\ResultSet\ResultSet, NULL, \Zend\Db\ResultSet\ResultSetInterface>
     */
    function userIndent($domain,$buyer,$status=null,$num=30,$page=1)
    {
        $sql = new Sql($this->adapter);
        $select=$sql->select('indent');
        $where="uid  = '{$domain}' AND buyer='{$buyer}'";
        if ($status)
        {
        	$where.=" AND status IN({$status})";
        }
        $select->where($where);
        $select->order('id DESC');
        $offset = ($page-1)*$num;
        $select->limit($num);
    	$select->offset($offset);
        $rowsSet = $sql->prepareStatementForSqlObject($select);
        $rows = $rowsSet->execute();
        return $rows;
    }
    /**
     * @todo 按域名，购买人 商品ＩＤ　取得订单
     * @param String $domain
     * @param String $buyer
     * @param Int $id
     * @return Ambigous <multitype:, ArrayObject, NULL, \ArrayObject, unknown>
     */
    function clickIndent($domain,$buyer,$id)
    {
    	$table = new TableGateway('indent', $this->adapter);
    	$rowSet=$table->select(array('uid'=>$domain,'buyer'=>$buyer,'pid'=>$id,'status'=>2));
    	$row = $rowSet->current();
    	return $row?$row:false;
    }
    /**
     * @tood 订单状态
     */
    function indentStatus()
    {
        $status=array(1=>'已付款',2=>'未付款',3=>'已退款',4=>'已发货',5=>'取消订单');
        return $status;
    }
    /**
     * @todo 快递公司
     * @return multitype:string
     */
    function express()
    {
    	$express=array(1=>'圆通快递',2=>'顺丰快递',3=>'韵达快递',4=>'申通快递',5=>'汇通快运',6=>'中通速递',7=>'邮政EMS',8=>'汇通快递',9=>'天天快递');
    	return $express;
    }
    
}