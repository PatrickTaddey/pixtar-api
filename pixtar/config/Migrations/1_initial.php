<?php
use Phinx\Migration\AbstractMigration;

class Initial extends AbstractMigration {
	public function up() {
		$table = $this->table('images');
		$table
			->addColumn('users_id', 'integer', [
				'default' => 0,
				'limit' => 11,
				'null' => false,
			])
			->addColumn('name', 'string', [
				'default' => null,
				'limit' => 40,
				'null' => false,
			])
			->addColumn('description', 'string', [
				'default' => null,
				'limit' => 255,
				'null' => false,
			])
			->addColumn('mime_type', 'string', [
				'default' => null,
				'limit' => 20,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'null' => false,
			])
			->addColumn('modified', 'datetime', [
				'default' => null,
				'null' => true,
			])
			->create();

		$table = $this->table('users');
		$table
			->addColumn('username', 'string', [
				'default' => null,
				'limit' => 255,
				'null' => false,
			])
			->addColumn('password', 'string', [
				'default' => null,
				'limit' => 255,
				'null' => false,
			])
			->addColumn('active', 'integer', [
				'default' => 0,
				'limit' => 1,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'null' => false,
			])
			->addColumn('modified', 'datetime', [
				'default' => null,
				'null' => true,
			])
			->addColumn('activation_key', 'string', [
				'default' => null,
				'limit' => 40,
				'null' => false,
			])
			->create();
	}
	public function down() {
		$this->dropTable('images');
	}
}