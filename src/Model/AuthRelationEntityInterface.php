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
 * Interface of AuthRelation Entity
 * 
 * This entity reflects the relationship between {@see AuthItemEntityInterface} entities.
 * 
 * See {@see AuthRelationEntityTrait}
 * 
 * See {@see AuthItemEntityInterface} for details about Auth-Items.
 *  
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
interface AuthRelationEntityInterface
{

    /**
     * Sets the parent item
     * 
     * Sets the parent item and adds itself to the collection of child auth items.
     * If $value is null, it removes itself from the previously assigned item
     * 
     * @param \Amylian\Yii\Doctrine\Rbac\AuthItemInterface $authItem
     */
    public function setParent(?AuthItemEntityInterface $authItem);

    /**
     * Returns the parent item
     * @return \Amylian\Yii\Doctrine\Rbac\AuthItemInterface
     */
    public function getParent(): ?AuthItemEntityInterface;

    /**
     * Sets the child item
     * 
     * Sets the child item and adds itself to the collection of parent auth items.
     * If $value is null, it removes itself from the previously assigned item
     * 
     * @param \Amylian\Yii\Doctrine\Rbac\AuthItemInterface $authItem
     */
    public function setChild(?AuthItemEntityInterface $authItem);

    /**
     * Returns the child item
     * @return \Amylian\Yii\Doctrine\Rbac\AuthItemChildInterface
     */
    public function getChild(): ?AuthItemEntityInterface;
    
}
