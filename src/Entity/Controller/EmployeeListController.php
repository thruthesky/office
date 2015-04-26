<?php
namespace Drupal\office\Entity\Controller;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;
class EmployeeListController extends EntityListBuilder {
	public function buildHeader() {
		$header['id'] = t('EmployeeID');
		$header['name'] = t('Name');
		return $header + parent::buildHeader();
	}
	public function buildRow(EntityInterface $entity) {
		/* @var $entity \Drupal\office\Entity\Employee */
		$row['id'] = $entity->id();
		$row['name'] = \Drupal::l(
			$this->getLabel($entity),
			new Url(
				'entity.employee.edit_form', array(
					'employee' => $entity->id(),
				)
			)
		);
		return $row + parent::buildRow($entity);
	}
}