<?php
/**
 * Author: ansidev
 * Date: 04/04/2015
 * Time: 12:33 AM
 */

namespace Users\Model;

use Zend\Authentication\Storage;

class AppAuthStorage extends Storage\Session
{
    public function setRememberMe($rememberMe = 0, $timeOut = 86400)
    {
        if ($rememberMe == 1) {
            $this->session->getManager()->rememberMe(5);
        }
    }

    public function forgetMe()
    {
        $this->session->getManager()->forgetMe();
    }
}