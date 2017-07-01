<?php

namespace Poeticus\Repository;

use Poeticus\Entity\Poem;

/**
 * Poem repository
 */
class PoemRepository extends GenericRepository implements iRepository
{
	public function save($entity, $id = null)
	{
		if(empty($entity->getSlug()))
			$entity->setSlug($entity->getTitle());

		$entityData = array(
			'title' => $entity->getTitle(),
			'slug' => $entity->getSlug(),
			'text'  => $entity->getText(),
			'releasedDate' => $entity->getReleasedDate(),
			'authorType' => $entity->getAuthorType(),
			'poeticForm_id' => ($entity->getPoeticForm() == 0) ? null : $entity->getPoeticForm(),
			'biography_id' => ($entity->getBiography() == 0) ? null : $entity->getBiography(),
			'user_id' => (!is_object($entity->getUser())) ? $entity->getUser() : $entity->getUser()->getId(),
			'country_id' => ($entity->getCountry() == 0) ? null : $entity->getCountry(),
			'collection_id' => ($entity->getCollection() == 0) ? null : $entity->getCollection(),
			'state' => ($entity->getState() == null) ? 0 : $entity->getState(),
			'photo' => $entity->getPhoto(),
			'language_id' => ($entity->getLanguage() == 0) ? null : $entity->getLanguage()
		);

		if(empty($id))
		{
			$this->db->insert('poem', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('poem', $entityData, array('id' => $id));

		return $id;
	}
	
    public function find($id, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM poem WHERE id = ?', array($id));

        return $data ? $this->build($data, $show) : null;
    }
	
    public function findAll($show = false)
    {
		$qb = $this->db->createQueryBuilder();

		$qb->select("pf.*")
		   ->from("poem", "pf");

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
		   ->from("poem", "pf")
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
	
	public function findIndexSearch($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $datasObject, $locale, $count = false)
	{
		$aColumns = array( 'pf.title', 'pfb.title', 'pfc.title', 'pf.id');
		$qb = $this->db->createQueryBuilder();

		$qb->select("pf.*")
		   ->from("poem", "pf")
		   ->leftjoin("pf", "country", "pfc", "pf.country_id = pfc.id");

		$this->whereLanguage($qb, 'pf', $locale);

		if(!empty($datasObject->title))
		{
			$value = "%".$datasObject->title."%";
			$qb->andWhere("pf.title LIKE :title")
			   ->setParameter("title", $value);
		}

		if(!empty($datasObject->text))
		{
			$keywords = explode(",", $datasObject->text);
			$i = 0;
			foreach($keywords as $keyword)
			{
				$keyword = "%".$keyword."%";
				$qb->andWhere("pf.text LIKE :keyword".$i)
			       ->setParameter("keyword".$i, $keyword);
				$i++;
			}
		}

		if(!empty($datasObject->author))
		{
			$author = "%".$datasObject->author."%";
			$qb->leftjoin("pf", "biography", "pfb", "pf.biography_id = pfb.id")
			   ->andWhere("pfb.title LIKE :username")
			   ->setParameter("username", $author);
		}

		if(!empty($datasObject->country))
		{
			$qb->andWhere("pf.country_id = :country")
			   ->setParameter("country", $datasObject->country);
		}

		if(!empty($datasObject->collection))
		{
			$collection = "%".$this->findByTable($datasObject->collection, 'collection', 'title')."%";
			$qb->leftjoin("pf", "collection", "pfco", "pf.collection_id = pfco.id")
			   ->andWhere("pfco.title LIKE :collection")
			   ->setParameter("collection", $collection);
		}

		if(!empty($datasObject->type))
		{
			$qb->andWhere("pf.authorType = :type")
			   ->setParameter("type", $datasObject->type);
		}

		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
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
	
	public function getLastEntries($locale)
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("pf.*")
		   ->from("poem", "pf")
		   ->where("pf.authorType = 'biography'")
		   ->setMaxResults(7)
		   ->andWhere("pf.state = 0")
		   ->orderBy("pf.id", "DESC");
		   
		$this->whereLanguage($qb, "pf", $locale, true);
		   
		$dataArray = $qb->execute()->fetchAll();
		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data, true);
        }
			
		return $entitiesArray;
	}
	
	public function getRandomPoem($locale)
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("COUNT(*) AS countRow")
		   ->from("poem", "pf");
		   
		$this->whereLanguage($qb, "pf", $locale);
		
		$max = $qb->execute()->fetchColumn() - 1;
		$offset = rand(0, $max);

		$qb = $this->db->createQueryBuilder();

		$qb->select("pf.*")
		   ->from("poem", "pf")
		   ->andWhere("pf.state = 0")
		   ->andWhere("pf.authorType = :authorType")
		   ->setParameter("authorType", "biography")
		   ->setFirstResult($offset)
		   ->setMaxResults(1);
		   
		$this->whereLanguage($qb, "pf", $locale);

		$result = $qb->execute()->fetch();

		if(!$result)
			return null;
		
		return $this->build($result, true);
	}

