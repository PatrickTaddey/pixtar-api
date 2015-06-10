<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Image Entity.
 */
class Image extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'users_id' => true,
        'name' => true,
        'description' => true,
        'mime_type' => true,
        'user' => true,
    ];
}
