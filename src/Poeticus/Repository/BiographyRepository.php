<?php

namespace Poeticus\Repository;

use Doctrine\DBAL\Connection;
use Poeticus\Entity\Biography;

/**
 * Poem repository
 */
class BiographyRepository
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

	public function save($entity, $id = null)
	{
		$entityData = array(
		'title' => $entity->getTitle(),
		'text' => $entity->getText(),
		'dayBirth' => $entity->getDayBirth(),
		'monthBirth' => $entity->getMonthBirth(),
		'yearBirth' => $entity->getYearBirth(),
		'dayDeath' => $entity->getDayDeath(),
		'monthDeath' => $entity->getMonthDeath(),
		'yearDeath' => $entity->getYearDeath(),
		'photo' => $entity->getPhoto(),
		'country_id' => ($entity->getCountry() == 0) ? null : $entity->getCountry()
		);

		if(empty($id))
		{
			$this->db->insert('biography', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('biography', $entityData, array('id' => $id));

		return $id;
	}
	
    public function find($id, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM biography WHERE id = ?', array($id));

        return $data ? $this->build($data, $show) : null;
    }
	
	public function getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.id', 'pf.title', 'pf.id');
		
		$qb->select("*")
		   ->from("biography", "pf");
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->where('pf.title LIKE :search')
			   ->setParameter('search', $search);
		}
		if($count)
		{
			$qb->select("COUNT(*) AS count");
			$results = $qb->execute()->fetchAll();
			return $results[0]["count"];
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
	
	protected function build($data, $show = false)
    {
        $entity = new Biography();
        $entity->setId($data['id']);
        $entity->setTitle($data['title']);
        $entity->setText($data['text']);
        $entity->setDayBirth($data['dayBirth']);
        $entity->setMonthBirth($data['monthBirth']);
        $entity->setYearBirth($data['yearBirth']);
        $entity->setDayDeath($data['dayDeath']);
        $entity->setMonthDeath($data['monthDeath']);
        $entity->setYearDeath($data['yearDeath']);
        $entity->setPhoto($data['photo']);
		
		if($show)
		{
			$entity->setCountry($this->findByTable($data['country_id'], 'country'));
		}
		else
		{
			$entity->setCountry($data['country_id']);
		}

        return $entity;
    }
	
	public function getPoemByAuthors()
	{
		$qb = $this->db->createQueryBuilder();
	
		$qb->select("*")
		   ->from("poem", "pf")
		   ->groupBy("pf.biography_id");
		   
		$dataArray = $qb->execute()->fetchAll();
		
		return $dataArray;
		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data, true);
        }
			
		return $entitiesArray;
	}
	
	public function findAllForChoice()
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("id, title")
		   ->from("biography", "pf")
		   ->orderBy("title", "ASC");

		$results = $qb->execute()->fetchAll();
		$choiceArray = array();
		
		foreach($results as $result)
		{
			$choiceArray[$result["id"]] = $result["title"];
		}
		
        return $choiceArray;
	}
	
	public function checkForDoubloon($entity)
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("COUNT(*) AS number")
		   ->from("biography", "pf")
		   ->where("pf.title = :title")
		   ->setParameter('title', $entity->getTitle());

		if($entity->getId() != null)
		{
			$qb->andWhere("pf.id != :id")
			   ->setParameter("id", $entity->getId());
		}
		$results = $qb->execute()->fetchAll();
		
		return $results[0]["number"];
	}

    public function findByTable($id, $table, $field = null)
    {
		if(empty($id))
			return null;
			
        $data = $this->db->fetchAssoc('SELECT * FROM '.$table.' WHERE id = ?', array($id));

		if(empty($field))
			return $data;
		else
			return $data[$field];
    }
}
