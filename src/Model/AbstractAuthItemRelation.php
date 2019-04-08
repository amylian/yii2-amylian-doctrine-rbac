<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Yii\Doctrine\Rbac\Model;

/**
 * Description of AbstractAuthRelation
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 * 
 * @MappedSuperclass
 */
abstract class AbstractAuthItemRelation extends \yii\base\Model implements AuthItemRelationInterface
{

    /**
     * @var AuthItemEntityInterface
     * @Id
     * @ManyToOne(targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface", fetch="LAZY", inversedBy="parentAuthItemRelations")
     * @JoinColumn(name = "child_auth_item_name", referencedColumnName="name", onDelete="CASCADE")
     */
    protected $child = null;

    /**
     * @var AuthItemEntityInterface 
     * @Id
     * @ManyToOne(targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface", fetch="LAZY", inversedBy="childAuthItemRelations")
     * @JoinColumn(name = "parent_auth_item_name", referencedColumnName="name", onDelete="CASCADE")
     */
    protected $parent = null;

    public function getChild(): AuthItemEntityInterface
    {
        return $this->child;
    }

    public function getParent(): AuthItemEntityInterface
    {
        return $this->parent;
    }

    public function setChild(?AuthItemEntityInterface $authItem)
    {
        $oldAuthItem = $this->child;
        if ($authItem !== $this->child) {
            $this->child = $authItem;
            if ($authItem === null) {
                if ($oldAuthItem !== null) {
                    $oldAuthItem->removeParentAuthItemRelation($this);
                }
            } else {
                $authItem->addParentAuthItemRelation($this);
            }
        }
    }

    public function setParent(?AuthItemEntityInterface $authItem)
    {
        $oldAuthItem = $this->parent;
        if ($authItem !== $this->parent) {
            $this->parent = $authItem;
            if ($authItem === null) {
                if ($oldAuthItem !== null) {
                    $oldAuthItem->removeChildAuthItemRelation($this);
                }
            } else {
                $authItem->addChildAuthItemRelation($this);
            }
        }
    }

}
