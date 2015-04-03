<?php
namespace User\Controller;

use User\Form\UserForm;
use User\Form\LoginForm;
use User\Model\User;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

//use Zend\Authentication\AuthenticationService;
//use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;

class UserController extends AbstractActionController
{
    protected $userTable;
    protected $storage;
    protected $authService;

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
            $this->storage = $this->getServiceLocator()->get('User\Model\AppAuthStorage');
        }
        return $this->storage;
    }

    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('User\Model\UserTable');
        }

        return $this->userTable;
    }

    public function indexAction()
    {
        if (!$this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('user', array('action' => 'login'));
        }
        $view = new ViewModel(array(
            'users' => $this->getUserTable()->fetchAll(),
        ));
//         $view->setTemplate('user/user/default.phtml');
//         $this->layout()->setTemplate('layout/custom_layout.phtml');
        // $view->setTerminal(true);
        return $view;
    }

    public function loginAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('user', array('action' => 'index'));
        }
        $view = new ViewModel();
        $form = new LoginForm();
        $form->get('submit')->setValue('Login');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new User();
            $form->setInputFilter($user->getLoginInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                $login = $this->getUserTable()->loginUser($user);
                if ($login) {
                    // Check if user select Remember me
                    if ($request->getPost('remember_me') == 1) {
                        // Set storage again
                        $this->getSessionStorage()->setRememberMe(1);
                    }
                    $this->getAuthService()->getStorage()->write($request->getPost('username'));
                    return $this->redirect()->toRoute('user', array('action' => 'index'));
                }
            }
        }
        // $view->setTemplate('users/index/login');
        return array(
            'form' => $form,
            'view' => $view,
        );
    }

    public function registerAction()
    {
        $view = new ViewModel();
//        $view->setTemplate('user/user/index.phtml');
//        $view->setTerminal(true);
        $form = new UserForm();
        $form->get('submit')->setValue('Register');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new User();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                $this->getUserTable()->saveUser($user);
                return $this->redirect()->toRoute('user', array('action' => 'index'));
            }
        }
        return array(
            'form' => $form,
            'view' => $view,
        );
    }

    public function logoutAction()
    {
        $this->getSessionStorage()->forgetMe();
        $this->getAuthService()->clearIdentity();
        $this->flashMessenger()->addMessage("You 've been logged out!");
        return $this->redirect()->toRoute('user', array('action' => 'login'));
    }

    public function addAction()
    {
        $view = new ViewModel();
        $view->setTemplate('user/user/add.phtml');
        $form = new UserForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new User();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                $this->getUserTable()->saveUser($user);

                return $this->redirect()->toRoute('user', array('action' => 'index'));
            }
        }

        return array(
            'form' => $form,
            'view' => $view
        );
    }

    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute('user', array('action' => 'add'));
        }

//        Get the user with specification id.
//        An exception is thrown if it cannot be found, in which case go to index page.
        try {
            $user = $this->getUserTable()->getUserById($id);
        } catch (\Exception $ex) {
            return $this->redirect()->toRoute('user', array('action' => 'index'));
        }

        $form = new UserForm();
        $form->bind($user);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getUserTable()->saveUser($user);

                // Redirect to list of user
                return $this->redirect()->toRoute('user');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );

    }

    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute('user', array('action' => 'index'));
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int)$request->getPost('id');
                $this->getUserTable()->deleteUser($id);
            }

            // Redirect to list of user
            return $this->redirect()->toRoute('user');
        }

        return array(
            'id' => $id,
            'user' => $this->getUserTable()->getUserById($id),
        );
    }
}
