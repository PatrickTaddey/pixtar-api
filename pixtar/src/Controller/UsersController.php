<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;
use App\Controller\AppController;
use Cake\Network\Email\Email;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;

class UsersController extends AppController 
{

	/**
	 * list users - disabled
	 *
	 * @return void
	 */
	public function index() 
	{
		$this->response->statusCode(405);
		echo json_encode(["message" => "Method Not Allowed"]);
	}

	/**
	 * view user - disabled
	 *
	 * @return void
	 */
	public function view() 
	{
		$this->response->statusCode(405);
		echo json_encode(["message" => "Method Not Allowed"]);
	}

	/**
	 * add user
	 *
	 * @return void
	 */
	public function add() 
	{
		/* find user with given password */
		$query = $this->Users->find("all", [
			'conditions' => [
				"username" => $this->request->data["username"],
			],
		]);

		/* check if username/email is already in use */
		if ($query->count() == 0) {

			/* create new user entity */
			$usersTable = TableRegistry::get('Users');
			$user = $usersTable->newEntity();
			$user->username = $this->request->data["username"];
			$user->password = $this->request->data["password"];

			/* create activation key with sha1 */
			$user->activation_key = sha1($user->username . $user->password);
			
			/* proceed if user could be saved */
			if ($usersTable->save($user)) {

				/* send account activation email, return status */
				$this->sendAccountActivationMail($user, $user->activation_key);
				$this->response->statusCode(201);
				echo json_encode(["message" => "resource created"]);

			} else {
				/* could not save data */
				$this->response->statusCode(500);
				echo json_encode(["message" => "Internal Server Error"]);
			}
		} else {
			/* user already exists */
			$this->response->statusCode(409);
			echo json_encode(["message" => "email already exists"]);
		}
	}

	/**
	 * add user
	 *
	 * @return void
	 */
	public function activate() 
	{
		/* find user by activation_key */
		$query = $this->Users->find("all", [
			'conditions' => [
				"activation_key" => $this->request->query['activation_key'],
			],
		]);

		/* return first result */
		$user = $query->first();

		/* check result */
		if ($query->count() == 1) {

			/* check if account is already active */
			if($user->active == 0) {
				$user->active = 1;
				$this->Users->save($user);
				$this->response->statusCode(200);
				echo json_encode(["message" => "resource updated successfully"]);
			} else {
				$this->response->statusCode(409);
				echo json_encode(["message" => "Der Account ist schon aktiviert"]);
			}
		} else {
			$this->response->statusCode(409);
			echo json_encode(["message" => "Der Aktivierungscode ist nicht gültig."]);
		}
	}

	/**
	 * send account activation email
	 * @fixme: link in config auslagern
	 * @param object $user
	 * @param string $activation_key
	 * @return void
	 */
	private function sendAccountActivationMail($user, $activation_key) {

		$email = new Email();
		$email->profile('default')
		      ->to($user->username)
		      ->from('tooltonix@gmail.com')
		      ->subject('Pixtar Account-Aktivierung')
		      ->template('users_optin')
		      ->emailFormat('html')
		      ->viewVars([
			      'link' => Configure::read('activation_url') . $activation_key,
		      ])
		      ->send();
	}
}