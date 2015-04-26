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
				'entity.group.edit_form', array(
					'group' => $entity->id(),
				)
			)
		);
		return $row + parent::buildRow($entity);
	}

}
