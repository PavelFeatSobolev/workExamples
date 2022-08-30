<?php

namespace BonusesProgram;

use Bitrix\Iblock\Elements, BonusesProgram\Exceptions;
use Bitrix\Main\Application;

class BonusesTable
{
    protected array $config;

    public int $userId = 0;
    public int $bonuses = 0;
    public int $bonusesAtRegistration = 0;
    public int $bonusesInHoliday = 0;
    public int $writeOf = 0;
    public int $accrued = 0;
    public string $dataFire = '';
    public int $groupId = 0;
    public string $registrationDate = '';
    public string $holidayDate = '';
    public string $orders = '';
    public string $dateWriteOff = '';

    public array $arClassProperty;
    protected object $obElement;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Method get user data from cache
     *
     * @param int $userId
     *
     * @return array
     */
    public function getUserDataByUserId(int $userId): array
    {
        $cache = Application::getInstance()->getManagedCache();
        $cacheId = md5($userId);
        $cacheTime = $this->config['cache_time'];

        if ($cache->read($cacheTime, $cacheId)) {
            $arResult = $cache->get($cacheId);
        } else {
            $arResult = $this->getUserData($userId);
            (empty($arResult)) ? $cache->clean($cacheId) : $cache->set($cacheId, $arResult);
        }

        return $arResult;
    }

    /**
     * Method get user data from table user bonuses
     *
     * @param int $userId
     * @return object
     *
     * @throws Exceptions\BonusesTableExceptions
     */
    public function getUserData(int $userId): array
    {
        $arProperty = $this->addAliasesPropertySelect();
        $arSelect = array_merge(array('ID', 'NAME'), $arProperty);

        $queryParams = array(
            'select' => $arSelect,
            'filter' => array('=ACTIVE' => 'Y', $this->config['user_props']['userId'] . '.VALUE' => $userId)
        );

        $elements = $this->getUsersDataList($queryParams);
        if (count($elements) > 1) {
            throw new Exceptions\BonusesTableExceptions(
                'Two users with one id in table ElementUsersBonusesTable');
        }
        $this->obElement = (!empty($elements)) ? $elements[array_key_first($elements)] : array();

        return $this->getArrayByObjectElement($this->obElement);
    }

    /**
     * Method get users registration date
     *
     * @return array $result
     */
    public function getUsersByRegistration(): array
    {
        $result = array();
        $propName = $this->config['user_props'];
        $queryParams = array(
            'select' =>
                array('ID', 'NAME', 'registrationDate_'=> $propName['registrationDate'],
                    'bonusesAtRegistration_' => $propName['bonusesAtRegistration']),
            'filter' => array(
                '=ACTIVE' => 'Y',
                '!'. $propName['registrationDate'] .'.VALUE' => 0,
                '>'. $propName['bonusesAtRegistration'] .'.VALUE' => 0
            ),
        );
        $elements =  $this->getUsersDataList($queryParams);
        foreach ($elements as $key => $element) {
            $result[$key] = $this->getArrayByObjectElement($element, false);
        }

        return $result;
    }

    protected function getArrayByObjectElement(object $element,bool $updateClassProp = true): array
    {
        $result = array();
        foreach ($this->config['user_props'] as $key => $value) {
            $method = 'get'.ucfirst($key);
            if (is_object($element->$method())) {
                $propValue = $element->$method()->getValue();

                //prepare order from json in array
                if ($element->$method() === 'getOrders' && !empty($propValue)) {
                    $propValue =  $this->ordersPropertyPrepare($propValue);
                }

                $result[$key] = $propValue;

                if ($updateClassProp === true) {
                    $this->$key = $propValue;
                    $this->arClassProperty[$key] = $propValue;
                }
            }
        }

        return $result;
    }

    /**
     * Method prepare array for selecting query data users
     *
     * @return array
     */
    protected function addAliasesPropertySelect(): array
    {
        $result = array();
        if (!empty($this->config['user_props'])) {
            foreach ($this->config['user_props'] as $value) {
                $result[$value . '_'] = $value;
            }
        }

        return $result;
    }

    /**
     * Method prepare props orders
     *
     * @param string $orders
     *
     * @return array
     */
    public function ordersPropertyPrepare(string $orders): array
    {
        return json_decode($orders);
    }

    /**
     * Method getList orm bitix from table(iblock) ElementUsersBonusesTable
     *
     * @param array $queryParams
     *
     * @return array
     */
    public function getUsersDataList(array $queryParams): array
    {
        $result = array();

        $query = Elements\ElementUsersBonusesTable::getList($queryParams)->fetchCollection();
        foreach ($query as $element) {
            $result[$element['ID']] = $element;
        }

        return $result;
    }


    public function getUserPropertiesByElementId(int $elementId, array $codeProperties): object|bool
    {
        if ($elementId <= 0)
            throw new Exceptions\BonusesTableExceptions('Argument $elementId can\'t to be null');

        foreach ($codeProperties as $property) {
            if (!empty($this->config['user_props'][$property])) {
                $select[$property . '_'] = $this->config['user_props'][$property];
            }
        }
        array_push($select, 'ID');

        $element = Elements\ElementUsersBonusesTable::getByPrimary($elementId, array(
            'select' => $select
        ))->fetchObject();

        return (!empty($element)) ? $element : false;
    }
}