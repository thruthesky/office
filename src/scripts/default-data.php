<?php
use Drupal\office\Entity\Employee;
use Drupal\office\Entity\Group;
use Drupal\office\Entity\Issue;
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

	$employee = Employee::create();
	$employee->set('first_name', "First Name: admin");
	$employee->set('address', "This is ADMIN address");
	$employee->set('group_id', $group_id);
	$employee->set('user_id', 1); // admin
	$employee->save();


	$issue = Issue::create();
	$issue->set('name', "This is the first issue!");
	$issue->set('description', "This is a test issue created by default-data.");
	$issue->set('group_id', $group_id);
	$issue->set('client_id', 1); // admin
	$issue->save();



}
