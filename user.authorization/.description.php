<?php
use \Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

$arComponentDescription = array(
	"NAME" => Loc::getMessage("IBLOCK_COMPONENT_NAME"),
	"DESCRIPTION" => Loc::getMessage("IBLOCK_COMPONENT_DESCRIPTION"),
	"ICON" => "images/cat_all.gif",
	"CACHE_PATH" => "Y",
	"SORT" => 90,
	"PATH" => array(
		"ID" => "Energo",
		"CHILD" => array(
			"ID" => "eshop_catalog",
			"NAME" => Loc::getMessage("T_IBLOCK_DESC_CATALOG"),
			"SORT" => 30,
		)
	),
);