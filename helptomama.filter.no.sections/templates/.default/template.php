<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Page\Asset;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/js/jquery.ui.mouse.js');
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/js/jquery.ui.slider.js');
Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/css/jquery-ui-1.10.3.custom-1512982892020.css');

$this->setFrameMode(true);

$this->SetViewTarget("ShowFilter");

//Получаем тикущий url адрес страницы
$requestObject = Application::getInstance()->getContext()->getRequest();
$curPageAdress = $requestObject->getRequestedPage();
$curPageAdress = str_replace("index.php", "", $curPageAdress);

$arJs["SEF_SET_FILTER_URL"] = $curPageAdress;
?>

    <form name="<?=$arResult["FILTER_NAME"] . "_form" ?>" action="<?=$curPageAdress;?>" method="get">
        <div class="catalog-filter">
            <div class="text-right">
                <input type="submit" id="del_filter" name="del_filter" value="<?= Loc::getMessage("CT_BCSF_DEL_FILTER") ?>" />
                <a href="javascript:void(0);" id="dels_filter"><?= Loc::getMessage("CT_BCSF_DEL_FILTER") ?></a>
                <script>
                    $('#dels_filter').click(function () {
                        $('#del_filter').click();
                    });
                </script>
            </div>

            <? foreach ($arResult['PROPERTIES']['ITEMS'] as $keyProp => $valueProp) { ?>
                <? if (count($valueProp['VALUES']) <= 0) { continue; } ?>
                <? switch($valueProp['PARAMS']['IBLOCK_SECTION_PROPERTY_PROPERTY_PROPERTY_TYPE']):
                    //Вывод свойств и значений типа список
                    case 'L':?>
                        <? $valueProp['VALUES'] = array_unique($valueProp['VALUES']); ?>
                        <div class="lvl1"><b><span class="showchild"><?=$valueProp['PARAMS']['IBLOCK_SECTION_PROPERTY_PROPERTY_NAME'];?></span></b></div>
                        <ul id="ul_<?=$keyProp;?>" <? if (count($valueProp["VALUES"]) > 10) : ?>class="cut"<? endif; ?>>
                            <?foreach ($valueProp['VALUES'] as $keyValue => $value) {?>
                                <? if ($value === "") continue; ?>
                                <li class="lvl2">
                                    <input type="checkbox" value="Y" name="<?=$arParams['NAME_FILTER'];?>_<?=$keyProp;?>_<?=$valueProp['VALUES_CRC'][$keyValue];?>" id="LIST_<?=$keyValue;?>" <?=(!empty($arResult["CHECKED_PROPERTIES"][$keyProp][$keyValue]))? 'checked="checked"' : '' ?> class="CheckboxFilter"/>
                                    <label style="width:65px" for="LIST_<?=$keyValue?>"><?=$value;?></label>
                                </li>
                            <?}?>
                        </ul>
                        <?break;?>
                        <?
                    // Вывод свойств привязка к элементу
                    case 'E':?>
                        <div class="lvl1"><b><span class="showchild"><?=$valueProp['PARAMS']['IBLOCK_SECTION_PROPERTY_PROPERTY_NAME'];?></span></b></div>
                        <ul id="ul_<?=$keyProp;?>" <? if (count($valueProp["VALUES"]) > 10) : ?>class="cut"<? endif; ?>>
                            <?foreach ($valueProp['VALUES'] as $keyValue => $value) {?>
                                <? if ($value === "") continue; ?>
                                <li class="lvl2">
                                    <input type="checkbox" value="Y" name="<?=$arParams['NAME_FILTER'];?>_<?=$keyProp;?>_<?=$valueProp['VALUES_CRC'][$keyValue];?>" id="E_<?=$keyValue;?>" <?=(!empty($arResult["CHECKED_PROPERTIES"][$keyProp][$keyValue]))? 'checked="checked"' : '' ?> class="CheckboxFilter"/>
                                    <label style="width:65px" for="E_<?=$keyValue?>"><?=$valueProp['VALUES_HTML'][$keyValue];?></label>
                                </li>
                            <?}?>
                        </ul>
                        <? break;?>
                        <?
                    //Вывод свойств типа строка
                    case 'S': ?>
                        <div class="lvl1"><b><span class="showchild"><?=$valueProp['PARAMS']['IBLOCK_SECTION_PROPERTY_PROPERTY_NAME'];?></span></b></div>
                        <ul id="ul_<?=$keyProp;?>" <? if (count($valueProp["VALUES"]) > 10) : ?>class="cut"<? endif; ?>>
                            <? ksort($valueProp['VALUES']);?>
                            <?foreach ($valueProp['VALUES'] as $keyValue => $value) {?>
                                <? if ($value === "") continue;
                                if ($value === "Y") { $value = "Да"; }
                                if ($value === "N") { $value = "Нет"; } ?>


                                <li class="lvl2">
                                    <input type="checkbox" value="Y" name="<?=$arParams['NAME_FILTER'];?>_<?=$keyProp;?>_<?=$valueProp['VALUES_CRC'][$keyValue];?>" id="STR_<?=$keyValue;?>" <?=(!empty($arResult["CHECKED_PROPERTIES"][$keyProp][$keyValue]))? 'checked="checked"' : '' ?> class="CheckboxFilter"/>
                                    <label style="width:65px" for="STR_<?=$keyValue?>"><?= !empty($valueProp['VALUES_HTML'][$value]) ? $valueProp['VALUES_HTML'][$value] : $value; ?></label>
                                </li>
                            <?}?>
                        </ul>
                        <? break;?>

                    <? endswitch; ?>

                <div class="clearfix"></div>
                <? if (count($valueProp['VALUES']) > 10) : ?>
                    <div class="open-title" id="open-title_<?=$keyProp?>" attr-id="<?=$keyProp ?>"><?= Loc::getMessage("CT_BCSF_SHOW_ALL"); ?></div>
                <?endif; ?>
                <div class="cline2"></div>
            <? }?>

            <? if ($arResult['PROPERTIES']['MIN_PRICE'] !== $arResult['PROPERTIES']['MAX_PRICE'] && (!empty($arResult['PROPERTIES']['MIN_PRICE']) || !empty($arResult['PROPERTIES']['MAX_PRICE']))) {?>
                <?
                ($arResult['CHECKED_PROPERTIES']['PRICE_MIN'])? $requestValueMin = $arResult['CHECKED_PROPERTIES']['PRICE_MIN']: $requestValueMin = $arResult['PROPERTIES']['MIN_PRICE'];
                ($arResult['CHECKED_PROPERTIES']['PRICE_MAX'])? $requestValueMax = $arResult['CHECKED_PROPERTIES']['PRICE_MAX']: $requestValueMax = $arResult['PROPERTIES']['MAX_PRICE'];

                ?>

                <div class="lvl1"><b><span class="showchild">Розничная цена</span></b></div>
                <div id="ul_<? echo $arItem["ID"] ?>" class="num-filter">
                    <? echo Loc::getMessage("CT_BCSF_FILTER_FROM") ?>
                    <input class="min-price" type="text" name="<?=$arParams['NAME_FILTER'];?>_PRICE_MIN" id="arrFilter_P1_MIN" value="<?=$requestValueMin;?>" size="5"
                           placeholder="<?=$arResult['PROPERTIES']['MIN_PRICE'];?>"/>
                    <? echo Loc::getMessage("CT_BCSF_FILTER_TO") ?>
                    <input class="max-price" type="text" name="<?=$arParams['NAME_FILTER'];?>_PRICE_MAX" id="arrFilter_P1_MAX" value="<?=$requestValueMax;?>" size="5"
                           placeholder="<?=$arResult['PROPERTIES']['MAX_PRICE'];?>"/>
                    <div class="slider-range" id="slider-arrFilter_P1_MIN"  style="margin:7px auto 8px"></div><div class="slider-range" id="slider-arrFilter_P1_MIN"  style="margin:7px auto 8px"></div>


                    <script>
                        $(function () {
                            var minprice = <?=CUtil::JSEscape($arResult['PROPERTIES']['MIN_PRICE'])?>,
                                maxprice = <?=CUtil::JSEscape($arResult['PROPERTIES']['MAX_PRICE'])?>,
                                requestValueMin  = <?=CUtil::JSEscape($requestValueMin)?>,
                                requestValueMax  = <?=CUtil::JSEscape($requestValueMax)?>;


                            $("#slider-arrFilter_P1_MIN").slider({
                                range: true,
                                min: minprice,
                                max: maxprice,
                                values: [ requestValueMin, requestValueMax],
                                slide: function (event, ui) {
                                    $("#arrFilter_P1_MIN").val(ui.values[0]);
                                    $("#arrFilter_P1_MAX").val(ui.values[1]);
                                }
                            });

                            $("#max-price-arrFilter_P1_MIN").text(maxprice);
                            $("#min-price-arrFilter_P1_MAX").text(minprice);
                        });
                    </script>

                </div>
                <div class="cline2"></div>
            <? } ?>

            <div class="buttons">
                <input type="hidden" name="FilterApply" value="Y"/>
                <button class="btn btn-themes btn-apply ButtonFilter" type="submit" name="Low" value="Y"><?= Loc::getMessage("CT_BCSF_DO_FILTER"); ?></button>
                <div style="display:none;">
                    <input class="btn btn-themes btn-apply" type="submit" id="set_filter" name="set_filter" value="<?= Loc::getMessage("CT_BCSF_DO_FILTER") ?>"/>
                </div>
                <input class="btn btn-link" type="submit" id="del_filter" name="del_filter" value="<?= Loc::getMessage("CT_BCSF_DEL_FILTER") ?>"
                />
            </div>
            <div class="clb"></div>
            <div class="modef" class="bx-filter-popup-result" id="modef" style="display: none;">
                <span class="PopupFilter"><?=Loc::getMessage("CT_BCSF_FILTER_COUNT"); ?></span>
            </div>
        </div>
    </form>

    <script>
        var smartFilter = new JCSmartFilter('<?=$curPageAdress;?>', 'VERTICAL', <?=CUtil::PhpToJSObject($arJs)?>);

        $('.open-title').on('click', function () {
            var id = $(this).attr('attr-id');

            if ($('#ul_' + id).hasClass("active")) {
                $('#ul_' + id).removeClass('active').addClass('cut', 500, "easeInSine");
                $('#open-title_' + id).html('Показать еще');
            } else {
                $('#ul_' + id).removeClass('cut').addClass('active', 500, "easeOutSine");
                $('#open-title_' + id).html('Скрыть');
            }
            ;
        });
        function modef_block_position(selector) {
            $('.modef').css("top", selector[0].offsetTop + "px");
            var modef = BX('modef');
            if (modef.style.display == 'none')
                modef.style.display = 'block';
        }
        $(document).on('change','.catalog-filter input', function () {
            modef_block_position($(this));
        });
        $(document).on('click','.PopupFilter',function(){
            $('.ButtonFilter').click();
        });

    </script>


<? $this->EndViewTarget();






