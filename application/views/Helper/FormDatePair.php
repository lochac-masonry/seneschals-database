<?php

require_once 'Zend/View/Helper/FormElement.php';

class SenDb_View_Helper_FormDatePair extends Zend_View_Helper_FormElement
{
    public function formDatePair($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, id, value, attribs, options, listsep, disable

        // parse value
        $startDate = '';
        $startTime = '';
        $endTime = '';
        $endDate = '';
        if (is_array($value)) {
            if (isset($value['startdate'])) {
                $startDate = ' value="' . $this->view->escape($value['startdate']) . '"';
            }
            if (isset($value['starttime'])) {
                $startTime = ' value="' . $this->view->escape($value['starttime']) . '"';
            }
            if (isset($value['endtime'])) {
                $endTime = ' value="' . $this->view->escape($value['endtime']) . '"';
            }
            if (isset($value['enddate'])) {
                $endDate = ' value="' . $this->view->escape($value['enddate']) . '"';
            }
        }

        // is it disabled?
        $disabled = '';
        if ($disable) {
            $disabled = ' disabled="disabled"';
        }

        // build the container
        $xhtml = '<p'
                . ' id="' . $this->view->escape($id) . '"'
                . $this->_htmlAttribs($attribs)
                . '>'
                . "\n";

        // add the individual fields
        // - start date
        $xhtml .= '    <input type="text" size="10"'
                . ' name="' . $this->view->escape($name) . '[startdate]"'
                . ' id="' . $this->view->escape($id) . '_start_date"'
                . ' class="start date"'
                . $startDate
                . $disabled
                . $this->getClosingBracket()
                . "\n";

        // - start time
        $xhtml .= '    <input type="text" size="6"'
                . ' name="' . $this->view->escape($name) . '[starttime]"'
                . ' id="' . $this->view->escape($id) . '_start_time"'
                . ' class="start time"'
                . $startTime
                . $disabled
                . $this->getClosingBracket()
                . "\n";

        $xhtml .= '    <span class="to"> to </span>'
                . "\n";

        // - end time
        $xhtml .= '    <input type="text" size="6"'
                . ' name="' . $this->view->escape($name) . '[endtime]"'
                . ' id="' . $this->view->escape($id) . '_end_time"'
                . ' class="end time"'
                . $endTime
                . $disabled
                . $this->getClosingBracket()
                . "\n";

        // - end date
        $xhtml .= '    <input type="text" size="10"'
                . ' name="' . $this->view->escape($name) . '[enddate]"'
                . ' id="' . $this->view->escape($id) . '_end_date"'
                . ' class="end date"'
                . $endDate
                . $disabled
                . $this->getClosingBracket()
                . "\n";

        $xhtml .= '</p>';

        return $xhtml;
    }
}
