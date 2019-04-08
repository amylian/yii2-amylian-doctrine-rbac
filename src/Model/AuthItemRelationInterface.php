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
interface AuthItemRelationInterface
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
    public function getParent(): AuthItemEntityInterface;

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
    public function getChild(): AuthItemEntityInterface;
    
}
