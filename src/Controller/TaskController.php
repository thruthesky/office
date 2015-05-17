<?php
namespace Drupal\office\Controller;
use Drupal\Core\Routing\RouteProvider;
use Drupal\file\Entity\File;
use Drupal\office\Entity\Group;
use Drupal\office\Entity\Task;
use Drupal\office\Entity\TaskLog;
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

		$keyword = x::in('keyword');

		if ( ! empty($keyword) ) {
			$condition = x::in('search_condition');
			if ( $condition == 'and' ) {
				$words = explode(' ', $keyword);
				foreach( $words as $word ) {
					$or = $db->orConditionGroup();
					$or->condition('title', $word, 'CONTAINS');
					if ( x::in('search_title_only') != 'Y' ) {
						$or->condition('description', $word, 'CONTAINS');
						$or->condition('summary', $word, 'CONTAINS');
					}
					$db->condition($or);
				}
			}
			else if ( $condition == 'or' ) {
				$words = explode(' ', $keyword);
				$or = $db->orConditionGroup();
				foreach( $words as $word ) {
					$or->condition('title', $word, 'CONTAINS');
					if ( x::in('search_title_only') != 'Y' ) {
						$or->condition('description', $word, 'CONTAINS');
						$or->condition('summary', $word, 'CONTAINS');
					}
				}
				$db->condition($or);
			}
			else {
				$or = $db->orConditionGroup();
				$or->condition('title', $keyword, 'CONTAINS');
				if ( x::in('search_title_only') != 'Y' ) {
					$or->condition('description', $keyword, 'CONTAINS');
					$or->condition('summary', $keyword, 'CONTAINS');
				}
				$db->condition($or);
			}
		}

		/*
		if ( $status = x::in('status') ) {
			$db->condition('status', $status);
		}
		*/

		$status = x::in('status', array());
		if ( $status ) {
			$or = $db->orConditionGroup();
			foreach($status as $text => $Y ) {
				 $or->condition('status', $text);
			}
			$db->condition($or);
		}

		$priority = x::in('priority', array());
		if ( $priority ) {
			$or = $db->orConditionGroup();
			foreach($priority as $no) {
				$or->condition('priority', $no);
			}
			$db->condition($or);
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
		if ( ! x::login() ) x::messageLoginFirst($data);
		return [
			'#theme' => 'task.edit',
			'#data' => $data,
		];
	}
	public function edit(Task $office_task=null) {
		if ( ! x::login() ) x::messageLoginFirst($data);

		$id = $office_task->id();
		$data = [];
		if (x::isFromSubmit()) {
			if ( x::in('for') == 'file-delete' ) {
				$file = File::load(x::in('fid'));
				if ( $file ) $file->delete();
			}
			else {
				$id = task::formSubmit($data);
				return new RedirectResponse("/office/task/edit/$id?code=$data[code]&message=$data[message]");
			}
		}

		$data['task'] = Task::loadFull($id);
		$data['logs'] = TaskLog::loadByTaskId($id);

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