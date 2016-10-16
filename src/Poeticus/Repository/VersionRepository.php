<?php

namespace Poeticus\Repository;

use Poeticus\Entity\Version;

/**
 * Poem repository
 */
class VersionRepository extends GenericRepository
{
	public function save($entity, $id = null)
	{
		$entityData = array(
		'file' => $entity->getFile(),
		'versionNumber' => $entity->getVersionNumber(),
		'releaseDate' => $entity->getReleaseDate()->format('Y-m-d'),
		'language_id' => ($entity->getLanguage() == 0) ? null : $entity->getLanguage()
		);

		if(empty($id))
		{
			$this->db->insert('version', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('version', $entityData, array('id' => $id));

		return $id;
	}
	
    public function find($id, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM version WHERE id = ?', array($id));

        return $data ? $this->build($data, $show) : null;
    }
	
    public function findAll($show = false)
    {
		$qb = $this->db->createQueryBuilder();

		$qb->select("bo.*")
		   ->from("version", "bo");

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

		$aColumns = array('v.id', 'v.versionNumber', 'v.releaseDate', 'la.title', 'v.id');
		
		$qb->select("v.*")
		   ->from("version", "v")
		   ->leftjoin("v", "language", "la", "v.language_id = la.id");
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->where('v.versionNumber LIKE :search')
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
            $entitiesArray[] = $this->build($data, true);
        }
			
		return $entitiesArray;
	}
	
	protected function build($data, $show = false)
    {
        $entity = new Version();
        $entity->setId($data['id']);
        $entity->setVersionNumber($data['versionNumber']);
        $entity->setReleaseDate(new \DateTime($data['releaseDate']));
        $entity->setFile($data['file']);

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
	
	public function getCurrentVersion()
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("v.versionNumber AS version")
		   ->from("version", "v")
		   ->orderBy("v.id", "DESC")
		   ->setMaxResults(1);
		   
		$res = $qb->execute()->fetch();
		
		return $res["version"];
	}
	
	public function checkForDoubloon($entity)
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("COUNT(*) AS number")
		   ->from("version", "v")
		   ->where("v.versionNumber = :versionNumber")
		   ->setParameter('versionNumber', $entity->getVersionNumber());

		if($entity->getId() != null)
		{
			$qb->andWhere("v.id != :id")
			   ->setParameter("id", $entity->getId());
		}
		$results = $qb->execute()->fetchAll();
		
		return $results[0]["number"];
	}
}