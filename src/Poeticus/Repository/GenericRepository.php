<?php

namespace Poeticus\Repository;

use Doctrine\DBAL\Connection;
use Poeticus\Entity\Country;

/**
 * Generic repository
 */
class GenericRepository
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
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

	public function whereLanguage($qb, $alias, $locale, $join = true)
	{
		if($join)
			$qb->leftjoin($alias, "language", "la", $alias.".language_id = la.id");
		
		$qb->andWhere('la.abbreviation = "'.$locale.'"');
		
		return $qb;
	}
}