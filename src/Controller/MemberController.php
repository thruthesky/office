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
		$data = x::officeInformation();


		if ( x::admin() ) {
			$ids = \Drupal::entityQuery('office_member')->execute();
		}
		else {
			
		}


		$entities = \Drupal::entityManager()->getStorage('office_member')->loadMultiple($ids);
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
		if ( ! x::login() ) return x::loginResponse();
		if (x::isFromSubmit())  Member::formSubmit($data);
		$data['member'] = Member::loadByUserID(x::myUid());
		$data['groups'] = Group::loadMultiple();
		return [
			'#theme' => 'member.edit',
			'#data' => $data,
		];
	}

}