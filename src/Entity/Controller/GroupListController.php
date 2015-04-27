<?php

/**
 * @file
 * Contains Drupal\office\Entity\Controller\GroupListController.
 */

namespace Drupal\office\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for Group entity.
 *
 * @ingroup office
 */
class GroupListController extends EntityListBuilder {
	/**
	 * {@inheritdoc}
	 */
	public function buildHeader() {
		$header['id'] = t('GroupID');
		$header['name'] = t('Name');
		$header['no'] = t('No. of Employees');
		return $header + parent::buildHeader();
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildRow(EntityInterface $entity) {
		/* @var $entity \Drupal\office\Entity\Group */
		$row['id'] = $entity->id();
		$row['name'] = \Drupal::l(
			$this->getLabel($entity),
			new Url(
				'entity.office_group.edit_form', array(
					'office_group' => $entity->id(),
				)
			)
		);

		$count = \Drupal::entityQuery('office_employee')
			->condition('group_id', $entity->id())
			->count()
			->execute();
		$row['no'] = $count;


		return $row + parent::buildRow($entity);
	}

}
