<?php
namespace Drupal\office\Controller;
use Drupal\office\Entity\Client;
use Drupal\office\Entity\Group;
use Drupal\office\x;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ClientController extends ControllerBase {
	public function collection() {
		$eids = \Drupal::entityQuery('office_client')->execute();
		$clients = \Drupal::entityManager()->getStorage('office_client')->loadMultiple($eids);
		return [
			'#theme' => 'client.list',
			'#data' => [ 'clients' => $clients ],
		];
	}
	public function view($client) {
		$data = [];
		$client = Client::load($client);
		$data['client'] = $client;
		$data['user'] = $client->getOwner();
		return [
			'#theme' => 'client.view',
			'#data' => $data,
		];
	}

	/**
	 * Client add/edit
	 *
	 * @return array
	 */
	public function edit() {
		$data = [];
		$data['mode'] = x::g('mode');
		if ( x::login() ) {
			if (x::isFromSubmit()) {
				if ( x::clientFormSubmit($data) ) {
					// error
				}
				else {
				}
			}
			$client = Client::load(x::myUid());
			if ( $client ) {
				$data['client'] = $client->getOwner();
			}
			$data['groups'] = Group::loadMultiple();
			return [
				'#theme' => 'client.edit',
				'#data' => $data,
			];
		}
		else {
			return new RedirectResponse('/office/login');
		}
	}
}