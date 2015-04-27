<?php

/**
 * @file
 * Contains Drupal\office\Entity\Controller\ClientListController.
 */

namespace Drupal\office\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides a list controller for Client entity.
 *
 * @ingroup office
 */
class ClientListController extends EntityListBuilder {
	/**
	 * {@inheritdoc}
	 */
	public function buildHeader() {
		$header['id'] = t('ClientID');
		$header['name'] = t('Name');
		return $header + parent::buildHeader();
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildRow(EntityInterface $entity) {
		/* @var $entity \Drupal\office\Entity\Client */
		$row['id'] = $entity->id();
		$row['name'] = \Drupal::l(
			$this->getLabel($entity),
			new Url(
				'entity.office_client.edit_form', array(
					'office_client' => $entity->id(),
				)
			)
		);
		return $row + parent::buildRow($entity);
	}

}
