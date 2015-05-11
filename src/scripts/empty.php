<?php

use Drupal\office\Entity\Group;
use Drupal\office\Entity\Task;

empty_office();

function delete_all_entity_item($entity_type)
{
	$entities = \Drupal::entityManager()->getStorage($entity_type)->loadMultiple();
	if ( $entities ) {
		foreach ( $entities as $entitiy ) {
			$entitiy->delete();
		}
	}
}

function empty_office() {
	delete_all_entity_item('office_member');
	delete_all_entity_item('office_group');
	delete_all_entity_item('office_task');
	delete_all_entity_item('office_attendance');
	delete_all_entity_item('office_process');
}
