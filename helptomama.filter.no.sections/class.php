<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
use Bitrix\Main\Entity;

/**
 * Class HelptomamaFilterNoSection
 */
class HelptomamaFilterNoSection extends CBitrixComponent
{

    /* Получаем id на основе переданных ID товаров
     *
     * @PARAM array elementsIds - массив ID товаров
     *
     * @RETURN array/bool
    */
    public static function getSectionsIdsForElements($elementsIds,$iblockId)
    {
        //Получаем разделы на основе переданных Id товаров
        $parametersQuerySectionsIds = array(
            'select'  => array('IBLOCK_SECTION_ID', 'IBLOCK_ELEMENT_ID'),
            'filter'  => array('IBLOCK_ELEMENT_ID' => $elementsIds),
            'group'   => array(),
            'order'   => array(),
        );
        $resultQuerySectionsIds = \Bitrix\Iblock\SectionElementTable::getList($parametersQuerySectionsIds);
        while ($arQuerySectionsIds = $resultQuerySectionsIds->fetch())
        {
            $arSections['IDS'][$arQuerySectionsIds['IBLOCK_SECTION_ID']] = $arQuerySectionsIds['IBLOCK_SECTION_ID'];
            $arSections[$arQuerySectionsIds['IBLOCK_SECTION_ID']] = $arQuerySectionsIds;
        }
        //Получаем список всех разделов для последующего нахождения родительских разделов
        $arNewSections = self::getAllSections($iblockId, $arSections['IDS']);
        if (is_array($arNewSections)) {
            $arSections['IDS'] = $arNewSections;
        }
        return $arSections;
    }

    /* Метод получает Id родительских разделов на основе переданного массива Id разделов
     *
     * @PARAM - int $iblockId - id инфо.блока
     * @PARAM -  array $sectionsIds - массив id разделов, для которых необходимо найти родителей
     *
     * @RETURN - array/false  $newArrayIds - новый числовой массив разделов и и х родителей вида array(0 => "ID_раздела", ...)
    */
    public static function getAllSections($iblockId, $sectionsIds)
    {
        if ($iblockId > 0)
        {
            $newArrayIds = array();
            $parametersQuerySectionsIds = array(
                'select'  => array('ID', 'IBLOCK_SECTION_ID'),
                'filter'  => array('ACTIVE' => "Y", 'IBLOCK_ID' => $iblockId)
            );
            $resultQuerySectionsIds = \Bitrix\Iblock\SectionTable::getList($parametersQuerySectionsIds);
            while ($arQuerySectionsIds = $resultQuerySectionsIds->fetch())
            {
                $newArraySection[$arQuerySectionsIds['ID']]["ID"] = $arQuerySectionsIds['ID'];
                $newArraySection[$arQuerySectionsIds['ID']]["PARENT_ID"] = $arQuerySectionsIds['IBLOCK_SECTION_ID'];
            }
            foreach ($sectionsIds as $sectionId)
            {
                $newArrayIds[] = $sectionId;
                $newArrayIds = self::searchElementSectionParents($sectionId, $newArraySection, $newArrayIds);
            }

            return $newArrayIds;
        }
        else
        {
            return false;
        }

    }

    /* Поиска родительского раздела в массиве разделов
     *
     * @PARAM int $element Id текущего элемента
     * @PARAM array $arrayElements - масиив всех разделов видв array("ID раздела" => array("PARENT_ID" => "ID раздела родителя"))
     * @PARAM array $newArray - массив c уже найдеными id разделов, заполняется через данный метод
     *
     * @RETURN array $newArray - возвращает уже заполненный массив разделов
    */
    public static function searchElementSectionParents ($element, $arrayElements, $newArray = array()) {
        if (is_array($arrayElements))
        {
            if (!empty($arrayElements[$element]["PARENT_ID"]))
            {
                $newArray[] = $arrayElements[$element]["PARENT_ID"];

                $result = self::searchElementSectionParents($element["PARENT_ID"], $arrayElements, $newArray);
                if ($result !== false && is_array($result)) {
                    $newArray = $result;
                }
                return $newArray;
            }

            return $newArray;
        }
        else
        {
            return false;
        }
    }

