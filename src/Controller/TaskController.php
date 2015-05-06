<?php
namespace Drupal\office\Controller;
use Drupal\Core\Routing\RouteProvider;
use Drupal\office\Entity\Group;
use Drupal\office\Entity\Task;
use Drupal\office\x;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TaskController extends ControllerBase {
	public function collection() {
		$ids = \Drupal::entityQuery('office_task')->execute();
		$entities = task::loadMultipleFull($ids);
		return [
			'#theme' => 'task.list',
			'#data' => [ 'tasks' => $entities ],
		];
	}
	public function edit() {
		if ( ! x::login() ) return x::loginResponse();
		$data = [];
		if (x::isFromSubmit()) $id = task::formSubmit($data);
		else $id = 0;
		$data['task'] = task::loadFull($id);
		$data['groups'] = Group::loadMultiple();
		return [
			'#theme' => 'task.edit',
			'#data' => $data,
		];

	}
}