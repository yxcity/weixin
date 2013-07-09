<?php
namespace Admin\Model;


use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
class Keyword
{

    private $adapter;

    function __construct ($adapter)
    {
        $this->adapter = $adapter;
    }
    
    function keyList($msgType,$page,$domain=null)
    {
    	$select = new Select('keywords');
    	$where=array('msgType'=>$msgType);
    	if ($domain)
    	{
    		$where['uid']=$domain;
    	}
    	$select->where($where);
    	$select->order('id DESC');
    	$adapter = new DbSelect($select, $this->adapter);
    	$paginator = new Paginator($adapter);
    	$paginator->setItemCountPerPage(30)->setCurrentPageNumber($page);
    	return $paginator;
    }
}