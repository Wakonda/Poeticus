<?php

	namespace Poeticus\Repository;
	
	interface iRepository
	{
		public function save($entity, $id = null);
		public function find($id, $show = false);
		public function checkForDoubloon($entity);
		public function build($data, $show = false);
	}