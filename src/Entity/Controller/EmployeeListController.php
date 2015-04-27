<?php
namespace Drupal\office\Entity\Controller;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;
use Drupal\office\Entity\Group;

class EmployeeListController extends EntityListBuilder {
	public function buildHeader() {
		$header['id'] = t('EmployeeID');
		$header['name'] = t('Name');
		$header['group'] = t('Group');
		return $header + parent::buildHeader();
	}
	public function buildRow(EntityInterface $entity) {
		/* @var $entity \Drupal\office\Entity\Employee */
		$row['id'] = $entity->id();
		$row['name'] = \Drupal::l(
			$this->getLabel($entity),
			new Url(
				'entity.office_employee.edit_form', array(
					'office_employee' => $entity->id(),
				)
			)
		);
		$group_id = $entity->get('group_id')->value;
		$group = Group::load($group_id)->label();
		$row['group'] = $group;
		return $row + parent::buildRow($entity);
	}
}