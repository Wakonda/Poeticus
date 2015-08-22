<?php

namespace Poeticus\Repository;

use Doctrine\DBAL\Connection;
use Poeticus\Entity\Collection;

/**
 * Poem repository
 */
class CollectionRepository
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
		'releasedDate' => $entity->getReleasedDate(),
		'biography_id' => ($entity->getBiography() == 0) ? null : $entity->getBiography(),
		'image' => $entity->getImage(),
		'widgetProduct' => $entity->getWidgetProduct()
		);

		if(empty($id))
		{
			$this->db->insert('collection', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('collection', $entityData, array('id' => $id));

		return $id;
	}
	
    public function find($id, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM collection WHERE id = ?', array($id));

        return $data ? $this->build($data, $show) : null;
    }
	
	public function getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.id', 'pf.title', 'pf.id');
		
		$qb->select("*")
		   ->from("collection", "pf");
		
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
        $entity = new Collection();
        $entity->setId($data['id']);
        $entity->setTitle($data['title']);
        $entity->setText($data['text']);
		$entity->setReleasedDate($data['releasedDate']);
        $entity->setImage($data['image']);
		$entity->setWidgetProduct($data['widgetProduct']);

		if($show)
		{
			$entity->setBiography($this->findByTable($data['biography_id'], 'biography'));
		}
		else
		{
			$entity->setBiography($data['biography_id']);
		}
		
        return $entity;
    }

	public function findAllForChoice()
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("id, title")
		   ->from("collection", "pf")
		   ->orderBy("title", "ASC");

		$results = $qb->execute()->fetchAll();
		$choiceArray = array();
		
		foreach($results as $result)
		{
			$choiceArray[$result["id"]] = $result["title"];
		}
		
        return $choiceArray;
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
	
	public function findAllByAuthor($authorId)
    {
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("*")
		   ->from("collection", "co")
		   ->where("co.biography_id = :biographyId")
		   ->setParameter("biographyId", $authorId)
		   ->orderBy("biography_id", "ASC");

		$results = $qb->execute()->fetchAll();
		
		return $results;
    }

	public function checkForDoubloon($entity)
	{
		if($entity->getTitle() == null or $entity->getBiography() == null)
			return 0;

		$qb = $this->db->createQueryBuilder();

		$qb->select("COUNT(*) AS number")
		   ->from("collection", "pf")
		   ->where("pf.title = :title")
		   ->setParameter('title', $entity->getTitle())
		   ->andWhere("pf.biography_id = :biographyId")
		   ->setParameter("biographyId", $entity->getBiography());

		if($entity->getId() != null)
		{
			$qb->andWhere("pf.id != :id")
			   ->setParameter("id", $entity->getId());
		}
		$results = $qb->execute()->fetchAll();
		
		return $results[0]["number"];
	}
}