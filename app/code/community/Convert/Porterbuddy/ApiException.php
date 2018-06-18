<?php
/**
 * @author Convert Team
 * @copyright Copyright (c) 2017 Convert (http://www.convert.no/)
 */
class Convert_Porterbuddy_ApiException extends Convert_Porterbuddy_Exception
{
    protected $logData;

    public function __construct($message = "", array $logData = array(), Throwable $previous = null)
    {
        $this->logData = $logData;
        parent::__construct($message, null, $previous);
    }

    /**
     * @return array
     */
    public function getLogData()
    {
        return $this->logData;
    }

    /**
     * @param array $logData
     * @return $this
     */
    public function setLogData($logData)
    {
        $this->logData = $logData;
        return $this;
    }
}
