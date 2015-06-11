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

class SessionsController extends AppController 
{
	/**
	 * session controller uses any model
	 *
	 * @var array
	 */
	public $uses = array();
	public $autoRender = false;

	/**
	 * disabled
	 *
	 * @return void
	 */
	public function index() 
	{
		$this->response->statusCode(405);
		echo json_encode(["message" => "Method Not Allowed"]);
	}

	/**
	 * add session
	 *
	 * @return void
	 */
	public function add() 
	{
		if ($this->request->is('post')) {

			/* check login, identify user */
			$user = $this->Auth->identify();

			/* check if user exists */
			if ($user) {
				/* check if user is active */
				if ($user["active"] == 1) {

					/* set user to auth object */
					$this->Auth->setUser($user);

					/* return csrftoken, username & id */
					$this->response->statusCode(201);
					echo json_encode([
						"message" => "resource created",
						"data" => [
							"username" => $user["username"],
							"session_id" => session_id(),
							"session_name" => session_name()
						],
					]);

				} else {
					/* error: user inactive */
					header("HTTP/1.1 405");
					echo json_encode(["message" => "user inactive"]);
				}
			} else {
				/* error: login incorrect */
				header("HTTP/1.1 401");
				echo json_encode(["message" => "login incorrect"]);
			}
		}
	}

	/**
	 * delete session
	 * @todo
	 * @return void
	 */
	private function delete() {
		$this->autoRender = false;

		$this->Auth->logout();
		$this->response->statusCode(200);
		echo json_encode(["message" => "Der Benutzer wurde abgemeldet."]);
	}
}