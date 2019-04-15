<?php

use Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use yii\rbac\Rule;
use DateTime

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Yii\Doctrine\Rbac\Model;

/**
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 * @property $name; 
 */
interface AuthRuleEntityInterface
{

    /**
     * Returns the name of the rule
     */
    public function getName();

    /**
     * Sets the name of the rule
     * @param string $value 
     */
    public function setName($value);

    /**
     * Returns the Creation Date
     * @return DateTime
     */
    public function getCreatedAt(): ?DateTime;

    /**
     * Returns the Rule Instance
     * Creates the rule instance if necessary and returns it
     * @return Rule
     */
    public function getRule(): Rule;

    /**
     * Assigns the rule.
     * This function calls {@see setRuleClassName()} using the type of the given rule
     * @param Rule
     */
    public function setRule(Rule $rule);

    /**
     * Sets the creation time
     * @param int|DateTime $value
     */
    public function setCreatedAt($value);

    /**
     * Returns the modification time
     */
    public function getUpdatedAt(): ?DateTime;

    /**
     * Sets the modification time
     * @param int|DateTime $value
     */
    public function setUpdatedAt($value);
    /**
     * Returns all AuthItems using this rule
     * @return AuthItemEntityInterface[]|ArrayCollection
     */
    public function getUsedByAuthItems();
}