    /* Метод получает свойства участвующие в умном фильтре на основе пререданных разделов
     *
     * @PARAM array sectionsIds - массив Id разделов по которым необходимо найти св-ва
     * @PARAM array propertyNoDisplay - массив свойств которые необходимо исключить(Необязательный)
     *
     * @RETURN array
    */
    public static function getPropertysSmartFiterToSections($sectionsIds, $propertyNoDisplay)
    {
        if (is_array($sectionsIds) || $sectionsIds > 0)
        {
            $parametersQueryProps = array(
                'select'  => array('PROPERTY_ID', 'SECTION_ID', 'PROPERTY.PROPERTY_TYPE','PROPERTY.NAME','PROPERTY.SORT','PROPERTY.CODE',"PROPERTY.IBLOCK_ID"),
                'filter'  => array('SMART_FILTER' => 'Y', 'SECTION_ID' => $sectionsIds),
                'group'   => array('PROPERTY_ID'),
            );
            //Если передан массив свойств которые необходимо исключить
            if (!empty($propertyNoDisplay) && is_array($propertyNoDisplay))
            {
                $parametersQueryProps['filter']['!PROPERTY_ID'] = $propertyNoDisplay;
            }
            $resultQuery = \Bitrix\Iblock\SectionPropertyTable::getList($parametersQueryProps);
            while ($arPropQuery = $resultQuery->fetch())
            {
                $propertyParamId = intval($arPropQuery['PROPERTY_ID']);
                $arPropertiesSmartFilter['IDS'][$propertyParamId] = $propertyParamId;
                $arPropertiesSmartFilter['ITEMS'][$propertyParamId]['PARAMS'] = $arPropQuery;
            }

            return $arPropertiesSmartFilter;
        }
        else
        {
            return false;
        }
    }

    /* Метод получает ID ид торговых предложений на основе переданных ID товаров
     *
     * @PARAM array $elementsIds  - числовой массив id товаров
     * @PARAM int numberSkuProperty - id свойства привязки Sku
     * @PARAM array/false arIblockIds - массив id информационных блоков (каталога и торговых предложений)
     *
     * @RETURN
    */
    public static function getElementsOffersSmartFilter($elementsIds,$numberSkuProperty,$arIblockIds)
    {
        if (!empty($elementsIds) && !empty($numberSkuProperty) && !empty($arIblockIds))
        {
            //Выбираем связанные торговые предложения переданных товаров
            $parametersQueryGetOffers = array(
                'select' => array('ID'),
                'filter' => array(
                    //фильтрация по свойству привязка торговых предложений
                    '=PROPERTY.IBLOCK_PROPERTY_ID' => $numberSkuProperty,
                    '=PROPERTY.VALUE' => $elementsIds,
                    '=ACTIVE' => 'Y',
                    '=IBLOCK_ID' => $arIblockIds
                ),
                'runtime' => array(
                    new Entity\ReferenceField('PROPERTY',
                        '\IBlockElementPropertyTable',
                        array('=this.ID' => 'ref.IBLOCK_ELEMENT_ID'),
                        array('join_type' => 'INNER')
                    ),
                )
            );

            $resultElementOffers = \Bitrix\Iblock\ElementTable::getList($parametersQueryGetOffers);
            while ($arOffers = $resultElementOffers->fetch()) {
                $elementsIds[] =  $arOffers['ID'];
            }
            return $elementsIds;
        }
        else
        {
            return false;
        }
    }

