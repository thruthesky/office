<?php
namespace Drupal\office\Entity;
use Drupal\office\GroupInterface;
use Drupal\office\GroupMemberInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\office\x;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Defines the TaskLog entity.
 *
 *
 * @ContentEntityType(
 *   id = "office_groupmember",
 *   label = @Translation("GroupMember entity"),
 *   base_table = "office_groupmember",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   }
 * )
 */
class GroupMember extends ContentEntityBase implements GroupMemberInterface {
	public static function loadBy(GroupInterface &$group, $name) {
		if ( $user = user_load_by_name($name) ) {
			$ids = \Drupal::entityQuery('office_groupmember')
				->condition('group_id', $group->id())
				->condition('user_id', $user->id())
				->execute();
			$members = self::loadMultiple($ids);
			return $members ? reset($members) : null;
		}
		else return null;
	}

	public static function addUser(GroupInterface &$group, $name) {
		if ( $user = user_load_by_name($name) ) {
			$member = self::create();
			$member->set('user_id', $user->id());
			$member->set('group_id', $group->id());
			$member->save();
			return $member->id();
		}
		else return 0;
	}

	/**
	 *
	 * 그룹 회원 전체 또는 개별 목록을 리턴한다.
	 *
	 * 두번째 인자에 값이 있으면 해당 그룹의 해당 회원 레코드만 리턴한다.
	 *
	 * @param $group_id
	 * @param int $user_id
	 * @return array
	 *
	 * @code
	 *	$group_members = GroupMember::loadByGroup($office_group->id(), $user->id());
	 * 	$id = $group_members ? reset($group_members) : null;
	 * @endcode
	 */
	public static function loadByGroup($group_id,$user_id=0) {
		$prop = ['group_id' => $group_id];
		if ( $user_id ) $prop['user_id'] = $user_id;
		$members = entity_load_multiple_by_properties('office_groupmember', $prop);
		if ( $members ) {
			foreach( $members as $member ) {
				$member->attr = Member::loadByUserID($member->get('user_id')->target_id);
			}
		}
		return $members;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getCreatedTime() {
		return $this->get('created')->value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getChangedTime() {
		return $this->get('changed')->value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOwner() {
		return $this->get('user_id')->entity;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOwnerId() {
		return $this->get('user_id')->target_id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setOwnerId($uid) {
		$this->set('user_id', $uid);
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setOwner(UserInterface $account) {
		$this->set('user_id', $account->id());
		return $this;
	}


	public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
		$fields['id'] = BaseFieldDefinition::create('integer')
			->setLabel(t('ID'))
			->setDescription(t('The ID of the Task entity.'))
			->setReadOnly(TRUE);
		$fields['uuid'] = BaseFieldDefinition::create('uuid')
			->setLabel(t('UUID'))
			->setDescription(t('The UUID of the Task entity.'))
			->setReadOnly(TRUE);

		$fields['langcode'] = BaseFieldDefinition::create('language')
			->setLabel(t('Language code'))
			->setDescription(t('The language code of entity.'));
		$fields['created'] = BaseFieldDefinition::create('created')
			->setLabel(t('Created'))
			->setDescription(t('The time that the entity was created.'));
		$fields['changed'] = BaseFieldDefinition::create('changed')
			->setLabel(t('Changed'))
			->setDescription(t('The time that the entity was last edited.'));

		$fields['user_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('User ID'))
			->setDescription(t('The person who created this task.'))
			->setSetting('target_type', 'user');

		$fields['group_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('Group ID'))
			->setDescription(t('The group id this entity to.'))
			->setSetting('target_type', 'office_group');

		return $fields;
	}


}
