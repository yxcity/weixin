<?php
namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class Type
{

    private $adapter;

    function __construct ($adapter)
    {
        $this->adapter = $adapter;
    }
    
    /**
     * @todo 添加分类
     * @param array $data
     * @return Ambigous <boolean, number>
     */

    function addType ($data)
    {
        $table = new TableGateway('type', $this->adapter);
        $table->insert($data);
        $tid = $table->getLastInsertValue();
        return $tid ? $tid : false;
    }
    
    /**
     * @todo 编辑分类
     * @param int $id
     * @param array $data
     * @return boolean
     */

    function editType ($id, $data)
    {
        $id = (int) $id;
        if ($id) {
            $table = new TableGateway('type', $this->adapter);
            $table->update($data, array(
                'id' => $id
            ));
            return true;
        }
        return false;
    }
    /**
     * @todo 批量修改二级分类
     * @param Int $pid
     * @param Array $data
     * @return boolean
     */
    function editPid($pid,$data)
    {
        $pid = (int) $pid;
        if ($pid) {
        	$table = new TableGateway('type', $this->adapter);
        	$table->update($data, array(
        			'pid' => $pid
        	));
        	return true;
        }
        return false;
    }
    
    /**
     * @todo 分类列表
     * @param int $page
     * @return \Zend\Paginator\Paginator
     */

    function typeList ($page,$domain)
    {
    	$select = new Select('type');
    	$select->where(array('domain'=>$domain));
    	$select->order('id desc');
    	$adapter = new DbSelect($select, $this->adapter);
    	$paginator = new Paginator($adapter);
    	$paginator->setItemCountPerPage(30)->setCurrentPageNumber($page);
    	return $paginator;
    }
    /**
     * @todo 取得所有分类
     * @return Ambigous <\Zend\Db\ResultSet\ResultSet, NULL, \Zend\Db\ResultSet\ResultSetInterface>
     */
    function typeAll($domain,$pid=null)
    {
    	$table = new TableGateway('type', $this->adapter);
    	$where=array('display'=>1,'domain'=>$domain);
    	if ($pid != null)
    	{
    		$where['pid']=$pid;
    	}
    	$rows = $table->select ($where);
    	
    	return $rows;
    }
    
    /**
     * @todo 取得单个分类信息
     * @param int $id
     * @return Ambigous <boolean, multitype:, ArrayObject, NULL, \ArrayObject, unknown>|boolean
     */
    function getType ($id)
    {
        $id = (int) $id;
        if ($id) {
            $table = new TableGateway('type', $this->adapter);
            $rowSet = $table->select(array(
                'id' => $id
            ));
            $row = $rowSet->current();
            return $row ? $row : false;
        }
        return false;
    }
    /**
     * @todo 删除分类
     * @param int $id
     * @return boolean
     */
    function delete ($id)
    {
        $id = (int) $id;
        if ($id) {
            $table = new TableGateway('type', $this->adapter);
            $table->delete(array('id' => $id));
            return true;
        }
        return false;
    }
}