    /* Метод получает диапазон цен на основе преданных ID товаров
     *
     * @PARAM array $elementsIds - массив id товаров
     * @PARAM int $priceType - ID типа цены
     *
     * @RETURN array/false $arPriceCheck  - массив результатов проверки и нахождения диапазона цен
     * с ключами MIN_PRICE, MAX_PRICE и ELEMENTS_IDS - массив элементов с актуальными ценами и кол-вом
    */
    public static function getPriceDiapasone($elementsIds,$priceType)
    {
        if (!empty($elementsIds) && is_array($elementsIds))
        {
            //Выбираем цены по элементам
            $queryPriceElements = array(
                'select' => array("ID","PRICE.PRICE"),
                'filter' => array(
                    '=ID' => $elementsIds,
                    '>QUANTITY' => 0,
                    '=PRICE.CATALOG_GROUP_ID' => $priceType,
                    '>PRICE.PRICE' => 0
                ),
                'group'  => array('ID'),
                'runtime' => array(
                    new Entity\ReferenceField('PRICE',
                        'CatalogPriceTable',
                        array('=this.ID' => 'ref.PRODUCT_ID'),
                        array('join_type' => 'INNER')
                    ),
                ),
            );

            $resultQueryPriceElements = \Bitrix\Catalog\ProductTable::getList($queryPriceElements);
            $minPriceElements = 0;
            $maxPriceElements = 0;

            while ($arPriceElement = $resultQueryPriceElements->fetch())
            {
                $priceElement = round($arPriceElement['CATALOG_PRODUCT_PRICE_PRICE']);
                //Вборка и поиск минимальных и максимальных цен
                if ($minPriceElements === 0)
                {
                    $minPriceElements = $priceElement;
                }
                if ($minPriceElements > $priceElement && $priceElement > 0)
                {
                    $minPriceElements = $priceElement;
                }
                if ($maxPriceElements < $priceElement)
                {
                    $maxPriceElements = $priceElement;
                }
                //Сохраняем ID найденых товаров, для исключения выборки по свойствам
                $arPriceCheck['ELEMENTS_IDS'][] = $arPriceElement['ID'];
            }

            $arPriceCheck['MIN_PRICE'] = $minPriceElements;
            $arPriceCheck['MAX_PRICE'] = $maxPriceElements;

            return $arPriceCheck;
        }
        else
        {
            return false;
        }
    }

    /**
     * Method returns array of properties with their values
     *
     * @param array $elementsIds Product IDs array
     * @param array $propertiesIds Properties IDs array that used in filter
     * @param array $arPropertiesParam Properties parameters array (keys are properties IDs)
     *
     * @return array $arPropertiesParam
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getPropertiesValueForElements($elementsIds, $propertiesIds, $arPropertiesParam)
    {
        $parametersQueryPropsValue = array(
            'select' => array('IBLOCK_PROPERTY_ID', 'IBLOCK_ELEMENT_ID', 'VALUE', "PROPERTY_LIST.VALUE", "PROPERTY.USER_TYPE"),
            'filter' => array(
                '=IBLOCK_PROPERTY_ID' => $propertiesIds,
                '=IBLOCK_ELEMENT_ID' => $elementsIds,
            ),
            'group'  => array('IBLOCK_PROPERTY_ID', 'IBLOCK_ELEMENT_ID')
        );

        $resultQueryPropsValue = \IBlockElementPropertyTable::getList($parametersQueryPropsValue);

        while ($arPropsIdsField = $resultQueryPropsValue->fetch())
        {
            $arPriceElement = array();
            $propertyId = intval($arPropsIdsField['IBLOCK_PROPERTY_ID']);

            $propertyType = "";
            $propertyType = $arPropertiesParam[$propertyId]['PARAMS']['IBLOCK_SECTION_PROPERTY_PROPERTY_PROPERTY_TYPE'];
            //Выбираем доступные свойства заказа и их значения
            switch ($propertyType){
                case "L":
                    $arPropertiesParam[$propertyId]['VALUES_NUM'][$arPropsIdsField['VALUE']] = $arPropsIdsField['VALUE'];
                    $arPropertiesParam[$propertyId]['VALUES'][$arPropsIdsField['VALUE']] = $arPropsIdsField['I_BLOCK_ELEMENT_PROPERTY_PROPERTY_LIST_VALUE'];
                    break;
                case "E":
                    $propertyIntvalVlaueID = intval(trim($arPropsIdsField['VALUE']));
                    $arPropertiesParam[$propertyId]['VALUES'][$propertyIntvalVlaueID] = $propertyIntvalVlaueID;
                    $arPropertiesParam[$propertyId]['VALUES_NUM'][$propertyIntvalVlaueID] = $propertyIntvalVlaueID;
                    break;
                case "S":
                    $arPropertiesParam[$propertyId]['VALUES'][$arPropsIdsField['VALUE']] = $arPropsIdsField['VALUE'];
                    $arPropertiesParam[$propertyId]['VALUES_NUM'][$arPropsIdsField['VALUE']] = $arPropsIdsField['VALUE'];
                    break;
            }
            //Создаем контрольную сумму на основе id значения свойства
            $newCsrValue = self::prepareFilterProperty($arPropsIdsField['VALUE']);
            $arPropertiesParam[$propertyId]['VALUES_CRC'][$arPropsIdsField['VALUE']] = $newCsrValue;

            $arPropertiesParam['VALUES_CRC'][$propertyId][$arPropsIdsField['VALUE']] = $newCsrValue;
        }
        $arPropertiesParam['ITEMS'] = $arPropertiesParam;

        return $arPropertiesParam;
    }

    /* Метод переводит данные в Html безопасный вид и создает контрольную сумму на основе переданных данных
     *
     * @PARAM mixed $idValueProp - id значения или само значение свойства
     *
     * @RETURN int $newValueProp - контрольная сумма значения
    */
    public static function prepareFilterProperty($idValueProp)
    {
        $idValueProp = htmlspecialcharsbx($idValueProp);
        $newValueProp = abs(crc32(htmlspecialcharsbx($idValueProp)));

        return intval($newValueProp);
    }

