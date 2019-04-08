<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Tests\Yii\Doctrine\Rbac\Misc;

/**
 * Description of AuthAssignment
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 * 
 * @Entity
 */
class AuthAssignment extends \yii\base\Model implements \Amylian\Yii\Doctrine\Rbac\Model\AuthAssignmentEntityWithIdInterface
{
    use \Amylian\Yii\Doctrine\Rbac\Model\AuthAssignmentEntityWithIdTrait;
    
    /**
     * @var int
     * @Id
     * @Column(type="integer", unique=false, nullable=false)
     */
    protected $userId = null;
}
