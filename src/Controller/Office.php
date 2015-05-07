<?php
namespace Drupal\office\Controller;
use Drupal\office\Entity\Attendance;
use Drupal\office\Entity\Member;
use Drupal\office\x;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Office extends ControllerBase {
	public function pageAdmin() {
		$markup = "
		<h2>
		Visit /office to get the full control of office module. ( You have to login as admin )
		<a href='/office'>Open office</a>
</h2>

		<div class='admin buttons'>
			<div class='button'><a href='/admin/office/member'>Office Member Management</a></div>
		</div>
		<div class='admin buttons'>
			<div class='button'><a href='/admin/office/group'>Office Group Management</a></div>
		</div>
";

		$render_array = [
			'#type' => 'markup',
			'#markup' => $markup,
		];
		$render_array['#attached']['library'][] = 'office/Admin';
		return $render_array;
	}
	public function pageFront() {
		$data = [];
		return [
			'#theme' => 'office',
			'#data' => $data,
		];
	}
	public function login() {
		$data = [];
		if ( x::isFromSubmit() ) {
			if ( ! x::loginFormSubmit($data) ) return new RedirectResponse('/office');
		}
		return [
			'#theme' => 'login',
			'#data' => $data,
		];
	}

	public function mydesk() {
		$data = [];
		if ( $re = x::checkMember() ) {
			$data = array_merge($data, $re);
		}
		else {
			$data['today'] = date("M d, Y (D)");
			$member = Member::loadByUserID(x::myUid());
			$data['work'] = Attendance::getWorkingHours(x::myUid(), $member->get('group_id')->target_id, date("Ymd"));
		}
		return [
			'#theme' => 'mydesk',
			'#data' => $data,
		];
	}
}