    /* Метод возвращает параметры относящиеся к фильтру из request
     *
     * @PARAM obj/array $request - объект или массив $_REQUEST
     * @PARAM string $prefixFilter - префикс по которому ищутся значения
     * @PARAM array $arProperties - массив свойств фильтрации с контрольными суммами
     *
     * @RETURN array/boolean - возвращает массив параметров фильтра
     * или false если таких параметров нет
    */
    public static function getRequestParamsFilter ($request, $prefixFilter, $arPropertiesCrc, $arPropsParams)
    {
        if (!$request || !$prefixFilter)
        {
            return false;
        }
        //Разбираем массив фильтра
        foreach ($request as $keyRequest => $valueRequest)
        {
            if (strpos($keyRequest,$prefixFilter) !== false)
            {
                $newGetDataKey = str_replace($prefixFilter,"",$keyRequest);
                if ($newGetDataKey === "PRICE_MIN" || $newGetDataKey === "PRICE_MAX")
                {
                    if ($newGetDataKey ===  "PRICE_MIN")
                    {
                        $arResultFilter['FilterArray'][">=CATALOG_PRICE_1"] = $valueRequest;
                        $arResultFilter['CHECKED']['PRICE_MIN'] = $valueRequest;
                    } elseif ($newGetDataKey ===  "PRICE_MAX")
                    {
                        $arResultFilter['FilterArray']["<=CATALOG_PRICE_1"] = $valueRequest;
                        $arResultFilter['CHECKED']['PRICE_MAX'] = $valueRequest;
                    }
                } else {
                    $arValueExplode = explode('_',$newGetDataKey);
                    $originalIdValueProps = "";
                    foreach ($arPropertiesCrc[$arValueExplode[0]] as $keyValueCrc => $valueCrc)
                    {
                        if (intval($arValueExplode[1]) === intval($valueCrc))
                        {
                            $originalIdValueProps = $keyValueCrc;
                            if ($originalIdValueProps && $arValueExplode[0]) {
                                $arProperties[$arValueExplode[0]][] = $originalIdValueProps;
                            }
                        }
                    }
                    $arResultFilter['CHECKED'][$arValueExplode[0]][$originalIdValueProps] = $originalIdValueProps;
                }
            }
        }
        foreach ($arProperties as $keyProps => $valueProps) {
            if (intval($arPropsParams['ITEMS'][$keyProps]['PARAMS']['IBLOCK_SECTION_PROPERTY_PROPERTY_IBLOCK_ID']) === 4) {
                if (count($valueProps) > 1) {
                    $arResultFilter['FilterArray']['OFFERS']['=PROPERTY_'.$keyProps] = $valueProps;
                } else {
                    $arResultFilter['FilterArray']['OFFERS']['=PROPERTY_'.$keyProps] = $valueProps[0];
                }
            } else {
                if (count($valueProps) > 1) {
                    $arResultFilter['FilterArray']['=PROPERTY_'.$keyProps] = $valueProps;
                } else {
                    $arResultFilter['FilterArray']['=PROPERTY_'.$keyProps] = $valueProps[0];
                }

            }
        }

        return $arResultFilter;
    }
}