<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Yii\Doctrine\Rbac\Model;

/**
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
trait AuthAssignmentEntityWithIdTrait
{
    use AuthAssignmentEntityTrait;
    
    public function getUserId()
    {
        return $this->userId;
    }
    
    public function setUserId($id)
    {
        return $this->userId;
    }
}
