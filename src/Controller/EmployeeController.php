<?php
namespace Drupal\office\Controller;
use Drupal\Core\Routing\RouteProvider;
use Drupal\office\Entity\Employee;
use Drupal\office\Entity\Group;
use Drupal\office\x;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

	/**
	 * Employee add/edit
	 *
	 * @return array
	 */
	public function edit() {
		$data = [];
		$data['mode'] = x::g('mode');
		if ( x::login() ) {
			if (x::isFromSubmit()) {
				if ( x::employeeFormSubmit($data) ) {
					// error
				}
				else {
				}
			}
			$data['employee'] = Employee::loadByUserID(x::myUid());
			$data['groups'] = Group::loadMultiple();
			return [
				'#theme' => 'employee.edit',
				'#data' => $data,
			];
		}
		else {
			return new RedirectResponse('/office/login');
		}
	}
}