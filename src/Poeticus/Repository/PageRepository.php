<?php

namespace Poeticus\Repository;

use Poeticus\Entity\Page;

/**
 * Poem repository
 */
class PageRepository extends GenericRepository
{
	public function save($entity, $id = null)
	{
		$entityData = array(
		'title' => $entity->getTitle(),
		'internationalName' => $entity->getInternationalName(),
		'text' => $entity->getText(),
		'photo' => $entity->getPhoto(),
		'language_id' => ($entity->getLanguage() == 0) ? null : $entity->getLanguage()
		);

		if(empty($id))
		{
			$this->db->insert('page', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('page', $entityData, array('id' => $id));

		return $id;
	}
	
    public function find($id, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM page WHERE id = ?', array($id));

        return $data ? $this->build($data, $show) : null;
    }
	
    public function findByName($name, $locale, $show = false)
    {
		$qb = $this->db->createQueryBuilder();

		$qb->select("pa.*")
		   ->from("page", "pa")
		   ->where('pa.internationalName = :internationalName')
		   ->setParameter('internationalName', $name);
		
		$this->whereLanguage($qb, 'pa', $locale);
		$data = $qb->execute()->fetch();

        return $data ? $this->build($data, $show) : null;
    }
	
    public function findAll($show = false)
    {
		$qb = $this->db->createQueryBuilder();

		$qb->select("pa.*")
		   ->from("page", "pa");

		$dataArray = $qb->execute()->fetchAll();

		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data, true);
        }

        return $entitiesArray;
    }
	
	public function getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pa.id', 'pa.title', 'pa.id');
		
		$qb->select("pa.*")
		   ->from("page", "pa")
		   ->leftjoin("pa", "language", "la", "pa.language_id = la.id");
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->where('pa.title LIKE :search')
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
            $entitiesArray[] = $this->build($data, true);
        }
			
		return $entitiesArray;
	}
	
	protected function build($data, $show = false)
    {
        $entity = new Page();
        $entity->setId($data['id']);
        $entity->setTitle($data['title']);
        $entity->setInternationalName($data['internationalName']);
        $entity->setText($data['text']);
        $entity->setPhoto($data['photo']);
		
		if($show)
		{
			$entity->setLanguage($this->findByTable($data['language_id'], 'language'));
		}
		else
		{
			$entity->setLanguage($data['language_id']);
		}

        return $entity;
    }
	
	public function checkForDoubloon($entity)
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("COUNT(*) AS number")
		   ->from("page", "pa")
		   ->where("pa.title = :title")
		   ->setParameter('title', $entity->getTitle());

		if($entity->getId() != null)
		{
			$qb->andWhere("pa.id != :id")
			   ->setParameter("id", $entity->getId());
		}
		
		return $qb->execute()->fetchColumn();
	}
}