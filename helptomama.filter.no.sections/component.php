<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) { die(); }

define('BRAND_PROPERTY_ID', 5);

use \Bitrix\Main\Loader,
    \Bitrix\Main\Application;

$requestData = Application::getInstance()->getContext()->getRequest();

if ($requestData->getQuery('del_filter') == 'Y') {
    $curPageAdress = $requestData->getRequestedPage();
    $curPageAdress = str_replace('index.php', '', $curPageAdress);
    LocalRedirect($curPageAdress);
}

Loader::includeModule('iblock');

$arResult = array();

//Получаем список разделов на основе переданных ID товаров
if (!empty($arParams['ARRAY_ITEMS_IDS'])) {
    $cacheTimeSection = $arParams['CACHE_TIME_SECTION'];
    $cacheIdSection = serialize($arParams['ARRAY_ITEMS_IDS']);
    $cacheDirSection = '/custom_components/helptomama_filter_no_sections/list_section/';

    $cache = Bitrix\Main\Data\Cache::createInstance();
    if ($cache->initCache($cacheTimeSection, $cacheIdSection, $cacheDirSection)) {
        $arResult['SECTIONS'] = $cache->getVars();
    } elseif ($cache->startDataCache()) {
        $arResult['SECTIONS'] = array();

        $arResult['SECTIONS'] = $this->getSectionsIdsForElements($arParams['ARRAY_ITEMS_IDS'], $arParams['IBLOCK_ID_ELEMENTS']);

        if (empty($arResult['SECTIONS']['IDS'])) {
            $cache->abortDataCache();
        }
        $arResult['SECTIONS']['IDS'][0] = 0;

        $cache->endDataCache($arResult['SECTIONS']);
    }
}

//Получаем список свойств с отмеченной галкой "Показывать в умном фильтре"
if (!empty($arResult['SECTIONS']['IDS'])) {
    $cacheTimeProps = $arParams['CACHE_TIME_PROPERTIES'];
    $cacheIdProps = serialize($arResult['SECTIONS']['IDS']);
    $cacheDirProps = '/custom_components/helptomama_filter_no_sections/prop_section/';

    $cache = Bitrix\Main\Data\Cache::createInstance();
    if ($cache->initCache($cacheTimeProps, $cacheIdProps, $cacheDirProps)) {
        $arResult['PROPERTIES'] = $cache->getVars();
    } elseif ($cache->startDataCache()) {
        $arResult['PROPERTIES'] = $this->getPropertysSmartFiterToSections($arResult['SECTIONS']['IDS'], $arParams['PROPERTY_ID_NO_DISPLAY']);
        if (empty($arResult['PROPERTIES']['IDS'])) {
            $cache->abortDataCache();
        }

        $cache->endDataCache($arResult['PROPERTIES']);
    }
}

//Получаем значения свойств которые участвуют в фильтрации по всем переданным товарам
if (!empty($arResult['PROPERTIES']['IDS'])) {
    $cacheTimeElement = $arParams['CACHE_TIME_ELEMENTS'];
    $cacheIdProps = serialize($arResult['PROPERTIES']['IDS']) . serialize($arParams['ARRAY_ITEMS_IDS']);
    $cacheDirProps = '/custom_components/helptomama_filter_no_sections/element_props/';

    $cache = Bitrix\Main\Data\Cache::createInstance();
    if ($cache->initCache($cacheTimeProps, $cacheIdProps, $cacheDirProps)) {
        $arResult['PROPERTIES'] = $cache->getVars();
    } elseif ($cache->startDataCache()) {
        //Получаем торговые предложения связанные с товарами
        $newArItemsIds = $this->getElementsOffersSmartFilter(
            $arParams['ARRAY_ITEMS_IDS'],
            $arParams['PROPERTY_SKU_RELATION'],
            array($arParams['IBLOCK_ID_ELEMENTS'], $arParams['IBLOCK_ID_OFFERS'])
        );
        if (!empty($newArItemsIds) && is_array($newArItemsIds)) {
            $arParams['ARRAY_ITEMS_IDS'] = $newArItemsIds;
        }

        //Выбираем цены по элементам
        $arPriceCheck = $this->getPriceDiapasone($arParams['ARRAY_ITEMS_IDS'], $arParams['PRICE_ID_TYPE']);
        if (!empty($arPriceCheck) && $arPriceCheck !== false) {
            $arResult['PROPERTIES']['MIN_PRICE'] = $arPriceCheck['MIN_PRICE'];
            $arResult['PROPERTIES']['MAX_PRICE'] = $arPriceCheck['MAX_PRICE'];
            $newIdsElements = $arPriceCheck['ELEMENTS_IDS'];
        }
        if (!empty($newIdsElements)) {
            //Получаем значения найденых свойств по товарам
            $newArPropertiesParams = $this->getPropertiesValueForElements(
                $newIdsElements,
                $arResult['PROPERTIES']['IDS'],
                $arResult['PROPERTIES']['ITEMS']
            );
            if (!empty($newArPropertiesParams) && $newArPropertiesParams !== false) {
                $arResult['PROPERTIES']['ITEMS'] = $newArPropertiesParams['ITEMS'];
                $arResult['PROPERTIES']['VALUES_CRC'] = $newArPropertiesParams ['VALUES_CRC'];
            }
        }
        if (empty($arResult['PROPERTIES'])) {
            $cache->abortDataCache();
        }
        $cache->endDataCache($arResult['PROPERTIES']);
    }
}

//Если свойство "бренд" выбираем название из кастомного класса из уже закешированных данных
if (count($arResult['PROPERTIES']['ITEMS'][BRAND_PROPERTY_ID]['VALUES'])) {
    foreach ($arResult['PROPERTIES']['ITEMS'][BRAND_PROPERTY_ID]['VALUES'] as $brandCode) {
        $arBrand = \Local\Iblock\CustomHighloadBlocks::getHighloadBlockBrandProps($brandCode);
        $arResult['PROPERTIES']['ITEMS'][BRAND_PROPERTY_ID]['VALUES_HTML'][$brandCode] = $arBrand['NAME'];
    }
}

//Работаем с переданными параметрами Request и на основе их создаем глобальный масиив с параметрами фильтрации элементов,
// который будет доступен в комплексном компоненте catalog
$filterGetData = $requestData->getQueryList();

if ($requestData->getQuery('FilterApply') === 'Y') {
    global ${$arParams['NAME_FILTER']};
    ${$arParams['NAME_FILTER']} = array();

    $prefixFilter = $arParams['NAME_FILTER'] . "_";
    $resutArrayRequest = $this->getRequestParamsFilter($filterGetData, $prefixFilter, $arResult['PROPERTIES']['VALUES_CRC'], $arResult['PROPERTIES']);
    if (is_array($resutArrayRequest['FilterArray'])) {
        ${$arParams['NAME_FILTER']} = $resutArrayRequest['FilterArray'];
    }
    $arResult['CHECKED_PROPERTIES'] = $resutArrayRequest['CHECKED'];
}

$this->IncludeComponentTemplate();