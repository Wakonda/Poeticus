<?php

namespace Poeticus\Repository;

use Doctrine\DBAL\Connection;
use Poeticus\Entity\PoemVote;

/**
 * PoemVote repository
 */
class PoemVoteRepository extends GenericRepository
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
		// die(var_dump($entity->getUser()->getId()));
		$entityData = array(
        'vote'  => $entity->getVote(),
        'user_id' => ($entity->getUser()->getId() == null) ? null : $entity->getUser()->getId(),
        'poem_id' => ($entity->getPoem()->getId() == 0) ? null : $entity->getPoem()->getId()
		);

		if(empty($id))
		{
			$this->db->insert('poemvote', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('poemvote', $entityData, array('id' => $id));

		return $id;
	}
	
	public function checkIfUserAlreadyVote($idPoem, $idUser)
	{
		$data = $this->db->fetchAssoc('SELECT COUNT(*) AS votes_number FROM poemvote WHERE poem_id = ? AND user_id = ?', array($idPoem, $idUser));
		
		return $data['votes_number'];
	}
	
	public function countVoteByPoem($idPoem, $vote)
	{
		$data = $this->db->fetchAssoc('SELECT COUNT(*) AS votes_number FROM poemvote WHERE poem_id = ? AND vote = ?', array($idPoem, $vote));
		
		return $data['votes_number'];
	}

	public function findVoteByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array('pf.title', 'vo.vote');
		
		$qb->select("pf.id, pf.title, vo.vote")
		   ->from("poemvote", "vo")
		   ->leftjoin("vo", "user", "bp", "vo.user_id = bp.id")
		   ->leftjoin("vo", "poem", "pf", "vo.poem_id = pf.id")
		   ->where("bp.username = :username")
		   ->setParameter("username", $username);
		   
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andhere('pf.title LIKE :search')
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

		return $dataArray;
	}


	protected function build($data, $show = false)
    {
        $entity = new PoemVote();

        $entity->setId($data['id']);
        $entity->setVote($data['vote']);
		
		if($show)
		{
			$entity->setUser($this->findByTable($data['user_id'], 'user', 'username'));
			$entity->setPoem($this->findByTable($data['poem_id'], 'poem', 'title'));
		}
		else
		{
			$entity->setUser($data['user_id']);
			$entity->setPoem($data['poem_id']);
		}

        return $entity;
    }
}