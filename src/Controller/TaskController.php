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
		$db = \Drupal::entityQuery('office_task');
		if ( $group_id = x::in('group_id') ) {
			$db->condition('group_id', $group_id);
		}
		$ids  = $db->execute();
		$entities = task::loadMultipleFull($ids);
		return [
			'#theme' => 'task.list',
			'#data' => [ 'tasks' => $entities ],
		];
	}
	public function add() {
		if ( ! x::login() ) return x::loginResponse();
		$data = [];
		return [
			'#theme' => 'task.edit',
			'#data' => $data,
		];
	}
	public function edit(Task $office_task=null) {
		if ( ! x::login() ) return x::loginResponse();

		$data = [];
		if (x::isFromSubmit()) {
			$group_id = task::formSubmit($data);
			$code = 'task-updated';
			$message = "Task has been updated";
			return new RedirectResponse("/office/task/edit/$group_id?code=$code&message=$message");
		}
		else {
			$group_id = $office_task->id();
		}
		$data['task'] = task::loadFull($group_id);

		return [
			'#theme' => 'task.edit',
			'#data' => $data,
		];

	}

	public function view($office_task) {
		$data = [];
		$data['task'] = $office_task;
		return [
			'#theme' => 'task.view',
			'#data' => $data,
		];
	}
}