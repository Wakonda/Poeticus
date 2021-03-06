<?php

namespace Poeticus\Repository;

use Poeticus\Entity\Collection;

/**
 * Poem repository
 */
class CollectionRepository extends GenericRepository implements iRepository
{
	public function save($entity, $id = null)
	{
		$entityData = array(
			'title' => $entity->getTitle(),
			'text' => $entity->getText(),
			'slug' => $entity->getSlug(),
			'releasedDate' => $entity->getReleasedDate(),
			'biography_id' => ($entity->getBiography() == 0) ? null : $entity->getBiography(),
			'image' => $entity->getImage(),
			'widgetProduct' => $entity->getWidgetProduct(),
			'language_id' => ($entity->getLanguage() == 0) ? null : $entity->getLanguage()
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
	
    public function findAll($show = false)
    {
		$qb = $this->db->createQueryBuilder();

		$qb->select("co.*")
		   ->from("collection", "co");

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
		   ->from("collection", "pf")
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
			$entity->setLanguage($this->findByTable($data['language_id'], 'language'));
		}
		else
		{
			$entity->setBiography($data['biography_id']);
			$entity->setLanguage($data['language_id']);
		}
		
        return $entity;
    }

	public function findAllForChoice($locale)
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("pf.id AS id, pf.title AS title")
		   ->from("collection", "pf")
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
	
	public function findAllByAuthor($authorId)
    {
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("*")
		   ->from("collection", "co")
		   ->where("co.biography_id = :biographyId")
		   ->setParameter("biographyId", $authorId)
		   ->orderBy("title", "ASC");

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
		   ->leftjoin("pf", "language", "la", "pf.language_id = la.id")
		   ->where("pf.slug = :slug")
		   ->setParameter('slug', $entity->getSlug())
		   ->andWhere("pf.biography_id = :biographyId")
		   ->setParameter("biographyId", $entity->getBiography())
		   ->andWhere("la.id = :id")
		   ->setParameter("id", $entity->getLanguage());

		if($entity->getId() != null)
		{
			$qb->andWhere("pf.id != :id")
			   ->setParameter("id", $entity->getId());
		}
		$results = $qb->execute()->fetchAll();
		
		return $results[0]["number"];
	}
	
	public function getAllPoemsByCollectionAndAuthorForPdf($id)
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("pf.title, pf.text, pf.releasedDate")
		   ->from("poem", "pf")
		   ->where("pf.collection_id = :collectionId")
		   ->setParameter('collectionId', $id);
		   
		return $qb->execute()->fetchAll();
	}
}