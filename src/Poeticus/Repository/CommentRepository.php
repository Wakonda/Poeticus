<?php

namespace Poeticus\Repository;

use Poeticus\Entity\Comment;

/**
 * Comment repository
 */
class CommentRepository extends GenericRepository
{
	public function save($entity, $id = null)
	{
		$entityData = array(
        'text'  => $entity->getText(),
        'created_at' => $entity->getCreatedAt(),
        'poem_id' => ($entity->getPoem() == 0) ? null : $entity->getPoem(),
        'user_id' => ($entity->getUser()->getId() == null) ? null : $entity->getUser()->getId(),
		);

		if(empty($id))
		{
			$this->db->insert('comment', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('comment', $entityData, array('id' => $id));

		return $id;
	}

	public function findAll()
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("*")
		   ->from("comment", "pf");

		$dataArray = $qb->execute()->fetchAll();
		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data, true);
        }

		return $entitiesArray;
	}

	public function build($data, $show = false)
    {
        $entity = new Comment();

        $entity->setId($data['id']);
        $entity->setText($data['text']);
        $entity->setCreatedAt(new \Datetime($data['created_at']));
		
		if($show)
		{
			$entity->setPoem($this->findByTable($data['poem_id'], 'poem'));
			
			$entity->setUser($this->findByTable($data['user_id'], 'user', 'username'));
		}
		else
		{
			$entity->setPoem($data['poem_id']);
			$entity->setUser($data['user_id']);
		}

        return $entity;
    }

	public function countAllComments($poemId)
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("COUNT(*) AS count")
		   ->from('comment', 'c')
		   ->where('c.poem_id = :poemId')
		   ->setParameter('poemId', $poemId);
			$results = $qb->execute()->fetchAll();

			return $results[0]["count"];
	}
	
	public function displayComments($poemId, $max_comment_by_page, $first_message_to_display)
	{
		$qb = $this->db->createQueryBuilder();
		
		$first_message_to_display = ($first_message_to_display < 0) ? 0 : $first_message_to_display;

		$qb->select("*")
		   ->from("comment", "c")
		   ->where('c.poem_id = :poemId')
		   ->setParameter('poemId', $poemId)
		   ->setMaxResults($max_comment_by_page)
		   ->setFirstResult($first_message_to_display)
		   ->orderBy("c.created_at", "DESC");

		$dataArray = $qb->execute()->fetchAll();
		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data, true);
        }

		return $entitiesArray;
	}

	public function findCommentByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array('pf.title', 'co.created_at');
		
		$qb->select("pf.id, pf.title, pf.slug, co.created_at")
		   ->from("comment", "co")
		   ->leftjoin("co", "user", "bp", "co.user_id = bp.id")
		   ->leftjoin("co", "poem", "pf", "co.poem_id = pf.id")
		   ->where("bp.username = :username")
		   ->setParameter("username", $username)
		   ->orderBy("co.created_at", "DESC");
		   
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
			return $qb->execute()->fetchColumn();
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		$dataArray = $qb->execute()->fetchAll();

		return $dataArray;
	}
}