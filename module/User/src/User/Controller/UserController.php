<?php
namespace User\Controller;

use User\Form\UserForm;
use User\Model\User;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class UserController extends AbstractActionController
{
    protected $userTable;

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
        $view = new ViewModel(array(
            'users' => $this->getUserTable()->fetchAll(),
        ));
        // $view->setTemplate('user/user/default.phtml');
        // $this->layout()->setTemplate('layout/layout.phtml');
        // $view->setTerminal(true);
        return $view;
    }

    public function registerAction() {
        $view = new ViewModel();
        // $view->setTemplate('user/user/add.phtml')
             // ->setTerminal(true);
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

                return $this->redirect()->toRoute('user');
            }
        }

        return array(
            'form' => $form,
            'view' => $view
        );
    }

    public function loginAction() {
        // echo 'Đây là trang đăng nhập';
        $view = new ViewModel();
        // $view->setTemplate('users/index/login');
        return $view;
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
            $user = $this->getUserTable()->getUser($id);
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
            'user' => $this->getUserTable()->getUser($id),
        );
    }

}
