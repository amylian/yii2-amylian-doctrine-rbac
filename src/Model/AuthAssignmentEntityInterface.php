<?php

/*
 * BSD 3-Clause License
 * 
 * Copyright (c) 2019, Andreas Prucha (Abexto - Helicon Software Development) 
 * Amylian Project
 *  
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * 
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * 
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
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

