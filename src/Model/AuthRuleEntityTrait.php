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
use Doctrine\ORM\Mapping\OneToMany;
use yii\di\Instance;
use yii\grid\Column;
use yii\rbac\Rule;

/**
 * Standard Implementation for AuthRuleInterface
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 * 
 */
trait AuthRuleEntityTrait
{

    /**
     * @var string 
     * @Id;
     * @Column(type="string", length=64, unique=true, nullable=false)
     */
    protected $name;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @var DateTime
     */
    protected $updatedAt;

    /**
     * @var string Serialized {@see \yii\rbac\Rule object}
     * @Column(type="text")
     */
    protected $data;
    
    /**
     * @var AuthItemEntityInterface[]|ArrayCollection
     * @OneToMany (targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface", mappedBy="rule", fetch="EXTRA_LAZY")
     */
    protected $usedByAuthItems = null;

    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt():?DateTime
    {
        return $this->createdAt;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($value)
    {
        if (isset($value)) {
            $this->createdAt = $value instanceof DateTime ? $value : new DateTime(is_numeric($value) ? '@'.$value : $value);
        } else {
            $this->createdAt = null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getRule(): Rule
    {
        $result = unserialize((string)$this->data);
        $result = Instance::ensure($result, Rule::class);
        $result->name = $this->getName();
        $result->createdAt = $result->createdAt ?? (!empty($this->getCreatedAt())) ? $this->getCreatedAt()->getTimestamp() : null;
        $result->updatedAt = $result->updatedAt ?? (!empty($this->getUpdatedAt())) ? $this->getUpdatedAt()->getTimestamp() : null;
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function setRule(Rule $rule)
    {
        $this->data = serialize($rule);
        $this->setName($rule->name);
        $this->setCreatedAt($rule->createdAt);
        $this->setUpdatedAt($rule->updatedAt);
    }

    /**
     * {@inheritDoc}
     */
    public function setName($value)
    {
        $this->name = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedAt($value)
    {
        if (isset($value)) {
            $this->updatedAt = $value instanceof DateTime ? $value : new DateTime(is_numeric($value) ? '@'.$value : $value);
        } else {
            $this->updatedAt = null;
        }
    }
    
    /**
     * Returns all AuthItems using this rule
     * @return AuthItemEntityInterface[]|ArrayCollection
     */
    public function getUsedByAuthItems()
    {
        if (!isset($this->usedByAuthItems)) {
            $this->usedByAuthItems = new ArrayCollection();
        }
        return $this->usedByAuthItems;
    }

}
