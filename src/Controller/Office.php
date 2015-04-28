<?php
namespace Drupal\office\Controller;
use Drupal\office\x;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Office extends ControllerBase {
	public function pageAdmin() {
		$markup = "
		<div class='admin buttons'>
			<div class='button'><a href='/admin/office/client'>Client Management</a></div>
		</div>
		<div class='admin buttons'>
			<div class='button'><a href='/admin/office/employee'>Employee Management</a></div>
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
		return [
			'#theme' => 'office',
			'#data' => [],
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
}
