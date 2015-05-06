<?php
use Drupal\office\Entity\Group;
use Drupal\office\Entity\Member;
use Drupal\office\Entity\Task;
set_default_data();
function set_default_data() {
	$group_id = 0;
	$groups = [
		1 => 'Sonub Group',
		2 => 'VIU Consultancy',
		3 => 'Withcenter, Inc.'
	];
	foreach( $groups as $user_id => $name ) {
		$group = Group::create();
		$group->set('name', $name);
		$group->set('user_id', $user_id);
		$group->save();
		$group_id = $group->id();
	}

	$entity = Member::create();
	$entity->set('first_name', "First Name: admin");
	$entity->set('address', "This is ADMIN address");
	$entity->set('group_id', $group_id);
	$entity->set('user_id', 1); // admin
	$entity->save();


	$task = Task::create();
	$task->set('title', "This is the first task!");
	$task->set('description', "This is a test task created by default-data.");
	$task->set('group_id', $group_id);
	$task->set('creator_id', 1); // admin
	$task->set('worker_id', 1); // admin
	$task->set('in_charge_id', 1); // admin
	$task->save();



}
