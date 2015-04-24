<?php
namespace Users;

use Users\Model\Users;
use Users\Model\UsersTable;
use Zend\Authentication\AuthenticationService;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                )
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Users\Model\UsersTable' => function ($sm) {
                    $tableGateway = $sm->get('UsersTableGateway');
                    $table = new UsersTable($tableGateway);
                    return $table;
                },
                'UsersTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Users());
                    return new TableGateway('users', $dbAdapter, null, $resultSetPrototype);
                },
                'Users\Model\AuthStorage' => function ($sm) {
                    return new \Users\Model\AuthStorage("app_session");
                },
//                use Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter;
//use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;
                'AuthService' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $dbTable = new AuthAdapter($dbAdapter, 'users', 'username', 'password');
                    $authService = new AuthenticationService();
                    $authService->setAdapter($dbTable);
                    $authService->setStorage($sm->get('Users\Model\AuthStorage'));
                    return $authService;
                }
            ),
        );
    }
}
