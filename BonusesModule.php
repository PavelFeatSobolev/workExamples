<?php

namespace BonusesProgram;

use  Bitrix\Iblock\Elements, BonusesProgram\Exceptions\BonusesModuleExceptions;

class BonusesModule
{
    private array $config;
    private array $configProps;
    private object $userDb;

    public function __construct()
    {
        $this->config = $this->readConfig();
        $this->configProps = $this->getConfigUserProps();

        $this->userDb = $this->createObjectModuleClass('BonusesTable');
    }

    /**
     * Method load array configuration from file
     *
     * @return array
     *
     * @throws BonusesModuleExceptions
     */
    protected function readConfig(): array
    {
        $options = require_once 'config/config.php';
        if (empty($options) || !is_array($options)) {
            throw new BonusesModuleExceptions('Error read file configurations!' .
                __CLASS__ . ' ' . __METHOD__ . " line " . __LINE__);
        }
        return $options;
    }

    /**
     * Method get user props fields and alias from array config
     *
     * @return mixed
     * @throws BonusesModuleExceptions
     */
    protected function getConfigUserProps()
    {
        if (empty($this->config['userProps'])) {
            throw new BonusesModuleExceptions('Array userProps in config can\'t be empty!' .
                __CLASS__ . ' ' . __METHOD__ . " line " . __LINE__);
        }

        return $this->config['userProps'];
    }

    /**
     * Method create object class for work with database
     *
     * @param string $className
     *
     * @return mixed
     *
     * @throws BonusesModuleExceptions
     */
    private function createObjectModuleClass(string $className): object
    {
        $objectDb = new $className($this->config);
        if (!is_object($objectDb)) {
            throw new BonusesModuleExceptions('Error create object ' . $className . '!' .
                __CLASS__ . ' ' . __METHOD__ . " line " . __LINE__);
        }
        return $objectDb;
    }

    /**
     * Method get data user by userId
     *
     * @param int $userId
     *
     * @return array
     */
    public function getUserData(int $userId): array
    {
        $arElement = $this->userDb->getUserDataByUserId($userId);
        return (!empty($arElement) && is_array($arElement)) ? $this->prepareDataUserBonuses($arElement) : array();
    }

    /**
     * Method get users by registration date
     *
     * @return array $result
     */
    public function getUsersByRegistration(): array
    {
        return $this->userDb->getUsersByRegistration();
    }

    /**
     * Method prepare user data
     *
     * @param array $data
     *
     * @return array
     */
    protected function prepareDataUserBonuses(array $data): array
    {
        $data['sumAllBonuses'] = intval($data['bonuses']);

        if ($this->config['addBonusesAtRegistration'] === 'Y')
            $data['sumAllBonuses'] += intval($data['bonusesAtRegistration']);

        if ($this->config['addBonusesInHoliday'] === 'Y')
            $data['sumAllBonuses'] += intval($data['bonusesInHoliday']);

        $data['groupId'] = (empty($data['groupId'])) ? 1 : intval($data['groupId']);

        return $data;
    }

    /**
     * Method get all pay bonuses orders user by element id from iblock user bonuses
     *
     * @param int $elementId
     *
     * @return mixed
     */
    public function getPayBonusesOrders(int $elementId): array
    {
        $result = $this->userDb->getUserPropertiesByElementId($elementId, array('orders'));
        if (is_object($result->getOrders()))
            $finalResult = $this->userDb->ordersPropertyPrepare($result->getOrders()->getValue());

        return (!empty($finalResult)) ? $finalResult : array();
    }

    /**
     * Method get users who have not used bonuses for the period
     *
     * @param string $date
     *
     * @return array
     */
    public function getUsersNoApplyBonuses(string $date): array
    {
        $queryParams = array(
            'select' => array(
                'ID', 'USER_ID_' => $this->confProps['userId'], 'DATA_FIRE_' => $this->confProps['dateWriteOff'],
                'BONUSES_' => $this->confProps['bonuses']
            ),
            'filter' => array(
                'IBLOCK_ID' => $this->config['iblockId'], 'ACTIVE' => 'Y',
                '<=' . $this->confProps['dateWriteOff'] . '.VALUE' => $date,
                '>' . $this->confProps['bonuses'] . '.VALUE' => 0
            )
        );

        return $this->userDb->getUsersDataList($queryParams);
    }

    /**
     * Method get users who have not used registration bonuses for the period
     *
     * @param string $date
     *
     * @return array
     */
    public function getUsersNoApplyRegistrationBonuses(string $date): array
    {
        $queryParams = array(
            'select' => array(
                'ID', 'USER_ID_' => $this->confProps['userId'],
                'BONUSES_AT_REGISTRATION_' => $this->confProps['bonusesAtRegistration'],
                'REGISTRATION_DATE_' => $this->confProps['registrationDate']
            ),
            'filter' => array(
                'IBLOCK_ID' => $this->config['iblockId'], 'ACTIVE' => 'Y',
                '<=' . $this->confProps['registrationDate'] . '.VALUE' => $date,
                '>' . $this->confProps['bonusesAtRegistration'] . '.VALUE' => 0
            )
        );

        return $this->userDb->getUsersDataList($queryParams);
    }

