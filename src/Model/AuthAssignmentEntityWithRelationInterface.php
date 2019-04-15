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
interface AuthAssignmentEntityWithRelationInterface extends AuthAssignmentEntityInterface
{
    /**
     * Returns the Identity Object representing the user
     * @return AuthIdentityEntityInterface
     */
    public function getUser();
    /**
     * Sets the Identity Object representing the user
     * @param AuthIdentityEntityInterface $value
     */
    public function setUser($value);
}
