<?php
namespace Drupal\office\Controller;
use Drupal\Core\Routing\RouteProvider;
use Drupal\office\Entity\Member;
use Drupal\office\Entity\Group;
use Drupal\office\x;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MemberController extends ControllerBase {
	public function collection() {
		$data = [];
		$request = \Drupal::request();
		if ( $request->get('mode') == 'submit' ) {
			$group = Member::myOwnGroup(x::myUid());
			$user = user_load_by_name($request->get('name'));
			if ( $user ) {
				$member = Member::loadByUserID($user->id());
				if ( $member ) {
					$member->set('group_id', $group->id());
					$member->save();
				}
				else {
					x::messageNotGroupMemberToAddIntoGroup($data);
				}
			}
			else {
				x::messageUserNotExist($data);
			}
		}

		$db = \Drupal::entityQuery('office_member');
		if ( x::admin() ) {

		}
		else {
			$group = Member::myOwnGroup(x::myUid());
			if ( $group ) {
				$my_group_id = $group->id();
				$db->condition('group_id', $my_group_id);
			}
		}
		$member_ids = $db->execute();
		$entities = \Drupal::entityManager()->getStorage('office_member')->loadMultiple($member_ids);
		$data['members'] = $entities;
		return [
			'#theme' => 'member.list',
			'#data' => $data,
		];
	}
	public function view($member) {
		$data = [];
		$member = Member::load($member);
		$data['member'] = $member;
		$data['user'] = $member->getOwner();
		return [
			'#theme' => 'member.view',
			'#data' => $data,
		];
	}

	/**
	 * member add/edit
	 *
	 * @return array
	 */
	public function edit() {
		$data = [];
		$data['mode'] = x::g('mode');
		//if ( ! x::login() ) return x::loginResponse();
		if ( ! x::login() ) x::messageLoginFirst($data);
		else {
			if (x::isFromSubmit())  Member::formSubmit($data);
			$data['member'] = Member::loadByUserID(x::myUid());
			$data['groups'] = Group::loadMultiple();
		}
		return [
			'#theme' => 'member.edit',
			'#data' => $data,
		];
	}

}