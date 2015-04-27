<?php
use Drupal\office\Entity\Employee;
use Drupal\office\Entity\Group;
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
	$employee->set('name', "First Name: admin");
	$employee->set('address', "This is ADMIN address");
	$employee->set('group_id', $group_id);
	$employee->set('user_id', 1); // admin
	$employee->save();




}
