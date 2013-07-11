<?php
namespace Archives\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use module\Application\src\Model\Tool;
use Zend\Db\Sql\Select;

class Archives
{

    protected $tableGateway;
    private $user;

    public function __construct (TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $this->user = Tool::getSession('auth', 'user');
    }

    public function getArchivesList ($page)
    {
        $select = new Select('archives');
        $select->columns(array(
            'id',
            'title'
        ));
        $select->order('id DESC');
        $adapter = new DbSelect($select, $this->tableGateway->getAdapter());
        $paginator = new Paginator($adapter);
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(30);
        return $paginator;
    }

    public function getArchivesID ($id)
    {
        $id = (int) $id;
        $rowSet = $this->tableGateway->select(array(
            'id' => $id
        ));
        $row = $rowSet->current();
        if (! $row) {
            throw new \Exception("Could not find row {$row}");
        }
        return $row;
    }

    public function save ($data,$id=null)
    {
        
        if ($id)
        {
        	if ($this->getArchivesID($id)) {
        		$this->tableGateway->update($data, array(
        				'id' => $id
        		));
        	} else {
        		throw new \Exception('Form id docs not exist');
        	}
        }else 
        {
        	$data['uid']=$this->user->id;
        	$data['domain']=$this->user->domain;
        	$this->tableGateway->insert($data);
        }
    }

    public function delete ($id)
    {
        $this->tableGateway->delete(array(
            'id' => $id
        ));
    }
}