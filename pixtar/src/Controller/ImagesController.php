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
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;

class ImagesController extends AppController 
{
	public $uses = ["Users"];
	public $paginate = [
        'limit' => 5,
        'page' =>1,
        'contain' => ['Users'],
		'sort' => 'id',
		'direction' => 'desc',
		'fields' => ['id', 'name', 'description', 'Users.username'],
    ];

	/**
	 * overwrite beforeFilter to allow index & view action
	 *
	 * @param Event $event
	 * @return void
	 */
	public function beforeFilter(Event $event) 
	{
		$this->Auth->allow(["view", "index"]);
		parent::beforeFilter($event);
	}

	/**
	 * list images & enable filtering
	 *
	 * @param Event $event
	 * @return void
	 */
	public function index() 
	{
		$this->Crud->on('beforePaginate', function(\Cake\Event\Event $event) {
			if(isset($this->request->query['filter']) === true) {
				$this->paginate['conditions']['description'] = $this->request->query['filter'];
			}
		});		

		return $this->Crud->execute();	
	}

	/**
	 * view image
	 *
	 * @return void
	 */
	public function view($id) 
	{
		header("Content-type: image/jpeg");
		$file_content = file_get_contents(WWW_ROOT . 'upload' . DS . $id);
		$image = str_replace('data:image/jpeg;base64,', '', $file_content);
		echo base64_decode($image);
		exit;
	}

	/**
	 * add image
	 *
	 * @return void
	 */
	public function add() 
	{
		/* create new image entity */
		$imagesTable = TableRegistry::get('Images');
		$image = $imagesTable->newEntity();
		$image->name = $this->request->data["name"];
		$image->description = $this->request->data["description"];
		$image->mime_type = $this->request->data["mime_type"];
		$image->users_id = $this->Auth->user('id');

		/* transaction: begin */
		$connection = ConnectionManager::get('default');
		$connection->begin();

		/* proceed if image could be saved */
		if ($imagesTable->save($image)) {

			/* create images path */
			$file = WWW_ROOT . 'upload' . DS . $image->id;

			/* proceed if file could be created */
			if (file_put_contents($file, $this->request->data["file_content"]) !== false) {

				/* transaction: commit */
				$connection->commit();

				/* return status */
				$this->response->statusCode(201);
				echo json_encode(["message" => $file]);

			} else {

				/* transaction: rollback - image could not be saved */
				$connection->rollback();

				$this->response->statusCode(500);
				echo json_encode(["message" => "Internal Server Error"]);
			}

		} else {

			/* transaction: rollback - could not save data */
			$connection->rollback();
			$this->response->statusCode(500);
			echo json_encode(["message" => "Internal Server Error"]);
		}
	}
}