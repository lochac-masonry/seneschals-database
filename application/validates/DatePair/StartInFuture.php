<?php

require_once 'Zend/Validate/Abstract.php';

class SenDb_Validate_DatePair_StartInFuture extends Zend_Validate_Abstract
{
    const INVALID = 'datePair_startInFuture_invalid';
    const PAST    = 'startInPast';

    protected $_messageTemplates = array(
        self::INVALID => "Invalid value received. Array of start and end dates and times expected",
        self::PAST    => "Start date/time is not in the future"
    );

    public function isValid($value)
    {
        if (!is_array($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        try {
            $startDateTime = new DateTime($value['startdate'] . ' ' . $value['starttime']);
        } catch (Exception $e) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);

        if ((new DateTime()) >= $startDateTime) {
            $this->_error(self::PAST);
            return false;
        }
        return true;
    }

}
