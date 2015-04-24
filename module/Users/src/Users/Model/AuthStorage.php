<?php
/**
 * Author: ansidev
 * Date: 04/04/2015
 * Time: 12:33 AM
 */

namespace Users\Model;

use Zend\Authentication\Storage;

class AuthStorage extends Storage\Session
{
    public function setRememberMe($rememberMe = 0, $timeOut = 900)
    {
        if ($rememberMe == 1) {
            $timeOut = 86400;
        }
        $this->session->getManager()->rememberMe($timeOut);
    }

    public function forgetMe()
    {
        $this->session->getManager()->forgetMe();
    }
}