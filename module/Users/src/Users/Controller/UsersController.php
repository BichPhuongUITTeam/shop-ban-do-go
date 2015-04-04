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

    public function isLoggedIn()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return TRUE;
        }
        return FALSE;
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
        $loggedIn = $this->isLoggedIn();
        if ($loggedIn) {

            $view = new ViewModel();
            $users = $this->getUsersTable()->fetchAll();
//         $view->setTemplate('users/users/default.phtml');
//         $this->layout()->setTemplate('layout/custom_layout.phtml');
            // $view->setTerminal(true);
            return array(
                'view' => $view,
                'users' => $users,
                'messages' => $this->flashMessenger()->getMessages(),
            );
        }
        return $this->logoutAction();
    }

    public function loginAction()
    {
        $loggedIn = $this->isLoggedIn();
        if ($loggedIn) {
            return $this->indexAction();
        } else {
            $view = new ViewModel();
            $form = new LoginForm();
            $form->get('submit')->setValue('Login');
            // $view->setTemplate('users/index/login');
            return array(
                'form' => $form,
                'view' => $view,
                'messages' => $this->flashMessenger()->getMessages(),
            );
        }
    }

    public function authenticateAction()
    {
//        $view = new ViewModel();
        $form = new LoginForm();
        $form->get('submit')->setValue('Login');
        $redirect = 'login';
        $session = array(
            'username' => NULL,
            'logged_in' => FALSE,
        );
        $request = $this->getRequest();
//        var_dump($request->isPost()); die;
        if ($request->isPost()) {
            $user = new Users();
            $form->setInputFilter($user->getLoginInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                $result = $this->getUsersTable()->loginUser($user);
                if ($result) {
                    $redirect = 'index';
                    // Check if user select Remember me
                    if ($request->getPost('remember_me') == 1) {
                        // Set storage again
                        $this->getSessionStorage()->setRememberMe(1);
                    }
                    $this->getSessionStorage()->setRememberMe();
                    $session['username'] = $request->getPost('username');
                    $session['logged_in'] = TRUE;
                    $fullname = $this->getUsersTable()->getUserFullName($request->getPost('username'));
                    $message = "Welcome, <strong>" . $fullname . "</strong>";
                    $this->flashMessenger()->addMessage($message);
                }
//                $user->exchangeArray($form->getData());
//                $login = $this->getUsersTable()->loginUser($user);
//                if ($login) {
//                }
            }
        }
        $this->getAuthService()->getStorage()->write($session);
        return $this->redirect()->toRoute('users', array('action' => $redirect));
    }

    public
    function registerAction()
    {
        $loggedIn = $this->isLoggedIn();
        if ($loggedIn) {
            return $this->redirect()->toRoute('home');
        } else {
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
        $loggedIn = $this->isLoggedIn();
        if (!$loggedIn) {
            return $this->logoutAction();
        } else {
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
    }

    public
    function editAction()
    {
        $loggedIn = $this->isLoggedIn();
        if (!$loggedIn) {
            return $this->logoutAction();
        } else {
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
    }

    public function deleteAction()
    {
        $loggedIn = $this->isLoggedIn();
        if (!$loggedIn) {
            return $this->logoutAction();
        } else {
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
}
