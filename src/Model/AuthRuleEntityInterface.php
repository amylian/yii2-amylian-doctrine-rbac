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

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use yii\rbac\Rule;

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
