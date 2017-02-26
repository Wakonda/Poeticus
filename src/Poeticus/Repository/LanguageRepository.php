<?php

namespace Poeticus\Repository;

use Poeticus\Entity\Language;

/**
 * Poem repository
 */
class LanguageRepository extends GenericRepository
{
    public function find($id, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM language WHERE id = ?', array($id));

        return $data ? $this->build($data, $show) : null;
    }

    public function findOneByAbbreviation($abbreviation, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM language WHERE abbreviation = ?', array($abbreviation));

        return $data ? $this->build($data, $show) : null;
    }
	
    public function findAll($show = false)
    {
		$qb = $this->db->createQueryBuilder();

		$qb->select("bo.*")
		   ->from("language", "bo");

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

		$aColumns = array( 'pf.id', 'pf.title', 'pf.id');
		
		$qb->select("*")
		   ->from("language", "pf");
		
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
            $entitiesArray[] = $this->build($data);
        }
			
		return $entitiesArray;
	}
	
	public function build($data, $show = false)
    {
        $entity = new Language();
        $entity->setId($data['id']);
        $entity->setTitle($data['title']);
        $entity->setAbbreviation($data['abbreviation']);
        $entity->setLogo($data['logo']);
        $entity->setDirection($data['direction']);

        return $entity;
    }
	
	public function findAllForChoice()
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("id, title")
		   ->from("language", "pf")
		   ->orderBy("title", "ASC");

		$results = $qb->execute()->fetchAll();
		$choiceArray = array();
		
		foreach($results as $result)
		{
			$choiceArray[$result["title"]] = $result["id"];
		}
		
        return $choiceArray;
	}

	// Combobox
	public function getDatasCombobox($params, $count = false)
	{
		$qb = $this->db->createQueryBuilder();
		
		if(array_key_exists("pkey_val", $params))
		{
			$qb->select("b.id, b.title")
			   ->from("language", "b")
			   ->where('b.id = :id')
			   ->setParameter('id', $params['pkey_val']);
			   
			return $qb->execute()->fetch();
		}
		
		$params['offset']  = ($params['page_num'] - 1) * $params['per_page'];

		$qb->select("b.id, b.title")
		   ->from("biography", "b")
		   ->where("b.title LIKE :title")
		   ->setParameter("title", "%".implode(' ', $params['q_word'])."%")
		   ->setMaxResults($params['per_page'])
		   ->setFirstResult($params['offset'])
		   ;
		
		if($count)
		{
			$qb->select("COUNT(b.id)")
			   ->from("biography", "b")
			   ->where("b.title LIKE :title")
			   ->setParameter("title", "%".implode(' ', $params['q_word'])."%");
			   
			return $qb->execute()->rowCount();
		}

		return $qb->execute()->fetchAll();
	}
}