	public function getPoemByAuthorDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $authorId, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.title', 'co.title');
		
		$qb->select("pf.*")
		   ->from("poem", "pf")
		   ->from("collection", "co")
		   ->where("pf.biography_id = :id")
		   ->setParameter("id", $authorId)
		   ->andWhere("(pf.collection_id = co.id OR pf.collection_id IS NULL)");
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('pf.title LIKE :search')
			   ->setParameter('search', $search);
		}
		if($count)
		{
			$qb->select("COUNT(DISTINCT pf.id) AS count");
			$results = $qb->execute()->fetch();

			return $results["count"];
		}
		else
		{
			$qb->groupBy("pf.id")
			   ->setFirstResult($iDisplayStart)
			   ->setMaxResults($iDisplayLength);
		}

		$dataArray = $qb->execute()->fetchAll();
		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data, true);
        }

		return $entitiesArray;
	}
	
	public function build($data, $show = false)
    {
        $entity = new Poem();

        $entity->setId($data['id']);
        $entity->setTitle($data['title']);
        $entity->setText($data['text']);
        $entity->setSlug($data['slug']);
        $entity->setReleasedDate($data['releasedDate']);
        $entity->setAuthorType($data['authorType']);
        $entity->setState($data['state']);
        $entity->setPhoto($data['photo']);
		
		if($show)
		{
			$entity->setPoeticForm($this->findByTable($data['poeticform_id'], 'poeticform'));
			$entity->setBiography($this->findByTable($data['biography_id'], 'biography'));
			$entity->setUser($this->findByTable($data['user_id'], 'user', 'username'));
			$entity->setCountry($this->findByTable($data['country_id'], 'country'));
			$entity->setCollection($this->findByTable($data['collection_id'], 'collection'));
			$entity->setLanguage($this->findByTable($data['language_id'], 'language'));
		}
		else
		{
			$entity->setPoeticForm($data['poeticform_id']);
			$entity->setBiography($data['biography_id']);
			$entity->setUser($data['user_id']);
			$entity->setCountry($data['country_id']);
			$entity->setCollection($data['collection_id']);
			$entity->setLanguage($data['language_id']);
		}

        return $entity;
    }
	
    public function findPoemByAuthor($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $locale, $count = false)
    {
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'bp.title', 'COUNT(pf.id)');
		
		$qb->select("bp.id AS id, bp.title AS author, bp.slug AS slug, COUNT(pf.id) AS number_poems_by_author")
		   ->from("poem", "pf")
		   ->where("pf.authorType = 'biography'")
		   ->leftjoin("pf", "biography", "bp", "pf.biography_id = bp.id")
		   ->groupBy("bp.id");
		   
		 $this->whereLanguage($qb, "pf", $locale);
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('bp.title LIKE "'.$search.'"');
		}
		if($count)
		{
			$countRows = $this->db->executeQuery("SELECT COUNT(*) AS count FROM (".$qb->getSql().") AS SQ");
			$result = $countRows->fetch();

			return $result["count"];
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		$dataArray = $qb->execute()->fetchAll();

		return $dataArray;
    }
	
    public function findPoemByPoeticForm($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $locale, $count = false)
    {
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'co.title', 'COUNT(pf.id)');
		
		$qb->select("pf.id AS id, co.id AS poeticform_id, co.title AS poeticform, COUNT(pf.id) AS number_poems_by_poeticform, co.slug AS poeticform_slug")
		   ->from("poem", "pf")
		   ->where("pf.authorType = 'biography'")
		   ->innerjoin("pf", "poeticform", "co", "pf.poeticform_id = co.id")
		   ->groupBy("co.id");

		$this->whereLanguage($qb, 'pf', $locale);
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('co.title LIKE "'.$search.'"');
		}
		if($count)
		{
			$countRows = $this->db->executeQuery("SELECT COUNT(*) AS count FROM (".$qb->getSql().") AS SQ");
			$result = $countRows->fetch();

			return $result["count"];
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		$dataArray = $qb->execute()->fetchAll();

		return $dataArray;
    }
	
	public function getPoemByPoeticFormDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $collectionId, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.title');
		
		$qb->select("pf.title AS poem_title, pf.id AS poem_id, pf.slug AS slug")
		   ->from("poem", "pf")
		   ->where("pf.poeticform_id = :id")
		   ->setParameter("id", $collectionId)
		   ->andWhere("pf.authorType = :authorType")
		   ->setParameter("authorType", "biography");
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('pf.title LIKE :search')
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
	
    public function findPoemByCollection($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $locale, $count = false)
    {
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'co.title', 'bp.title', 'COUNT(pf.id)');
		
		$qb->select("pf.id AS id, bp.id AS author_id, co.id AS collection_id, bp.title AS author, bp.slug AS author_slug, co.title AS collection, co.slug AS collection_slug, COUNT(pf.id) AS number_poems_by_collection")
		   ->from("poem", "pf")
		   ->leftjoin("pf", "biography", "bp", "pf.biography_id = bp.id")
		   ->innerjoin("pf", "collection", "co", "pf.collection_id = co.id")
		   ->where("pf.authorType = 'biography'")
		   ->groupBy("co.id");

		$this->whereLanguage($qb, 'pf', $locale);
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('co.title LIKE "'.$search.'"');
		}
		if($count)
		{
			$countRows = $this->db->executeQuery("SELECT COUNT(*) AS count FROM (".$qb->getSql().") AS SQ");
			$result = $countRows->fetch();

			return $result["count"];
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		$dataArray = $qb->execute()->fetchAll();

		return $dataArray;
    }

	public function getPoemByCollectionDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $collectionId, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.title');
		
		$qb->select("pf.title AS poem_title, pf.id AS poem_id, pf.slug AS slug")
		   ->from("poem", "pf")
		   ->where("pf.collection_id = :id")
		   ->setParameter("id", $collectionId)
		   ->andWhere("pf.authorType = :authorType")
		   ->setParameter("authorType", "biography")
		   ;
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('pf.title LIKE :search')
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
	
    public function findPoemByCountry($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $locale, $count = false)
    {
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'co.title', 'COUNT(pf.id)');
		
		$qb->select("pf.id AS id, co.id AS country_id, co.slug AS country_slug, co.title AS country_title, COUNT(pf.id) AS number_poems_by_country, co.flag AS flag")
		   ->from("poem", "pf")
		   ->where("pf.authorType = 'biography'")
		   ->innerjoin("pf", "country", "co", "pf.country_id = co.id")
		   ->groupBy("co.id");
		
		$this->whereLanguage($qb, 'pf', $locale);
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('co.title LIKE "'.$search.'"');
		}
		if($count)
		{
			$countRows = $this->db->executeQuery("SELECT COUNT(*) AS count FROM (".$qb->getSql().") AS SQ");
			$result = $countRows->fetch();

			return $result["count"];
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		$dataArray = $qb->execute()->fetchAll();

		return $dataArray;
    }
	
    public function findPoemByPoemUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $locale, $count = false)
    {
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.title', 'u.username');
		
		$qb->select("pf.id AS poem_id, pf.title AS poem_title, u.username AS username, u.id AS user_id, pf.slug AS slug")
		   ->from("poem", "pf")
		   ->where("pf.authorType = 'user'")
		   ->join("pf", "user", "u", "pf.user_id = u.id")
		   ->andWhere("pf.state = 0");
		   
		$this->whereLanguage($qb, 'pf', $locale);
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('pf.title LIKE "'.$search.'"');
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
	
	public function getPoemByCountryDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $countryId, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.id', 'pf.title', 'pf.id');
		
		$qb->select("pf.title AS poem_title, bi.title AS biography_title, bi.slug AS biography_slug, pf.id AS poem_id, bi.id AS biography_id, pf.slug AS poem_slug")
		   ->from("poem", "pf")
		   ->innerjoin("pf", "biography", "bi", "pf.biography_id = bi.id")
		   ->where("pf.country_id = :id")
		   ->setParameter("id", $countryId)
		   ->andWhere("pf.authorType = :authorType")
		   ->setParameter("authorType", "biography")
		   ;
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('pf.title LIKE :search')
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

	public function checkForDoubloon($entity)
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("COUNT(*) AS number")
		   ->from("poem", "pf")
		   ->where("pf.title = :title")
		   ->setParameter('title', $entity->getTitle())
		   ->andWhere("pf.biography_id = :biographyId")
		   ->setParameter("biographyId", $entity->getBiography());

		if($entity->getId() != null)
		{
			$qb->andWhere("pf.id != :id")
			   ->setParameter("id", $entity->getId());
		}
		return $qb->execute()->fetchColumn();
	}
	
	public function getStat($locale)
	{
		$qbPoem = $this->db->createQueryBuilder();

		$qbPoem->select("COUNT(*) AS count_poem")
			   ->from("poem", "pf");
			   
		$this->whereLanguage($qbPoem, "pf", $locale);

		$resultPoem = $qbPoem->execute()->fetchColumn();
		
		$qbBio = $this->db->createQueryBuilder();

		$qbBio->select("COUNT(*) AS count_biography")
		      ->from("biography", "bp");
			  
		$this->whereLanguage($qbBio, "bp", $locale);
		
		$resultBio = $qbBio->execute()->fetchColumn();
		
		$qbCo = $this->db->createQueryBuilder();

		$qbCo->select("COUNT(*) AS count_collection")
		      ->from("collection", "bp");
		
		$this->whereLanguage($qbCo, "bp", $locale);
		
		$resultCo = $qbCo->execute()->fetchColumn();
		
		return array("count_poem" => $resultPoem, "count_biography" => $resultBio, "count_collection" => $resultCo);
	}

	public function findPoemByUserAndAuhorType($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, $currentUser, $authorType, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.id', 'pf.title', 'pf.state', 'pf.id');
		
		$qb->select("pf.*")
		   ->from("poem", "pf")
		   ->leftjoin("pf", "user", "pfu", "pf.user_id = pfu.id")
		   ->where("pfu.username = :username")
		   ->setParameter("username", $username)
		   ->andWhere("pf.state <> 2")
		   ->andWhere('pf.authorType = :authorType')
		   ->setParameter('authorType', $authorType);

		if($username != $currentUser->getUsername())
		{
			$qb->andWhere("pf.state = 0");
		}
		
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
		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data);
        }

		return $entitiesArray;
	}
	
	public function browsingPoemShow($params, $poemId)
	{
		// Previous
		$subqueryPrevious = 'p.id = (SELECT MAX(p2.id) FROM poem p2 WHERE p2.id < '.$poemId.')';
		$qb_previous = $this->db->createQueryBuilder();
		
		$qb_previous->select("p.id, p.title, p.slug")
		   ->from("poem", "p")
		   ->where('p.'.$params["field"].' = :biographyId')
		   ->setParameter('biographyId', $params["author"])
		   ->andWhere($subqueryPrevious);
		   
		// Next
		$subqueryNext = 'p.id = (SELECT MIN(p2.id) FROM poem p2 WHERE p2.id > '.$poemId.')';
		$qb_next = $this->db->createQueryBuilder();
		
		$qb_next->select("p.id, p.title, p.slug")
		   ->from("poem", "p")
		   ->where('p.'.$params["field"].' = :biographyId')
		   ->setParameter('biographyId', $params["author"])
		   ->andWhere($subqueryNext);
		
		$res = array(
			"previous" => $qb_previous->execute()->fetch(),
			"next" => $qb_next->execute()->fetch()
		);

		return $res;
	}
}