<?php
namespace Drupal\office\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\office\Entity\Process;
use Drupal\office\x;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
class API extends ControllerBase {
	public function DefaultController()
	{
		x::log(__METHOD__);
		$request = \Drupal::request();
		$call = $request->get('call');
		$re = $this->$call($request);
		$response = new JsonResponse( $re );
		$response->headers->set('Access-Control-Allow-Origin', '*');
		return $response;
	}
	public function autocomplete() {
		x::log(__METHOD__);
		x::log(x::input());

		if ( x::in('type') == 'member' ) {
			$base = 'user';
		}
		else {
			$base = 'office_' . x::in('type');
		}

		$ids = \Drupal::entityQuery($base)
			->condition('name', x::in('term'), 'CONTAINS')
			->execute();
		x::log($ids);

		if ( x::in('type') == 'member' ) {
			$entities = user::loadMultiple($ids);
		}
		else if ( x::in('type') == 'process' ) {
			$entities = Process::loadMultiple($ids);
		}
		$ret = [];
		if ( ! empty($entities) ) {
			foreach( $entities as $entity ) {
				$id = $entity->id();
				if ( x::in('type') == 'member' ) {
					$value = $entity->getUsername();
				}
				else {
					$value = $entity->get('name')->value;
				}
				$ret[] = ['id'=>$id, 'value'=>$value];
			}
		}
		x::log($ret);
		return $ret;
	}
	public function dayoff() {
		if ( ! x::in('group_id') ) return ['error'=> -1, 'message'=>'No group id'];
		if ( ! x::in('dayoff') ) return ['error'=> -2, 'message'=>'Please input date. No date for dayoff.'];
		if ( ! x::in('reason') ) return ['error'=> -3, 'message'=>'Please input the reason why it is a dayoff. No reason for the dayoff'];
		$code = 'group_'.x::in('group_id').'_dayoff';
		$date = x::in('dayoff');
		$date = str_replace('-', '', $date);
		$category = $date;
		if ( x::config_exist($code, $category) ) {
			$re = [
				'error' => -1,
				'message' => 'Already exists. Delete the record first if you want to update.',
			];
		}
		else {
			x::config('group_'.x::in('group_id').'_dayoff', $date, x::in('reason'));
			$re = [
				'error' => 0,
				'message' => 'Added',
				'date' => x::in('dayoff'),
				'reason' => x::in('reason'),
			];
		}
		return $re;
	}

	public function dayoff_delete() {
		if ( ! x::in('group_id') ) return ['error'=> -1, 'message'=>'No group id'];
		if ( ! x::in('dayoff') ) return ['error'=> -2, 'message'=>'Please input date. No date for dayoff.'];
		$code = 'group_'.x::in('group_id').'_dayoff';

		$date = x::in('dayoff');
		$date = str_replace('-', '', $date);
		$category = $date;
		if ( x::config_exist($code, $category) ) {
			x::config_delete($code, $category);
			return ['error'=>0, 'date'=>$category, 'message'=>"The date - $category - has been deleted."];
		}
		else {
			return ['error'=>-3, 'message'=>'The day does not exist as in dayoff.'];
		}
	}


	public function load_process() {
		$id = x::in('process_id');
		if ( $id ) return x::markupProcess($id);
		return null;
	}
}
