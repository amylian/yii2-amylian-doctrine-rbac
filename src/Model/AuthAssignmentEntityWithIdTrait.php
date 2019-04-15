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
    
    /**
     * Sets the user by calling
     * 
     * NOTE: This is equivalent to {@see setUserId()}
     * 
     * @param mixed $value
     */
    public function setUser($userId)
    {
        $this->setUserId($userId);
    }
    
    /**
     * Returns the User ID by calling
     * 
     * NOTE: This is equivalent to {@see getUserId()}
     * 
     * @return mixed
     */
    public function getUser()
    {
        return $this->getUserId();
    }
    
    /**
     * Returns the UserId
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }
    
    /**
     * Sets the user ID
     * @param mixed $id
     */
    public function setUserId($id)
    {
        $this->userId = (string)$id;
    }
}
