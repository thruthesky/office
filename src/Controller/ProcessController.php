<?php
namespace Drupal\office\Controller;
use Drupal\office\Entity\Process;
use Drupal\office\x;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProcessController extends ControllerBase {
	public function collection() {
		$entities = Process::loadMultipleExtra();
		return [
			'#theme' => 'process.list',
			'#data' => [ 'processes' => $entities ],
			'#attached' => ['library'=>['office/process']],
		];
	}

	public function view($office_process) {
		$data = [];
		$data['process'] = $office_process;
		return [
			'#theme' => 'process.view',
			'#data' => $data,
			'#attached' => ['library'=>['office/process']],
		];
	}

	public function add() {
		$data = [];
		if ( ! x::login() ) x::messageLoginFirst($data);
		return [
			'#theme' => 'process.edit',
			'#data' => $data,
			'#attached' => ['library'=>['office/process']],
		];
	}
	public function edit(Process $office_process=null) {
		if ( ! x::login() ) return x::loginResponse();

		$data = [];
		if (x::isFromSubmit()) {
			$group_id = Process::formSubmit($data);
			$code = 'process-updated';
			$message = "Process has been updated";
			return new RedirectResponse("/office/process/edit/$group_id?code=$code&message=$message");
		}
		else {
			$office_process_id = $office_process->id();
			$data['process'] = Process::load($office_process_id);
		}


		return [
			'#theme' => 'process.edit',
			'#data' => $data,
			'#attached' => ['library'=>['office/process']],
		];

	}

}