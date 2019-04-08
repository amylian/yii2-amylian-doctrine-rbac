<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Tests\Yii\Doctrine\Rbac\Unit;

use \Yii;

/**
 * Description of ManagerTest
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
class ManagerTest extends \yiiunit\framework\rbac\ManagerTestCase
{

    protected function mockApplication($config = array(), $appClass = '\yii\console\Application')
    {
        $defaultConfig = [
            'basePath' => __DIR__.'/../',
            'components' => [
                'authManager' => [
                    'class' => \Amylian\Yii\Doctrine\Rbac\Manager::class,
                ],
            ],
            'container' => [
                'definitions' => [
                    \Doctrine\ORM\EntityManager::class => function ($container, $params, $config) {
                        // Create the configuration
                        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
                                        [\Yii::getAlias('@app/Misc')], true);
                        // Initialize and configure EventManager
                        $eventManager = new \Doctrine\Common\EventManager();
                        $resolver = new \Doctrine\ORM\Tools\ResolveTargetEntityListener();
                        $resolver->addResolveTargetEntity(\Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface::class,
                                \Amylian\Tests\Yii\Doctrine\Rbac\Misc\AuthItem::class, []);
                        $resolver->addResolveTargetEntity(\Amylian\Yii\Doctrine\Rbac\Model\AuthRuleInterface::class,
                                \Amylian\Tests\Yii\Doctrine\Rbac\Misc\AuthRule::class, []);
                        $resolver->addResolveTargetEntity(\Amylian\Yii\Doctrine\Rbac\Model\AuthItemRelationInterface::class,
                                \Amylian\Tests\Yii\Doctrine\Rbac\Misc\AuthItemRelation::class, []);
                        $resolver->addResolveTargetEntity(\Amylian\Yii\Doctrine\Rbac\Model\AuthAssignmentEntityInterface::class,
                                \Amylian\Tests\Yii\Doctrine\Rbac\Misc\AuthAssignment::class, []);
                        $eventManager->addEventSubscriber($resolver);
                        // Initialize the Entity manager and open the connection 
                        return \Doctrine\ORM\EntityManager::create([
                                    'dbname' => $GLOBALS['db_name'],
                                    'user' => $GLOBALS['db_username'],
                                    'password' => $GLOBALS['db_password'],
                                    'host' => $GLOBALS['db_host'],
                                    'driver' => $GLOBALS['db_type'],
                                        ], $config, $eventManager);
                    }
                ]
            ]
        ];
        $config = \yii\helpers\ArrayHelper::merge($defaultConfig, $config);
        parent::mockApplication($config, $appClass);
        
        // Update Schema from Entities 
        
        $em = \yii\di\Instance::of(\Doctrine\ORM\EntityManager::class)->get(); /* @var $em \Doctrine\ORM\EntityManager  */
        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        $schemaValidator = new \Doctrine\ORM\Tools\SchemaValidator($em);
        $errors = $schemaValidator->validateMapping();
        if (!empty($errors)) {
            foreach ($errors as $className => $classErrors) {
                echo "Errors in Class $className:\n";
                foreach ($classErrors as $classError) {
                    echo "$classError\n";
                }
            }
        }
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $sqls = $schemaTool->getUpdateSchemaSql($metadatas, true);
        $schemaTool->updateSchema($metadatas, true);
    }

    protected function createManager(): \yii\rbac\ManagerInterface
    {
        if (!\Yii::$app) {
            $this->mockApplication();
        }
        return Yii::$app->get('authManager');
    }

    protected function setUp()
    {
        parent::setUp();
        $this->auth = $this->createManager();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->auth->removeAll();
    }

}
