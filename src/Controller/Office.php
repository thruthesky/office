<?php
namespace Drupal\office\Controller;
use Drupal\office\x;
use Drupal\Core\Controller\ControllerBase;

class Office extends ControllerBase {
	public function pageAdmin() {
		$markup = "
		<div class='admin buttons'>
			<div class='button'><a href='/admin/client'>Client Management</a></div>
		</div>
		<div class='admin buttons'>
			<div class='button'><a href='/admin/employee'>Employee Management</a></div>
		</div>
		<div class='admin buttons'>
			<div class='button'><a href='/admin/group'>Group Management</a></div>
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
}
