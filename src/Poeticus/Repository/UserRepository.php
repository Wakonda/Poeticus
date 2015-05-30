<?php

namespace Poeticus\Repository;

use Doctrine\DBAL\Connection;
use Poeticus\Entity\User;

/**
 * Poem repository
 */
class UserRepository
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

	public function findAllForChoice()
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("id, username")
		   ->from("user", "pf")
		   ->orderBy("username", "ASC");

		$results = $qb->execute()->fetchAll();
		$choiceArray = array();
		
		foreach($results as $result)
		{
			$choiceArray[$result["id"]] = $result["username"];
		}
		
        return $choiceArray;
	}
	
	public function find($id, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM user WHERE id = ?', array($id));

        return $data ? $this->build($data, $show) : null;
    }
	
	public function findByToken($token, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM user WHERE token = ?', array($token));

        return $data ? $this->build($data, $show) : null;
    }

	public function findByName($username, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM user WHERE username = ?', array($username));

        return $data ? $this->build($data, $show) : null;
    }
	
	public function findAll()
    {
        $dataArray = $this->db->fetchAll('SELECT * FROM user');

		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data, true);
        }

		return $entitiesArray;
    }
	
	protected function build($data, $show = false)
    {
        $entity = new User();

        $entity->setId($data['id']);
        $entity->setUsername($data['username']);
        $entity->setPassword($data['password']);
        $entity->setEmail($data['email']);
        $entity->setAvatar($data['avatar']);
        $entity->setPresentation($data['presentation']);
		$entity->setToken($data['token']);
		$entity->setSalt($data['salt']);
		$entity->setGravatar($data['gravatar']);
		$entity->setExpiredAt(\DateTime::createFromFormat('Y-m-d H:i:s', $data['expired_at']));
		
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
	
	public function findByUsernameOrEmail($field, $show = false)
	{
		$data = $this->db->fetchAssoc('SELECT * FROM user WHERE username = ? OR email = ?', array($field, $field));

		return $data ? $this->build($data, $show) : null;
	}
	
	public function checkForDoubloon($entity)
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("COUNT(*) AS number")
		   ->from("user", "pf")
		   ->where("pf.username = :username")
		   ->orWhere("pf.email = :email")
		   ->setParameter('username', $entity->getUsername())
		   ->setParameter('email', $entity->getEmail());

		if($entity->getId() != null)
		{
			$qb->andWhere("pf.id != :id")
			   ->setParameter("id", $entity->getId());
		}
		$results = $qb->execute()->fetchAll();

		return $results[0]["number"];
	}

	public function save($entity, $id = null)
	{
		$entityData = array(
		'username' => $entity->getUsername(),
        'password'  => $entity->getPassword(),
        'email' => $entity->getEmail(),
        'avatar' => $entity->getAvatar(),
        'presentation' => $entity->getPresentation(),
		'country_id' => $entity->getCountry(),
        'salt' => $entity->getSalt(),
        'token' => $entity->getToken(),
        'enabled' => $entity->getEnabled(),
        'expired_at' => $entity->getExpiredAt()->format('Y-m-d H:i:s'),
        'roles' => implode(",", $entity->getRoles()),
		'gravatar' => $entity->getGravatar()
		);

		if(empty($id))
		{
			$this->db->insert('user', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('user', $entityData, array('id' => $id));

		return $id;
	}
}
