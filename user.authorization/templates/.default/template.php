<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Page\Asset;

Asset::getInstance()->addJs('/local/js_plugins/jquery.validate2/jquery.validate.min.js'); ?>

<div class="authBlock">

    <h4><?= Loc::getMessage('CUS_AUTH_PHONE_H2'); ?></h4>
    <div class="infoText"></div>

    <form name="authPhone" method="post" target="_top" action="<?= $_SERVER['REQUEST_URI'] ?>" id="authPhone">

        <div class="inputBlockAuth">

            <label><?= Loc::getMessage('CUS_AUTH_PHONE_LABEL_PHONE'); ?></label>
            <span class="firstPartPhone"><?= Loc::getMessage('CUS_AUTH_PHONE_FIRST_CODE'); ?></span>
            <input name="phoneUser" class="phoneUser" type="text" maxlength="10"
                   onkeypress="return event.charCode >= 48 && event.charCode <= 57"/>

            <p class="phoneExample"><?= Loc::getMessage('CUS_AUTH_PHONE_EXAMPLE'); ?></p>

            <p><?= Loc::getMessage('CUS_AUTH_PHONE_INFO_REG'); ?></p>

            <div class="captchaBlock">

            </div>

            <div class="buttonBlock">
                <div class="button authGetCode disabled"><?= Loc::getMessage('CUS_AUTH_PHONE_BUTTON_GET_CODE'); ?></div>
            </div>

        </div>

        <div class="inputBlockCode">

            <label><?= Loc::getMessage('CUS_AUTH_PHONE_ENTER_CODE'); ?></label>
            <input name="phoneCode" class="phoneCode" type="text" maxlength="6" placeholder="_ _ _ _ _ _"/>

            <!-- <p class="repeatCode"><?= Loc::getMessage('CUS_AUTH_PHONE_REPEAT_SEND_CODE'); ?></p>-->
        </div>

        <div class="formPreloader"></div>
    </form>


</div>

