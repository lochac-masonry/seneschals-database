<?php

require_once 'Zend/Validate/Abstract.php';

class SenDb_Validate_DatePair_Ordered extends Zend_Validate_Abstract
{
    const INVALID     = 'datePair_ordered_invalid';
    const NOT_ORDERED = 'notOrdered';

    protected $_messageTemplates = array(
        self::INVALID     => "Invalid value received. Array of start and end dates and times expected",
        self::NOT_ORDERED => "End date/time is not after the start date/time"
    );

    public function isValid($value)
    {
        if (!is_array($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        try {
            $startDateTime = new DateTime($value['startdate'] . ' ' . $value['starttime']);
            $endDateTime = new DateTime($value['enddate'] . ' ' . $value['endtime']);
        } catch (Exception $e) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);

        if ($startDateTime > $endDateTime) {
            $this->_error(self::NOT_ORDERED);
            return false;
        }
        return true;
    }

}
