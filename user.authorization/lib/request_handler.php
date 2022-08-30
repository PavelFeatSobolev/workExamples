<?php
use Custom\Service\Application;

class UserAuthRequestHandler extends Application\RequestData
{
    private const COMPONENT_NAME = 'userAuth';

    private $arPostData;

    public function __construct()
    {
        parent::__construct();
        $this->arPostData = parent::getArrayPost();
        parent::setPropertyClass($this->arPostData);
    }

    /**
     * Method check request
     *
     * @return bool
     */
    public function checkRequestParamsComponent()
    {
        return (!empty($this->arPostData['action']) && $this->arPostData['component']  === self::COMPONENT_NAME) ?
            true : false;
    }

    /**
     * Method check class property for auth user
     *
     * @return bool
     */
    public function checkAuthData()
    {
        return (!empty($this->arPostData['phoneUser'])) ? true : false;
    }
}