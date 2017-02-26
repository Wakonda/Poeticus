<?php

namespace Poeticus\Repository;

use Poeticus\Entity\Contact;

/**
 * Contact repository
 */
class ContactRepository extends GenericRepository
{
	public function save($entity, $id = null)
	{
		$entityData = array(
		'subject' => $entity->getSubject(),
		'mail' => $entity->getMail(),
		'message' => $entity->getMessage(),
		'dateSending' => date("Y-m-d H:i:s"),
		'readMessage' => $entity->getReadMessage()
		);

		$this->db->insert('contact', $entityData);
		$id = $this->db->lastInsertId();

		return $id;
	}

	public function getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.id', 'pf.subject', 'pf.id', 'pf.id');
		
		$qb->select("*")
		   ->from("contact", "pf");
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->where('pf.subject LIKE :search')
			   ->setParameter('search', $search);
		}
		if($count)
		{
			$qb->select("COUNT(*) AS count");
			return $qb->execute()->fetchColumn();
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		$dataArray = $qb->execute()->fetchAll();
		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data);
        }
			
		return $entitiesArray;
	}

	public function build($data, $show = false)
    {
        $entity = new Contact();
        $entity->setId($data['id']);
        $entity->setSubject($data['subject']);
        $entity->setMail($data['mail']);
        $entity->setReadMessage($data['readMessage']);
        $entity->setMessage($data['message']);
        $entity->setDateSending($data['dateSending']);

        return $entity;
    }
	
    public function find($id)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM contact WHERE id = ?', array($id));

        return $data ? $this->build($data) : null;
    }
	
	public function readContact($id)
	{
		$this->db->update('contact', array("readMessage" => "1"), array('id' => $id));
	}

	public function countUnreadMessages()
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("COUNT(*) AS count")
		   ->from("Contact", "cn")
		   ->where("cn.readMessage = 0");
		   
		return $qb->execute()->fetchColumn();
	}
}