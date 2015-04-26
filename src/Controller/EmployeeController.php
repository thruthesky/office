<?php
namespace Drupal\office\Controller;
use Drupal\Core\Routing\RouteProvider;
use Drupal\office\Entity\Employee;
use Drupal\office\x;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;

class EmployeeController extends ControllerBase {
	public function collection() {
		$eids = \Drupal::entityQuery('employee')->execute();
		$employees = \Drupal::entityManager()->getStorage('employee')->loadMultiple($eids);
		return [
			'#theme' => 'employee.list',
			'#data' => [ 'employees' => $employees ],
		];
	}
	public function view($employee) {
		$data = [];
		$employee = Employee::load($employee);
		$data['employee'] = $employee;
		$uid = $employee->get('user_id')->target_id;
		$data['user'] = User::load($uid);
		return [
			'#theme' => 'employee.view',
			'#data' => $data,
		];
	}
}