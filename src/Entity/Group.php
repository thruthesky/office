<?php

/**
 * @file
 * Contains Drupal\office\Entity\Group.
 */

namespace Drupal\office\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\office\GroupInterface;
use Drupal\office\x;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Defines the Group entity.
 *
 * @ingroup office
 *
 * @ContentEntityType(
 *   id = "office_group",
 *   label = @Translation("Group entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\office\Entity\Controller\GroupListController",
 *     "views_data" = "Drupal\office\Entity\GroupViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\office\Entity\Form\GroupForm",
 *       "add" = "Drupal\office\Entity\Form\GroupForm",
 *       "edit" = "Drupal\office\Entity\Form\GroupForm",
 *       "delete" = "Drupal\office\Entity\Form\GroupDeleteForm",
 *     },
 *   },
 *   base_table = "office_group",
 *   admin_permission = "administer Group entity",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "entity.office_group.canonical",
 *     "edit-form" = "entity.office_group.edit_form",
 *     "delete-form" = "entity.office_group.delete_form",
 *     "collection" = "entity.office_group.collection"
 *   },
 *   field_ui_base_route = "office_group.settings"
 * )
 */
class Group extends ContentEntityBase implements GroupInterface {
	/**
	 *
	 * 관리자이거나 그룹장이면 참을 리턴한다.
	 *
	 * @param $group_id
	 * @param $myUid
	 * @return bool
	 */
	public static function isAdmin($group_id, $myUid) {
		if ( x::admin() ) return true;
		$group = Member::myOwnGroup($myUid);
		if ( $group ) return true;
		else return false;
		//di("group id:$group_id");
		//di($group->id());

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

	/**
	 * {@inheritdoc}
	 */
	public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
		$fields['id'] = BaseFieldDefinition::create('integer')
			->setLabel(t('ID'))
			->setDescription(t('The ID of the Group entity.'))
			->setReadOnly(TRUE);

		$fields['uuid'] = BaseFieldDefinition::create('uuid')
			->setLabel(t('UUID'))
			->setDescription(t('The UUID of the Group entity.'))
			->setReadOnly(TRUE);

		$fields['user_id'] = BaseFieldDefinition::create('entity_reference')
			->setLabel(t('Group Owner'))
			->setDescription(t('The user ID of the Group owner.'))
			->setRevisionable(TRUE)
			->setSetting('target_type', 'user')
			->setSetting('handler', 'default')
			->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')

			->setTranslatable(TRUE)
			->setDisplayOptions('view', array(
				'label' => 'hidden',
				'type' => 'author',
				'weight' => 0,
			))
			->setDisplayOptions('form', array(
				'type' => 'entity_reference_autocomplete',
				'weight' => 5,
				'settings' => array(
					'match_operator' => 'CONTAINS',
					'size' => '60',
					'autocomplete_type' => 'tags',
					'placeholder' => '',
				),
			))
			->setDisplayConfigurable('form', TRUE)
			->setDisplayConfigurable('view', TRUE);

		$fields['name'] = BaseFieldDefinition::create('string')
			->setLabel(t('Name'))
			->setDescription(t('The name of the Group, Company, Organization'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 50,
				'text_processing' => 0,
			))
			->setDisplayOptions('view', array(
				'label' => 'above',
				'type' => 'string',
				'weight' => -4,
			))
			->setDisplayOptions('form', array(
				'type' => 'string_textfield',
				'weight' => -4,
			))
			->setDisplayConfigurable('form', TRUE)
			->setDisplayConfigurable('view', TRUE);

		$fields['langcode'] = BaseFieldDefinition::create('language')
			->setLabel(t('Language code'))
			->setDescription(t('The language code of Group entity.'));
		$fields['created'] = BaseFieldDefinition::create('created')
			->setLabel(t('Created'))
			->setDescription(t('The time that the entity was created.'));

		$fields['changed'] = BaseFieldDefinition::create('changed')
			->setLabel(t('Changed'))
			->setDescription(t('The time that the entity was last edited.'));


		$fields['description'] = BaseFieldDefinition::create('string')
			->setLabel(t('Description'))
			->setDescription(t('Description of the project.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 255,
			));


		$fields['view_status'] = BaseFieldDefinition::create('string')
			->setLabel(t('View status'))
			->setDescription(t('View status of the project.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 1,
			));

		$fields['create_status'] = BaseFieldDefinition::create('string')
			->setLabel(t('Create status'))
			->setDescription(t('Create status of the project.'))
			->setSettings(array(
				'default_value' => '',
				'max_length' => 1,
			));

		return $fields;
	}



	/**
	 * @param array $data
	 * @return mixed
	 *      - returns group id
	 *      - or error information if there is any error.
	 *
	 *
	 */
	public static function formSubmit(array & $data) {
		x::log(__METHOD__);
		if ( ! x::login() ) return x::errorInfoArray(x::error_login_first, $data);
		$in = x::input();

		/**
		 * @note do not return here, so the data will be saved.
		 */
		if ($re = self::validateFormSubmit($data)) {
			if ( x::error_group_name_exist ) return x::in('group_id');
			// error
		}

		$group_id = x::in('group_id');
		if ( $group_id ) {
			$group = Group::load($group_id);
		}
		else {
			$group = Group::create();
		}
		$group->set('name', x::in('name'));
		if ( x::in('owner') ) {
			$group->set('user_id', x::getUserID(x::in('owner')));
		}
		else {
			$group->set('user_id', x::myUid());
		}
		$group->set('description', x::in('description'));
		$group->set('view_status', x::in('view_status'));
		$group->set('create_status', x::in('create_status'));
		$group->save();
		$id = $group->id();

		x::config("group_{$id}_work", 'sunday', x::in('work_sunday'));
		x::config("group_{$id}_work", 'monday', x::in('work_monday'));
		x::config("group_{$id}_work", 'tuesday', x::in('work_tuesday'));
		x::config("group_{$id}_work", 'wednesday', x::in('work_wednesday'));
		x::config("group_{$id}_work", 'thursday', x::in('work_thursday'));
		x::config("group_{$id}_work", 'friday', x::in('work_friday'));
		x::config("group_{$id}_work", 'saturday', x::in('work_saturday'));


		return $id;
	}

	private static function validateFormSubmit(array &$data) {
		$group_id = x::in('group_id');

			// check if group exits.
			$entities = \Drupal::entityManager()->getStorage('office_group')->loadByProperties(['name'=>x::in('name')]);
			if ( $entities ) {
				foreach( $entities as $entity ) {
					$eid = $entity->id();
					if ( $eid != $group_id ) {
						return x::errorInfoArray(x::error_group_name_exist, $data);
					}
				}
			}

		if ( ! x::in('name') ) return x::errorInfoArray(x::error_input_name, $data);
		// if ( ! x::getUserID(x::in('owner')) ) return x::errorInfoArray(x::error_wrong_owner, $data);
		return false;
	}

	public static function loadByUserID($uid) {
		if ( empty($uid) ) return null;
		return x::loadEntityByUserID('office_group', $uid);
	}

}
