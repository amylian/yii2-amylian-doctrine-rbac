<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
    public function setRule(?AuthRuleInterface $value);

    /**
     * Returns the assigned rule entity
     */
    public function getRule(): ?AuthRuleInterface;

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
     * @return AuthItemEntityInterface|array
     */
    public function getChildAuthItems();

    /**
     * Returns the parents of this auth item
     * @return AuthItemEntityInterface|array
     */
    public function getParentAuthItems();

    /**
     * Returns an Array (Collection) of {@see AuthItemRelationInterface} entities
     * 
     * **NOTE**: You SHOULD NOT modify this colleciton directly. 
     * Use {@see addChildAuthItem()} or {@see removeChildAuthItem()} instead
     * 
     * @return AuthItemRelationInterface|array
     */
    public function getChildAuthItemRelations();

    /**
     * Returns an Array (Collection) of {@see AuthItemRelationInterface} entities
     * 
     * **NOTE**: You SHOULD NOT modify this colleciton directly. 
     * Use {@see addParentAuthItem()} or {@see removeParentAuthItem()} instead.
     * 
     * @return AuthItemRelationInterface|array
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
    public function addChildAuthItemRelation(AuthItemRelationInterface $relation);

    /**
     * Adds the relation entity to {@see $parentAuthItems}
     * 
     * This method adds the object to the collection if necessary without 
     * performing any additional checks and reverse assignments 
     * 
     * NOTE: Usually it's not necessary to use this method directly - use {@see addParentAuthItem()}
     * instead as it takes care of reverse assignments
     */
    public function addParentAuthItemRelation(AuthItemRelationInterface $relation);

    /**
     * Adds a Child AuthItem Entity
     * 
     * This funciton creates a new entity of {@see AuthItemRelationInterface}
     * and adds it to {@see childAuthItemRelations} 
     * 
     * @param \Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface $child
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
     * @param AuthItemRelationInterface $relation
     */
    public function removeChildAuthItemRelation(AuthItemRelationInterface $relation);

    /**
     * Removes the relation entity from the parent list
     * 
     * Removes the item if it exists and sets child in it to null
     * 
     * @param AuthItemRelationInterface $relation
     */
    public function removeParentAuthItemRelation(AuthItemRelationInterface $relation);
}
