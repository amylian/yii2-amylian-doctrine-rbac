<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Yii\Doctrine\Rbac;

use yii\di\Instance;
use Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface;
use Amylian\Yii\Doctrine\Rbac\Model\AuthRuleInterface;
use Amylian\Yii\Doctrine\Rbac\Model\AuthItemRelationInterface;

/**
 * Description of Manager
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
class Manager extends \yii\rbac\BaseManager
{

    /**
     * @var \Doctrine\ORM\EntityManager|string Instance of entity manger
     * or string Identifying the entity manager 
     */
    public $em = \Doctrine\ORM\EntityManager::class;

    /**
     * @var bool Specifies if flush() is called on the EntityManager
     * when changes were made. 
     */
    public $autoFlush = true;

    /**
     * @var int Current work nesting level. incremented by {@see beginWork()}, decremented by {@see endWork()}
     */
    protected $workLevel = 0;

    /**
     * @var string|null Class implementing the entity {@see Model\AuthItemInterface}
     */
    public $authItemEntityClass = Model\AuthItemEntityInterface::class;

    /**
     * @var string|null Class implementing the entity {@see Model\AuthRuleInterface} (Auto-detected if not specified)
     */
    public $authRuleEntityClass = Model\AuthRuleInterface::class;

    /**
     * @var string|null Class implementing the entity {@see Model\AuthRuleInterface} (Auto-detected if not specified)
     */
    public $authItemRelationClass = Model\AutoItemRelationInterface::class;
    
    /**
     * @var string|null Class implementing the entity {@see Model\BaseAuthAssignmentEntityInterface} (Auto-detected if not specified)
     */
    public $authAssignmentEntityClass = Model\AuthAssignmentEntityInterface::class;

    public function init()
    {
        parent::init();

        if (is_string($this->em)) {
            $this->em = Instance::of($this->em)->get();
        }

        $this->authItemEntityClass = $this->em->getClassMetadata($this->authItemEntityClass)->getName();
        $this->authRuleEntityClass = $this->em->getClassMetadata(AuthRuleInterface::class)->getName();
        $this->authItemRelationClass = $this->em->getClassMetadata(AuthRuleInterface::class)->getName();
        $this->authAssignmentEntityClass = $this->em->getClassMetadata(Model\AuthAssignmentEntityInterface::class)->getName();
    }

    protected function beginWork()
    {
        
    }

    protected function endWork()
    {
        $this->em->flush();
    }

    /**
     * Finds and AuthItemEntity by name
     * @param string $name
     * @param bool $load Loads the entity if <code>true</code> - otherwise a reference is created
     * @return AuthRuleInterface
     */
    protected function findAuthRuleEntity($name, $load = true): ?AuthRuleInterface
    {
        if ($load) {
            return $this->em->getRepository($this->authRuleEntityClass)->find($name);
        } else {
            return $this->em->getReference($this->authRuleEntityClass, $name);
        }
    }
    
    /**
     * Assigns the Rule Object to the Rule Entity
     * @param type $rule
     * @param type $ruleEntity
     */
    protected function assignRuleDataToEntity(\yii\rbac\Rule $rule, AuthRuleInterface $ruleEntity) 
    {
        $ruleEntity->setRule($rule);
    }
    
    /**
     * Returns a new AuthRuleEntity
     * @return AuthRuleInterface
     */
    protected function newAuthRuleEntity(): AuthRuleInterface
    {
        return new $this->authRuleEntityClass ();
    }
    
    /**
     * Finds or creates entity object and updates attributes
     * @param \yii\rbac\Rule $rule
     */
    protected function findOrCreateAuthRuleEntityFromItem(\yii\rbac\Rule $rule): Model\AuthRuleInterface
    {
        $ruleEntity = $this->findAuthRuleEntity($rule->name);
        if (!$ruleEntity) {
            $ruleEntity = $this->newAuthRuleEntity(); /* @var $ruleEntity Model\AuthItemEntityInterface */
        }
        $this->assignRuleDataToEntity($rule, $ruleEntity);
        return $ruleEntity;
    }
    

    /**
     * Finds and AuthItemEntity by name
     * @param string $name
     * @param bool $load Loads the entity if <code>true</code> - otherwise a reference is created
     * @return AuthItemEntityInterface
     */
    protected function findAuthItemEntity($name, $load = true): ?AuthItemEntityInterface
    {
        if ($load) {
            return $this->em->getRepository($this->authItemEntityClass)->find($name);
        } else {
            return $this->em->getReference($this->authItemEntityClass, $name);
        }
    }
    
    /**
     * Assigns values from a {@link \yii\rbac\Item} object to the entity.
     * 
     * @param \yii\rbac\Item $item
     * @param \Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface $entity
     */
    protected function assignItemDataToEntity(\yii\rbac\Item $item, \Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface $itemEntity)
    {
        $ruleEntity = $item->ruleName ? $this->getRuleEntity($item->ruleName) : null;
        $itemEntity->setType($item->type);
        $itemEntity->setName($item->name);
        $itemEntity->setDescription($item->description);
        $itemEntity->setData($item->data);
        $itemEntity->setCreatedAt($item->createdAt);
        $itemEntity->setUpdatedAt($item->updatedAt);
        $itemEntity->setRule($ruleEntity);
    }
    
    /**
     * Creates a new AuthItem Entity
     * @return AuthItemEntityInterface
     */
    protected function newAuthItemEntity(): AuthItemEntityInterface
    {
        return new $this->authItemEntityClass ();
    }

    /**
     * Finds or creates entity object and updates attributes
     * @param \yii\rbac\Item $item
     */
    protected function findOrCreateAuthItemEntityFromItem(\yii\rbac\Item $item): Model\AuthItemEntityInterface
    {
        $itemEntity = $this->findAuthItemEntity($item->name);
        if (!$itemEntity) {
            $itemEntity = $this->newAuthItemEntity(); /* @var $itemEntity Model\AuthItemEntityInterface */
        }
        $this->assignItemDataToEntity($item, $itemEntity);
        return $itemEntity;
    }
    
    /**
     * Creates a new AuthAssignment Entity
     * @return AuthItemEntityInterface
     */
    protected function newAuthAssignmentEntity(): Model\AuthAssignmentEntityInterface
    {
        return new $this->authAssignmentEntityClass ();
    }

    /**
     * {@inheritDoc}
     */
    protected function addItem($item)
    {
        $this->beginWork();
        $this->em->persist($this->findOrCreateAuthItemEntityFromItem($item));
        $this->endWork();
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function addRule($rule)
    {
        $this->beginWork();
        $this->em->persist($this->findOrCreateAuthRuleEntityFromItem($rule));
        $this->endWork();
        return true;
    }

    /**
     * {@inheritDoc}
     * @param type $name
     * @return \yii\rbac\Item
     */
    protected function getItem($name): \yii\rbac\Item
    {
        $this->em->getRepository(AuthItemEntityInterface::class)->find($name);
    }

    protected function getItems($type): \yii\rbac\Item
    {
        return $this->em->getRepository(AuthItemEntityInterface::class)->findByType($type);
    }

    /**
     * {@inheritDoc}
     */
    protected function removeItem($item): bool
    {
        $this->beginWork();
        $this->em->remove($item);
        $this->endWork();
    }

    /**
     * @param type $rule
     * @return bool
     */
    protected function removeRule($rule): bool
    {
        if ($rule instanceof \yii\rbac\Rule) {
            $ruleEntity = $this->em->getRepository(AuthRuleInterface::class)->find($rule->name);
        } else {
            $ruleEntity = $rule;
        }
        if (isset($ruleEntity)) {
            $this->beginWork();
            $this->em->remove($ruleEntity);
            $this->endWork();
        }
    }

    protected function updateItem($name, $item): bool
    {
        if ($name !== $item->getName()) {
            throw \Exception('Auth Items cannot be renamed');
        }
        $this->beginWork();
        $mergedItem = $this->em->merge($item);
        $this->em->persist($mergedItem);
        $this->endWork();
    }

    protected function updateRule($name, $rule): bool
    {
        if ($name !== $rule->name()) {
            throw \Exception('Auth Rule cannot be renamed');
        }
        $ruleEntity = $this->getRule($name);
        if ($ruleEntity) {
            
        }
        return isset($ruleEntity) ? $ruleEntity->getRule() : null;
    }

    public function addChild($parent, $child): bool
    {
        $result = false;
        $parentItemEntity = $this->findAuthItemEntity($parent->name);
        $childItemEntity = $this->findAuthItemEntity($child->name);
        if ($parentItemEntity && $childItemEntity) {
            $parentItemEntity->addChildAuthItem($childItemEntity);
            $this->beginWork();
            $this->em->persist($parentItemEntity);
            $this->endWork();
        }
        return $result;
    }

    /**
     * Assigns 
     * @param type $role
     * @param type $userId
     * @return \yii\rbac\Assignment
     */
    public function assign($role, $userId): \yii\rbac\Assignment
    {
        $roleEntity = $this->findAuthItemEntity($role);
        $assignmentEntity = $this->newAuthAssignmentEntity();
        $assignmentEntity->setUserId($userId);
        $this->beginWork();
        $this->em->persist($assignmentEntity);
        $this->endWork();
        return true;
    }

    public function canAddChild($parent, $child): bool
    {
        
    }

    public function checkAccess($userId, $permissionName, $params = array())
    {
        
    }

    public function getAssignment($roleName, $userId)
    {
        
    }

    public function getAssignments($userId): \yii\rbac\Assignment
    {
        
    }

    public function getChildRoles($roleName)
    {
        
    }

    public function getChildren($name)
    {
        $result = [];
        $item = $this->findAuthItemEntity($name, true);
        if ($item) {
            foreach ($item->getChildAuthItems() as $childAuthItemEntity) {
                $result[] = $childAuthItemEntity->getItem();
            }
        }
        return $result;
    }

    public function getPermissionsByRole($roleName)
    {
        
    }

    public function getPermissionsByUser($userId)
    {
        
    }

    public function getRolesByUser($userId)
    {
        
    }

    /**
     * Returns the Entity object of the rule
     * 
     * 
     * @param bool $load If true, the entity is loaded, otherwise just a reference is created
     * @return AuthRuleInterface
     */
    public function getRuleEntity($name, $load = true): AuthRuleInterface
    {
        if ($load)
            return $this->em->getRepository(AuthRuleInterface::class)->find($name);
        else
            return $this->em->getReference(Model\AuthRuleInterface::class, $name);
    }

    /**
     * @param \yii\rbac\Rule $name
     */
    public function getRule($name)
    {
        $ruleEntity = $this->getRuleEntity($name);
        return isset($ruleEntity) ? $ruleEntity->getRule() : null;
    }

    public function getRules(): \yii\rbac\Rule
    {
        
    }

    public function getUserIdsByRole($roleName): array
    {
        
    }

    public function hasChild($parent, $child): bool
    {
        
    }

    public function removeAll()
    {
        
    }

    public function removeAllAssignments()
    {
        
    }

    public function removeAllPermissions()
    {
        
    }

    public function removeAllRoles()
    {
        
    }

    public function removeAllRules()
    {
        
    }

    public function removeChild($parent, $child): bool
    {
        
    }

    public function removeChildren($parent): bool
    {
        
    }

    public function revoke($role, $userId): bool
    {
        
    }

    public function revokeAll($userId): bool
    {
        
    }

}
