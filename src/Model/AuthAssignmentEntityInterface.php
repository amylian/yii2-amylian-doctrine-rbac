<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Yii\Doctrine\Rbac\Model;

/**
 * Base interface for User->Role Assignment entities.
 * 
 * NOTE: You should not use this interface directly. Rather use
 * {@see AuthAssignmentByIdEntityInterface} if you do not maintain a relation
 * to a user record or {@see AuthAssignmentByAuthIdentityEntityInterface}.

 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
interface AuthAssignmentEntityInterface
{
    /**
     * Returns the Identity Object representing the user
     * 
     * IMPLEMENTATION NOTE: If implementing an assignment to a relation this method should return
     * the related entity. If no relation is used, this function should return the id of the user
     * 
     * @return mixed
     */
    public function getUser();
    /**
     * Sets the Identity Object representing the user
     * @param mixed $value
     */
    public function setUser($value);
    public function getAuthItem(): ?AuthItemEntityInterface;
    /**
     * Assigns the role ({@see AuthItemEntityInterface}} item
     * NOTE: Calling this function also assigns the reverse side.
     * If this object is removed and/or added from/to Collection {@see AuthItemEntityInterface::getAuthAssignments()}.
     * @param \Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface|null $role
     */
    public function setAuthItem(AuthItemEntityInterface $value);
    /**
     * Returns the ID of the related user
     * @return mixed ID of user
     */
    public function getUserId();
    public function getCreatedAt(): \DateTime;
    public function setCreatedAt($value);
    /**
     * Returns the Yii {@see \yii\rbac\Assignment} object
     * @return \yii\rbac\Assignment
     */
    public function getAssignment(): \yii\rbac\Assignment;
}

