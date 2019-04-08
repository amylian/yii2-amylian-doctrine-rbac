<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Yii\Doctrine\Rbac\Model;

/**
 * Abstract Entity wrapping a rule
 * 
 * Contrary to Yii2 standard approach, this implementation does *not* serialize the
 * object drived from \yii\rbac\Rule, but just storeds it's class name.
 * 
 * The actual rule can be accessed via {@see getRule()}
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 * 
 * @MappedSuperclass
 */
class AbstractAuthRule extends \yii\base\BaseObject implements AuthRuleInterface
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
     * @Column(type="binary", nullable=false)
     */
    protected $data;

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
        if ($this->data) {
            $result = unserialize($this->data);
        } else {
            $result = null;
        }
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

}
