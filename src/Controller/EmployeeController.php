<?php
namespace Drupal\office\Controller;
use Drupal\Core\Routing\RouteProvider;
use Drupal\office\Entity\Employee;
use Drupal\office\Entity\Group;
use Drupal\office\x;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;

class EmployeeController extends ControllerBase {
	public function collection() {
		$eids = \Drupal::entityQuery('office_employee')->execute();
		$employees = \Drupal::entityManager()->getStorage('office_employee')->loadMultiple($eids);
		return [
			'#theme' => 'employee.list',
			'#data' => [ 'employees' => $employees ],
		];
	}
	public function view($employee) {
		$data = [];
		$employee = Employee::load($employee);
		$data['employee'] = $employee;
		$data['user'] = $employee->getOwner();
		return [
			'#theme' => 'employee.view',
			'#data' => $data,
		];
	}
	public function edit() {
		$data = [];
		if ( x::isFromSubmit() ) {
			$re = x::employeeFormSubmit();
			if ( $re ) {
				$data['error']['code'] = $re;
				$data['error']['message'] = x::errorMessage($re);
			}
		}
		$data['employee'] = Employee::loadByUserID(x::myUid());
		$data['groups'] = Group::loadMultiple();
		return [
			'#theme' => 'employee.add',
			'#data' => $data,
		];
	}
}