<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
     * @OneToMany (targetEntity="AuthItemEntityInterface", mappedBy="rule", fetch="EXTRA_LAZY")
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
