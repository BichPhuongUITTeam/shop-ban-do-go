<?php

namespace Album\Form;

use Zend\Form\Form;

class AlbumForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('album');

        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden',
        ));

        $this->add(array(
            'name' => 'title',
            'type' => 'Text',
            'attributes' => array(
                'class' => 'form-control',
            ),
//            'options' => array(
//                'label' => 'Title',
//                'div' => array(
//                    'class' => 'form-group',
//                ),
//            ),
        ));

        $this->add(array(
            'name' => 'artist',
            'type' => 'Text',
//            'options' => array(
//                'label' => 'Artist',
//            ),
            'attributes' => array(
                'class' => 'form-control',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Submit',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}