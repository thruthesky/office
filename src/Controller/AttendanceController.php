<?php
namespace Drupal\office\Controller;
use Drupal\office\Entity\Attendance;
use Drupal\office\Entity\Group;
use Drupal\office\Entity\Member;
use Drupal\office\x;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AttendanceController extends ControllerBase {

	public function attendance_list() {
		$date = x::in('date');
		$data = [];
		$data['input'] = x::input();
		if ( $re = x::checkMember() ) {
			$data = array_merge($data, $re);
		}
		else if ( x::isFromSubmit() && $date ) {
			$Ym = date("Ym", strtotime($date));
			$member = Member::loadByUserID(x::myUid());
			if ( empty($member) ) x::messageNotOfficeMember($data);
			else if ( empty($member->get('group_id')->target_id) ) x::messageNotGroupMember($data);
			else {
				$data['group'] = $member->get('group_id')->entity;
				$re = Attendance::getAttendanceOfMonth(x::myUid(), $Ym);
				$data = array_merge($data,$re);
			}
		}
		else x::messageSelectDate($data);


		return [
			'#theme' => 'attendance.list',
			'#data' => $data,
		];


	}

	/**
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function attend() {
		if ( $re = x::checkMember() ) {
			extract($re);
		}
		else {
			$code = Attendance::attend(x::myUid(), date('Ymd'), time());
			if ( $code ) {
				$message = x::getErrorMessage($code);
			}
			else {
				extract(x::getSuccessMessage('attend'));
			}
		}
		return new RedirectResponse("/office/mydesk?code=$code&message=$message");
	}
	public function leave() {
		return new RedirectResponse('/office/mydesk');
	}

}