    /**
     * Method get users who have not used holiday bonuses for the period
     *
     * @param string $date
     *
     * @return array
     */
    public function getUsersNoApplyHolidayBonuses(string $date): array
    {
        $queryParams = array(
            'select' => array(
                'ID', 'USER_ID_' => $this->confProps['userId'],
                'BONUSES_IN_HOLIDAY_' => $this->confProps['bonusesInHoliday'],
                'HOLIDAY_DATE_' => $this->confProps['holidayDate']
            ),
            'filter' => array(
                'IBLOCK_ID' => $this->config['iblockId'], 'ACTIVE' => 'Y',
                '<=' . $this->confProps['holidayDate'] . '.VALUE' => $date,
                '>' . $this->confProps['bonusesInHoliday'] . '.VALUE' => 0
            )
        );

        return $this->userDb->getUsersDataList($queryParams);
    }


    /**
     * This method write-off bonuses with account user
     *
     * @param int $userId
     * @param int $bonusesWriteOf
     *
     * @return bool
     * @throws BonusesModuleExceptions
     */
    public function deleteBonusesUser(int $userId, int $bonusesWriteOf): bool
    {
        $checkReg = $this->config['addBonusesAtRegistration'];
        $checkHoliday = $this->config['addBonusesInHoliday'];
        $userInfo = $this->userDb->getUserBonus($userId);

        if (!empty($userInfo) && $userInfo['ID'] > 0) {

            $arProps = array('DATE_LAST' => date($this->config['dateFormat']));

            //check amount bonuses from registration
            if ($checkReg === 'Y' && !empty($userInfo['REGISTRATION_BONUS'])) {
                $arProps['REGISTRATION_BONUS'] = $this->writeOfBonuses($userInfo['REGISTRATION_BONUS'],
                    $bonusesWriteOf);
            }
            if ($checkHoliday === 'Y' && !empty($userInfo['REGISTRATION_BONUS'])) {
                $arProps['HOLIDAY_BONUS'] = $this->writeOfBonuses($userInfo['HOLIDAY_BONUS'],
                    $bonusesWriteOf);
            }
            if ($bonusesWriteOf > 0 && $userInfo['BONUS'] > 0) {
                $arProps['BONUS'] = $userInfo['BONUS'] - $bonusesWriteOf;
            }

            //update user account
            $updateOb = $this->createObjectModuleClass('UpdateBonusesTable');
            if (is_object($updateOb)) {
                if ($this->updateBonusesUserAccount($userInfo['ID'], $arProps)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Method write of bonuses with user account
     *
     * @param int $bonusesUser
     * @param int $bonusesWriteOf
     *
     * @return int
     */
    public function writeOfBonuses(int $bonusesUser, int &$bonusesWriteOf): int
    {
        if ($bonusesWriteOf <= $bonusesUser) {
            $bonusesUser -= $bonusesWriteOf;
            $bonusesWriteOf = 0;
        } else {
            $bonusesUser = 0;
            $bonusesWriteOf -= $bonusesUser;
        }

        return $bonusesUser;
    }

    /**
     * Method add new user to bonuses system
     *
     * @param array $properties
     *
     * @return mixed
     * @throws BonusesModuleExceptions
     */
    public function addNewUser(array $properties)
    {
        $userObj = $this->createObjectModuleClass('AddBonusesTable');
        return $userObj->addUser($properties);
    }

    /**
     * Add bonuses from holiday
     *
     * @param int $userId
     *
     * @return int
     */
    public function addHolidayBonuses(int $userId): int
    {
        if ($this->config['addBonusesInHoliday'] === 'Y') {

            $amountBonusHoliday = intval($this->config['amountBonusesHoliday']);
            $dateHoliday = $this->config['dataHoliday'];
            $dateToday = date($this->config['']);

            if (!empty($dateHoliday) && $amountBonusHoliday > 0) {
                if ($dateToday === $dateHoliday) {
                    return $amountBonusHoliday;
                }
            }
        }

        return 0;
    }

    /**
     * Method add bonuses from order
     *
     * @param int $userId
     * @param int $quantityBonus
     * @param $orderId
     *
     * @return bool
     */
    public function addBonusesFromOrder(int $userId, int $quantityBonus, int $orderId): bool
    {
        $this->userDb->getUserDataByUserId($userId);
        $this->userDb->orders;

        if (!in_array($orderId, $this->userDb->orders)) {
            if ($this->addBonusesUser($userId, $quantityBonus, $orderId))
                return true;
        }
        return false;
    }

    /**
     * Method calculate pay bonuses on basket or basket order
     *
     * @param int $userId
     * @param object $basket
     * @param int $sumFreeDelivery
     * @param int $enterUserSum
     *
     * @return mixed
     * @throws BonusesModuleExceptions
     */
    public function calculateBuyBonuses(int $userId, object $basket, int $sumFreeDelivery, int $enterUserSum)
    {
        $userData = $this->getUserData($userId);
        $percentDiscount = $this->getPercentGroupDiscount($userData['groupId']);
        $bonusesBalance = ($enterUserSum > 0 && $enterUserSum < $userData['sumAllBonuses']) ?
            $enterUserSum : $userData['sumAllBonuses'];

        $calculateOb = $this->createObjectModuleClass('CalculateBonuses');
        $calculateOb->setFreeDelivery($sumFreeDelivery);
        $calculateOb->setPercentDiscount($percentDiscount);

        return $calculateOb->calculateWriteOff($basket, $bonusesBalance);
    }

    /**
     * Method get discount percent from group users
     *
     * @param int $groupId
     * @return int|mixed
     *
     * @throws BonusesModuleExceptions
     */
    protected function getPercentGroupDiscount(int $groupId)
    {
        $objBonusesGroups = $this->createObjectModuleClass('GroupsBonusesProgram');
        $userGroupParams = $objBonusesGroups->getParamsGroupId($groupId);

        return  (!empty($userGroupParams['PERCENT'])) ? $userGroupParams['PERCENT'] : 0;
    }
}