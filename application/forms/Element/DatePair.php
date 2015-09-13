<?php

require_once 'Zend/Form/Element/Xhtml.php';

class SenDb_Form_Element_DatePair extends Zend_Form_Element_Xhtml
{
    public $helper = 'formDatePair';

    public function render(Zend_View_Interface $view = null)
    {
        if (null === $view) {
            $view = $this->getView();
        }

        $view->addHelperPath('application/views/Helper', 'SenDb_View_Helper');

        return parent::render($view);
    }
}
