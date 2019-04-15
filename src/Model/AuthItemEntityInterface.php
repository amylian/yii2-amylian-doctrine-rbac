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
 * Abstract interface for Doctrine RBAC
 * 
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
interface AuthItemEntityInterface
{

    const TYPE_PERMISSION = \yii\rbac\Item::TYPE_PERMISSION;
    const TYPE_ROLE = \yii\rbac\Item::TYPE_ROLE;
    
    /**
     * Returns the RBAC-Item (@see \yii\rbac\Item) represented by this entity object
     * @return \yii\rbac\Item
     */
    public function getItem(): \yii\rbac\Item;
    
    /**
     * Assigns the item to to the entity
     * 
     * *Attention*: if <code>$rule->ruleName</code> is set, the matching {@see AuthRuleEntityInterface} object
     * *must* be be passed, otherwise an exception will be thrown
     * 
     * 
     * @param \yii\rbac\Item $item Item to assign
     * @param AuthRuleEntityInterface Used Rule. Must match <code>$item->ruleName</code>. 
     * @throws \yii\base\InvalidArgumentException if <code>$item->ruleName</code> is set, but no 
     * <code>$authRuleEntity</code> is passed or if <code>$item->ruleName</code> and <code>$authRuleEntity</code>
     */
    public function setItem(\yii\rbac\Item $item, ?AuthRuleEntityInterface $authRuleEntity = null);
    
    /**
     * Returns the Auth item name
     */
    public function getName();

    /**
     * Sets the Auth item name
     * 
     * A hierarchical naming is encouraged, e.g. "customer.create"
     * 
     * @param string Name of Auth item
     */
    public function setName($value);

    /**
     * Returns the type of the item
     */
    public function getType();

    /**
     * Sets the type of the item ({@see \yii\rbac\Item::TYPE_ROLE} or {@see \yii\rbac\Item::TYPE_PERMISSION})
     */
    public function setType($value);

    /**
     * Returns the description of the item
     */
    public function getDescription();

    /**
     * Sets the description of the item
     */
    public function setDescription($value);

    /**
     * Sets the assigned rule entity
     */
    public function setRule(?AuthRuleEntityInterface $value);

    /**
     * Returns the assigned rule entity
     */
    public function getRule(): ?AuthRuleEntityInterface;

    /**
     * Returns the rule name
     */
    public function getRuleName();

    /**
     * Sets the rule name
     */
    public function getData();

    /**
     * Sets additional rule data
     */
    public function setData($value);

    /**
     * Sets the creation date
     */
    public function setCreatedAt($value);

    /**
     * Returns the creation date
     */
    public function getCreatedAt();

    /**
     * Sets the modification date
     */
    public function setUpdatedAt($value);

    /**
     * Returns the modification date
     */
    public function getUpdatedAt();

    /**
     * Returns the child auth items
     * @return AuthItemEntityInterface|array Array of direct child items indexed by name
     */
    public function getChildAuthItems();

    /**
     * Returns the parents of this auth item
     * @return AuthItemEntityInterface|array  Array of direct parent items indexed by name
     */
    public function getParentAuthItems();

    /**
     * Returns an Array (Collection) of {@see AuthItemRelationInterface} entities
     * 
     * **NOTE**: You SHOULD NOT modify this colleciton directly. 
     * Use {@see addChildAuthItem()} or {@see removeChildAuthItem()} instead
     * 
     * @return AuthRelationEntityInterface|array
     */
    public function getChildAuthItemRelations();

    /**
     * Returns an Array (Collection) of {@see AuthItemRelationInterface} entities
     * 
     * **NOTE**: You SHOULD NOT modify this colleciton directly. 
     * Use {@see addParentAuthItem()} or {@see removeParentAuthItem()} instead.
     * 
     * @return AuthRelationEntityInterface|array
     */
    public function getParentAuthItemRelations();

    /**
     * Adds the relation entity to {@see $childAuthItems}
     * 
     * This method adds the object to the collection if necessary without 
     * performing any additional checks and reverse assignments 
     * 
     * NOTE: Usually it's not necessary to use this method directly - use {@see addChildAuthItem()}
     * instead as it takes care of reverse assignments
     */
    public function addChildAuthItemRelation(AuthRelationEntityInterface $relation);

    /**
     * Adds the relation entity to {@see $parentAuthItems}
     * 
     * This method adds the object to the collection if necessary without 
     * performing any additional checks and reverse assignments 
     * 
     * NOTE: Usually it's not necessary to use this method directly - use {@see addParentAuthItem()}
     * instead as it takes care of reverse assignments
     */
    public function addParentAuthItemRelation(AuthRelationEntityInterface $relation);

    /**
     * Returns all Assignments
     * 
     * @return AuthAssignmentEntityInterface[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getAuthAssignments();
    
    /**
     * Adds a Child AuthItem Entity
     * 
     * This funciton creates a new entity of {@see AuthItemRelationInterface}
     * and adds it to {@see childAuthItemRelations} 
     * 
     * @param \Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface $child
     * @return AuthRelationEntityInterface
     */
    public function addChildAuthItem(AuthItemEntityInterface $child);

    /**
     * Removes a Child Auth Item
     * 
     * This funciton removes the given entity of {@see AuthItemRelationInterface}
     * from childAuthItemRelations} 
     * 
     * @param \Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface $child
     */
    public function removeChildAuthItem(AuthItemEntityInterface $child);

    /**
     * Adds a Parent AuthItem Entity
     * 
     * This funciton creates a new entity of {@see AuthItemRelationInterface}
     * and adds it to {@see parentAuthItemRelations} 
     * 
     * @param AuthItemEntityInterface $child
     * @return AuthRelationEntityInterface
     */
    public function addParentAuthItem(AuthItemEntityInterface $child);

    /**
     * Removes a Child Auth Item
     * 
     * This funciton removes the given entity of {@see AuthItemRelationInterface}
     * from childAuthItemRelations} 
     * 
     * @param AuthItemEntityInterface $child
     */
    public function removeParentAuthItem(AuthItemEntityInterface $parent);

    /**
     * Removes the relation entity from the child list
     * 
     * Removes the item if it exists and sets parent in it to null
     * 
     * @param AuthRelationEntityInterface $relation
     */
    public function removeChildAuthItemRelation(AuthRelationEntityInterface $relation);

    /**
     * Removes the relation entity from the parent list
     * 
     * Removes the item if it exists and sets child in it to null
     * 
     * @param AuthRelationEntityInterface $relation
     */
    public function removeParentAuthItemRelation(AuthRelationEntityInterface $relation);
}
