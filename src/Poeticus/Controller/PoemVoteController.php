<?php

namespace Poeticus\Controller;

use Poeticus\Entity\PoemVote;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;

class PoemVoteController
{
	public function voteAction(Request $request, Application $app, $idPoem)
	{
		$vote = $request->query->get('vote');
		
		$state = "";
		
		if(!empty($vote))
		{
			$user = $app['security']->getToken()->getUser();
			
			if(is_object($user))
			{
				$vote = ($vote == "up") ? 1 : -1;

				$entity = new PoemVote();
				
				$entity->setVote($vote);
				$entity->setPoem($app['repository.poem']->find($idPoem));
				
				
				$userDb = $app['repository.user']->findByUsernameOrEmail($user->getUsername());
				$entity->setUser($userDb);
			
				$numberOfDoubloons = $app['repository.poemvote']->checkIfUserAlreadyVote($idPoem, $userDb->getId());
				
				if($numberOfDoubloons >= 1)
					$state = "Vous avez déjà voté pour cette poésie";
				else
					$app['repository.poemvote']->save($entity);
			}
			else
				$state = "Vous devez être connecté pour pouvoir voter !";
		}

		$up_values = $app['repository.poemvote']->countVoteByPoem($idPoem, 1);
		$down_values = $app['repository.poemvote']->countVoteByPoem($idPoem, -1);
		$total = $up_values + $down_values;
		$value = ($total == 0) ? 50 : round(((100 * $up_values) / $total), 1);

		$response = new Response(json_encode(array("up" => $up_values, "down" => $down_values, "value" => $value, "alreadyVoted" => $state)));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
}