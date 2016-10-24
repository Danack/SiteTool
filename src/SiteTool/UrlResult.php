<?php

namespace SiteTool;

class URLResult
{
    private $path;
    private $status;
    private $errorMessage;
    private $referrer;

    function __construct($path, $status, $referrer, $errorMessage = null) {
        $this->path = $path;
        $this->status = $status;
        $this->errorMessage = $errorMessage;
        $this->referrer = $referrer;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage() {
        return $this->errorMessage;
    }

    /**
     * @return mixed
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getStatus() {
        return $this->status;
    }
    
    public function getReferrer() {
        return $this->referrer;
    }
}
