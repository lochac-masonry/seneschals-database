<?php

require_once 'Zend/Validate/Abstract.php';

class SenDb_Validate_DatePair_NotEmpty extends Zend_Validate_Abstract
{
    const INVALID            = 'datePair_notEmpty_invalid';
    const EMPTY_DATE_OR_TIME = 'emptyDateOrTime';

    protected $_messageTemplates = array(
        self::INVALID            => "Invalid value received. Array of start and end dates and times expected",
        self::EMPTY_DATE_OR_TIME => "One or more dates or time were not provided, and are required"
    );

    public function isValid($value)
    {
        if (!is_array($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);

        if (empty($value['startdate'])
          || empty($value['enddate'])
          || empty($value['starttime'])
          || empty($value['endtime'])) {
            $this->_error(self::EMPTY_DATE_OR_TIME);
            return false;
        }
        return true;
    }

}
