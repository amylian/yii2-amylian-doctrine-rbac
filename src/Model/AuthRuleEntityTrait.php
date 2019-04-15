<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Yii\Doctrine\Rbac\Model;

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
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var string Serialized {@see \yii\rbac\Rule object}
     * @Column(type="text")
     */
    protected $data;
    
    /**
     * @var AuthItemEntityInterface[]|\Doctrine\Common\Collections\ArrayCollection
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
    public function getCreatedAt():?\DateTime
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
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt(?\DateTime $value)
    {
        if (isset($value)) {
            $this->createdAt = $value instanceof \DateTime ? $value : new \DateTime(is_numeric($value) ? '@'.$value : $value);
        } else {
            $this->createdAt = null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getRule(): \yii\rbac\Rule
    {
        $result = unserialize((string)$this->data);
        $result = \yii\di\Instance::ensure($result, \yii\rbac\Rule::class);
        $result->name = $this->getName();
        $result->createdAt = $result->createdAt ?? (!empty($this->getCreatedAt())) ? $this->getCreatedAt()->getTimestamp() : null;
        $result->updatedAt = $result->updatedAt ?? (!empty($this->getUpdatedAt())) ? $this->getUpdatedAt()->getTimestamp() : null;
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function setRule(\yii\rbac\Rule $rule)
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
            $this->updatedAt = $value instanceof \DateTime ? $value : new \DateTime(is_numeric($value) ? '@'.$value : $value);
        } else {
            $this->updatedAt = null;
        }
    }
    
    /**
     * Returns all AuthItems using this rule
     * @return AuthItemEntityInterface[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getUsedByAuthItems()
    {
        if (!isset($this->usedByAuthItems)) {
            $this->usedByAuthItems = new \Doctrine\Common\Collections\ArrayCollection();
        }
        return $this->usedByAuthItems;
    }

}
