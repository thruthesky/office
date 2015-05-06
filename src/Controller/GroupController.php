<?php
namespace Drupal\office\Controller;
use Drupal\office\Entity\Group;
use Drupal\office\GroupInterface;
use Drupal\office\x;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GroupController extends ControllerBase {
	public function collection() {
		$ids = \Drupal::entityQuery('office_group')->execute();
		$groups = \Drupal::entityManager()->getStorage('office_group')->loadMultiple($ids);
		return [
			'#theme' => 'group.list',
			'#data' => [ 'groups' => $groups ],
		];
	}
	public function view($group) {
		$data = [];
		$group = Group::load($group);
		$data['group'] = $group;
		$data['user'] = $group->getOwner();
		return [
			'#theme' => 'group.view',
			'#data' => $data,
		];
	}

	/**
	 * Group  add/edit
	 *
	 * @param \Drupal\office\Entity\Group $group
	 * @return array
	 * @internal param $group_id
	 */
	public function edit(GroupInterface $office_group=NULL) {
		x::log(__METHOD__);
		$data = [];
		$data['mode'] = x::g('mode');
		if ( x::login() ) {
			if (x::isFromSubmit()) {
				$group_id = Group::formSubmit($data);
				$office_group = Group::load($group_id);
			}
			if ( $office_group ) {
				$data['group'] = $office_group;
				$data['config']['work'] = x::getGroupWorkingDays($office_group->id());
				$data['config']['dayoffs'] = x::getGroupDayoffs($office_group->id());
			}
			//di($data['config']);
			return [
				'#theme' => 'group.edit',
				'#data' => $data,
			];
		}
		else {
			return new RedirectResponse('/office/login');
		}
	}

	public function workinghours(GroupInterface $office_group=NULL) {
		x::log(__METHOD__);
		$data = [];
		$data['mode'] = x::in('mode');

		if ( x::in('mode') == 'submit' ) {
			$group_id = x::in('group_id');
			if ( x::in('for') == 'group' ) {
				x::setGroupTime($group_id, x::in('begin_time'), x::in('end_time'));
			}
			else if ( x::in('for') == 'individual' ) {
				x::setIndividualTime($group_id, x::in('name'), x::in('date'), x::in('begin_time'), x::in('end_time'), x::in('reason'));
			}
			else if ( x::in('for') == 'delete' ) {
				x::deleteIndividualTime(x::in('idx'));
			}
			$data['code'] = 'workinghours_updated';
			$data['message'] = 'Updated';
		}
		else {
		}
		$data['group'] = $office_group;
		$group_id = $office_group->id();
		$data['work'] = x::getGroupWorkingHours($group_id);
		$data['member_work'] = x::getIndividualTime($group_id);


		return [
			'#theme' => 'group.workinghours',
			'#data' => $data,
		];
	}

}