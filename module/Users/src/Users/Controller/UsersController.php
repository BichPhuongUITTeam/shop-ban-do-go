<?php
namespace Users\Controller;

use Users\Form\LoginForm;
use Users\Form\UserForm;
use Users\Model\Users;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

//use Zend\Authentication\AuthenticationService;
//use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;

class UsersController extends AbstractActionController
{
    protected $usersTable;
    protected $storage;
    protected $authService;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
//        $this->checkAuthentication();
        return parent::onDispatch($e);
    }

    public function checkAuthentication()
    {
        if (!$this->getAuthService()->hasIdentity()) {
            return $this->logoutAction();
        }
    }

    public function getAuthService()
    {
        if (!$this->authService) {
            $this->authService = $this->getServiceLocator()->get('AuthService');
        }
        return $this->authService;
    }

    public function getSessionStorage()
    {
        if (!$this->storage) {
            $this->storage = $this->getServiceLocator()->get('Users\Model\AppAuthStorage');
        }
        return $this->storage;
    }

    public function getUsersTable()
    {
        if (!$this->usersTable) {
            $sm = $this->getServiceLocator();
            $this->usersTable = $sm->get('Users\Model\UsersTable');
        }

        return $this->usersTable;
    }

    public function indexAction()
    {
        if (!$this->getAuthService()->hasIdentity()) {
            return $this->logoutAction();
        }
        $view = new ViewModel(array(
            'users' => $this->getUsersTable()->fetchAll(),
        ));
//         $view->setTemplate('users/users/default.phtml');
//         $this->layout()->setTemplate('layout/custom_layout.phtml');
        // $view->setTerminal(true);
        return $view;
    }

    public function loginAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('users', array('action' => 'index'));
        }
        $view = new ViewModel();
        $form = new LoginForm();
        $form->get('submit')->setValue('Login');
        // $view->setTemplate('users/index/login');
        return array(
            'form' => $form,
            'view' => $view,
        );
    }

    public function authenticateAction()
    {
//        $view = new ViewModel();
        $form = new LoginForm();
        $form->get('submit')->setValue('Login');
        $redirect = 'login';
        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new Users();
            $form->setInputFilter($user->getLoginInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $auth = $this->getAuthService();
                $password_hash = $this->getUsersTable()->getUserPasswordHash($request->getPost('username'));
                $auth->getAdapter()
                    ->setIdentity($request->getPost('username'))
                    ->setCredential($request->getPost('password'))
                    ->setCredentialTreatment(password_verify($request->getPost('password'), $password_hash));
                $result = $auth->authenticate();
                foreach ($result->getMessages() as $message) {
                    $this->flashMessenger()->addMessage($message);
                }

                if ($result->isValid()) {
                    $redirect = 'index';
                    // Check if user select Remember me
                    if ($request->getPost('remember_me') == 1) {
                        // Set storage again
                        $this->getSessionStorage()->setRememberMe(1);
                    }
                    $this->getAuthService()->getStorage()->write(array(
                        'username' => $request->getPost('username'),
                        'logged_in' => TRUE,
                    ));

                }
//                $user->exchangeArray($form->getData());
//                $login = $this->getUsersTable()->loginUser($user);
//                if ($login) {
//                }
            }
        }
        $this->getAuthService()->getStorage()->write($redirect);
        return $this->redirect()->toRoute('users', array('action' => $redirect));
    }

    public
    function registerAction()
    {
        $view = new ViewModel();
//        $view->setTemplate('users/users/index.phtml');
//        $view->setTerminal(true);
        $form = new UserForm();
        $form->get('submit')->setValue('Register');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new Users();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                $this->getUsersTable()->saveUser($user);
                return $this->redirect()->toRoute('users', array('action' => 'index'));
            }
        }
        return array(
            'form' => $form,
            'view' => $view,
        );
    }

    public
    function logoutAction()
    {
        $this->getSessionStorage()->forgetMe();
        $this->getAuthService()->clearIdentity();
        $this->flashMessenger()->addMessage("You 've been logged out!");
        return $this->redirect()->toRoute('users', array('action' => 'login'));
    }

    public
    function addAction()
    {
        $view = new ViewModel();
        $view->setTemplate('users/users/add.phtml');
        $form = new UserForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new Users();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                $this->getUsersTable()->saveUser($user);

                return $this->redirect()->toRoute('users', array('action' => 'index'));
            }
        }

        return array(
            'form' => $form,
            'view' => $view
        );
    }

    public
    function editAction()
    {
        if (!$this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('users', array('action' => 'login'));
        }
        $id = (int)$this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute('users', array('action' => 'add'));
        }

//        Get the user with specification id.
//        An exception is thrown if it cannot be found, in which case go to index page.
        try {
            $user = $this->getUsersTable()->getUserById($id);
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('users', array('action' => 'index'));
        }

        $form = new UserForm();
        $form->bind($user);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getUsersTable()->saveUser($user);

                // Redirect to list of user
                return $this->redirect()->toRoute('users');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );

    }

    public
    function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute('users', array('action' => 'index'));
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int)$request->getPost('id');
                $this->getUsersTable()->deleteUser($id);
            }

            // Redirect to list of user
            return $this->redirect()->toRoute('users');
        }

        return array(
            'id' => $id,
            'user' => $this->getUsersTable()->getUserById($id),
        );
    }
}
