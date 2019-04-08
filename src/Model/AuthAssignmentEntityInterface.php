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
    public function getAuthItem(): ?AuthItemEntityInterface;
    public function setAuthItem(AuthItemEntityInterface $value);
    public function getUserId();
    public function getCreatedAt(): \DateTime;
    public function setCreatedAt($value);
    public function getAssignment(): \yii\rbac\Assignment;
}

