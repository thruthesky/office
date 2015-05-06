<?php
namespace Drupal\office\Entity\Controller;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;
use Drupal\office\Entity\Group;

class MemberListController extends EntityListBuilder {
	public function buildHeader() {
		$header['id'] = t('MemberID');
		$header['name'] = t('Name');
		$header['group'] = t('Group');
		return $header + parent::buildHeader();
	}
	public function buildRow(EntityInterface $entity) {
		/* @var $entity \Drupal\office\Entity\Member */

		$row['id'] = $entity->id();

		$member_name =
			$entity->get('first_name')->value . ' ' .
			$entity->get('last_name')->value;


		$row['name'] = \Drupal::l(
			$member_name,
			new Url(
				'entity.office_member.edit_form', array(
					'office_member' => $entity->id(),
				)
			)
		);


		$group_id = $entity->get('group_id')->value;

		if ($group_id) {
			$name = Group::load($group_id)->label();
			if (empty($name)) $name = "No group name";
			$row['group'] = $name;
		}
		else $row['group'] = 'No group';
		return $row + parent::buildRow($entity);
	}
}