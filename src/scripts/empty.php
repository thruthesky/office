<?php
use Drupal\office\Entity\Employee;
use Drupal\office\Entity\Group;
use Drupal\office\Entity\Task;

empty_office();

function empty_office() {

	$employees = Employee::loadMultiple();
	if ( $employees ) {
		foreach ( $employees as $employee ) {
			$employee->delete();
		}
	}

	$groups = Group::loadMultiple();
	if ( $groups ) {
		foreach ( $groups as $group ) {
			$group->delete();
		}
	}


	$tasks = Task::loadMultiple();
	if ( $tasks ) {
		foreach ( $tasks as $task ) {
			$task->delete();
		}
	}

}
