<?php

namespace Poeticus\Repository;

use Poeticus\Entity\Country;

/**
 * Poem repository
 */
class CountryRepository extends GenericRepository implements iRepository
{
	public function save($entity, $id = null)
	{
		$entityData = array(
			'title' => $entity->getTitle(),
			'internationalName' => $entity->getInternationalName(),
			'flag' => $entity->getFlag(),
			'slug' => $entity->getSlug(),
			'language_id' => ($entity->getLanguage() == 0) ? null : $entity->getLanguage()
		);

		if(empty($id))
		{
			$this->db->insert('country', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('country', $entityData, array('id' => $id));

		return $id;
	}
	
    public function find($id, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM country WHERE id = ?', array($id));

        return $data ? $this->build($data, $show) : null;
    }
	
    public function findAll($show = false)
    {
		$qb = $this->db->createQueryBuilder();

		$qb->select("co.*")
		   ->from("country", "co");

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

		$aColumns = array( 'pf.id', 'pf.title', 'la.title', 'pf.id');
		
		$qb->select("pf.*")
		   ->from("country", "pf")
		   ->leftjoin("pf", "language", "la", "pf.language_id = la.id");
		
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
	
	public function build($data, $show = false)
    {
        $entity = new Country();
        $entity->setId($data['id']);
        $entity->setTitle($data['title']);
        $entity->setInternationalName($data['internationalName']);
        $entity->setFlag($data['flag']);

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

	public function findAllForChoice($locale)
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("pf.id AS id, pf.title AS title")
		   ->from("country", "pf")
		   ->leftjoin("pf", "language", "la", "pf.language_id = la.id")
		   ->where('la.abbreviation = :locale')
		   ->setParameter('locale', $locale)
		   ->orderBy("title", "ASC");

		$results = $qb->execute()->fetchAll();
		$choiceArray = array();
		
		foreach($results as $result)
		{
			$choiceArray[$result["title"]] = $result["id"];
		}

        return $choiceArray;
	}
	
	public function findAllByLanguage($locale, $show = false)
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("co.*")
		   ->from("country", "co")
		   ->leftjoin("co", "language", "la", "co.language_id = la.id")
		   ->where("la.id = :id")
		   ->setParameter("id", $locale);

		$dataArray = $qb->execute()->fetchAll();

		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data, true);
        }

        return $entitiesArray;
	}

	public function checkForDoubloon($entity)
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("COUNT(*) AS number")
		   ->from("country", "co")
		   ->leftjoin("co", "language", "la", "co.language_id = la.id")
		   ->where("co.slug = :slug")
		   ->setParameter('slug', $entity->getSlug())
		   ->andWhere("la.id = :id")
		   ->setParameter("id", $entity->getLanguage())
		   ;

		if($entity->getId() != null)
		{
			$qb->andWhere("co.id != :id")
			   ->setParameter("id", $entity->getId());
		}

		return $qb->execute()->fetchColumn();
	}
}