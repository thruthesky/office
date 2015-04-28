<?php
use Drupal\office\Entity\Employee;
use Drupal\office\Entity\Group;
use Drupal\office\Entity\Issue;

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


	$issues = Issue::loadMultiple();
	if ( $issues ) {
		foreach ( $issues as $issue ) {
			$issue->delete();
		}
	}

}
