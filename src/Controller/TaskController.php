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
		// 마지막 검색을 쿠키에 저장한다.
		if ( x::in('mode') == 'submit' ) {
			$input = serialize(x::getInput());
			x::set_cookie('task_search_input', $input);
		}
		else {
			$value =  x::get_cookie('task_search_input');
			$input = unserialize($value);
			x::setInput($input);
		}

		$db = \Drupal::entityQuery('office_task');
		if ( $group_id = x::in('group_id') ) {
			$db->condition('group_id', $group_id);
		}
		if ( $creator = x::in('creator') ) {
			$creator_id = x::getUserID($creator);
			$db->condition('creator_id', $creator_id);
		}
		if ( $worker = x::in('worker') ) {
			$worker_id = x::getUserID($worker);
			$db->condition('worker_id', $worker_id);
		}
		if ( $in_charge = x::in('in_charge') ) {
			$in_charge_id = x::getUserID($in_charge);
			$db->condition('in_charge_id', $in_charge_id);
		}
		if ( $client = x::in('client') ) {
			$client_id = x::getUserID($client);
			$db->condition('client_id', $client_id);
		}
		if ( $sort = x::in('sort') ) {
			$db->sort($sort, x::in('by'));
		}
		$ids  = $db->execute();
		$entities = task::loadMultipleFull($ids);
		return [
			'#theme' => 'task.list',
			'#data' => [ 'tasks' => $entities ],
		];
	}
	public function add() {
		$data = [];
		if ( ! x::login() ) {
			x::messageLoginFirst($data);
		}
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