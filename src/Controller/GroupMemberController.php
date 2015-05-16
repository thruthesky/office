<?php
namespace Drupal\office\Controller;
use Drupal\office\Entity\Group;
use Drupal\office\Entity\GroupMember;
use Drupal\office\Entity\Member;
use Drupal\office\GroupInterface;
use Drupal\office\x;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GroupMemberController extends ControllerBase {

	/**
	 * @param \Drupal\office\GroupInterface $office_group
	 * @return array
	 */
	public function collection(GroupInterface $office_group = NULL) {
		x::log(__METHOD__);
		$data = [];
		if (x::in('mode') == 'submit') {
			if ($name = x::in('name')) {
				if ( $user = user_load_by_name($name)  ) {
					if (x::in('for') == 'add') { // 그룹 회원 추가
						if ($group_member = GroupMember::loadBy($office_group, $name)) x::messageGroupUserAlreadyAdded($data);
						else {
							if ( $member = Member::loadByUsername($name) ) {
								if ($id = GroupMember::addUser($office_group, $name)) x::messageGroupUserAdded($data);
								else x::messageUserNotExist($data); // 이 에러는 발생하지 않는다.
							}
							else x::messageNotGroupMemberToAddIntoGroup($data);
						}
					}
					else if (x::in('for') == 'member-add') {
						if ( $member = Member::loadByUsername($name) ) {
							if ( $member->get('group_id')->target_id == $office_group->id() ) x::messageGroupUserAlreadyAdded($data);
							else $member->set('group_id', $office_group->id())->save();
						}
						else x::messageNotGroupMemberToAddIntoGroup($data);
					}
					else if (x::in('for') == 'user-delete') { // 그룹 회원 삭제
						if ( $member = Member::loadByUsername($name) ) {
							$group_members = GroupMember::loadByGroup($office_group->id(), $user->id());
							$member = $group_members ? reset($group_members) : null;
							if ( $member ) GroupMember::load($member->id())->delete();
							x::messageGroupUserDeleted($data);
						}
						else x::messageNotGroupMemberToDelete($data);
					}
					else if (x::in('for') == 'member-delete') {
						if ( $member = Member::loadByUsername($name) ) {
							if ( $member->get('group_id')->target_id == $office_group->id() ) {
								$member->set('group_id', 0)->save();
								x::messageGroupMemberDeleted($data);
							}
							else x::messageNotYourGroupMember($data);
						}
						else x::messageNotGroupMemberToDelete($data);
					}
				}
				else x::messageUserNotExist($data);
			}
			else {
				$data['code'] = 'error input-name';
				$data['message'] = 'Input member name to add to your Group.';
			}
		}
		$data['group'] = $office_group;
		$data['group_members'] = entity_load_multiple_by_properties('office_member', ['group_id'=>$office_group->id()]);
		$data['group_users'] = GroupMember::loadByGroup($office_group->id());

		$data['office_members'] = Member::loadMultiple();
		// 그룹 회원 정보 리턴.
		return [
			'#theme' => 'group.memberlist',
			'#data' => $data,
		];
	}
}