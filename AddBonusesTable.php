<?php

namespace BonusesProgram;

use Bitrix\Iblock\Elements;
use BonusesProgram\Exceptions\BonusesTableExceptions;

/**
 * Class AddBonusesTable
 *
 * @package BonusesProgram
 */
class AddBonusesTable extends BonusesTable
{
    public function __construct(array $config)
    {
        parent::__construct($config);
        if (!$this->createObjectTable()) {
            throw new BonusesTableExceptions('Can\'t create object ElementUsersBonusesTable!');
        }
    }

    /**
     * Method create object ElementUsersBonusesTable for work with iblock
     *
     * @return bool
     */
    protected function createObjectTable() : bool
    {
        $this->obElement = Elements\ElementUsersBonusesTable::createObject();
        return (is_object($this->obElement)) ? true : false;
    }

    /**
     * Method add new user in iblock ElementUsersBonusesTable
     *
     * @param array $properties
     *
     * @return mixed
     * @throws BonusesTableExceptions
     */
    public function addUser(array $properties)
    {
        $this->userId = $properties['userId'];
        $this->bonuses = $properties['bonuses'];
        $this->orders = $properties['orders'];
        $this->groupId = ($properties['groupId'] > 0) ?
            $properties['groupId'] : $this->config['idGroupNewUser'];

        if ($properties['bonusesInHoliday'] > 0 && $this->config['addBonusesInHoliday'] === 'Y') {
            $this->bonusesInHoliday = $properties['bonusesInHoliday'];
            $this->holidayDate = $properties['holidayDate'];
        }

        if ($properties['bonusesAtRegistration'] > 0 && $this->config['addBonusesAtRegistration'] === 'Y') {
            $this->bonusesAtRegistration = $properties['bonusesAtRegistration'];
            $this->registrationDate = $properties['registrationDate'];
        }

         if (!$this->validateClassProperty())
             throw new BonusesTableExceptions('Important AddBonusesTable class properties can\'t be empty!');

        return $this->updateTableFields();
    }

    /**
     * Method validate value property
     *
     * @return bool
     */
    protected function validateClassProperty()
    {
        return  ($this->userId > 0 && $this->groupId > 0) ? true : false;
    }

    /**
     * Method update object ElementUsersBonusesTable and save result in iblock
     *
     * @return mixed
     */
    protected function updateTableFields()
    {
        $this->obElement -> setName($this->userId);
        $this->obElement -> setCode('user_'. $this->userId);

        $props = $this->config['userProps'];

        foreach ($props as $key => $value) {
            $method = 'set'.ucfirst($key);
            if ($this->obElement -> $method()) {
                $this->obElement -> $method($this->$key);
            }
        }

        return $this->obElement->save();
    }
}