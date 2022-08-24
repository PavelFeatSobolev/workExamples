<?php
return array(
    'iblockId' => 7,
    'moduleId' => 'custom.bonuses',
    'dateFormat' => 'Y-m-d',
    'cacheDir' => '/bonus_system/',
    'cacheTime' => 86000,
    'addBonusesInHoliday' => 'Y',
    'amountBonusesHoliday' => 200,
    'dataHoliday' => '',
    'addBonusesAtRegistration' => 'Y',
    'notFreeDelivery' => 'Y',
    'notDiscount' => 'Y',
    'notSocial' => 'Y',
    'userProps' => array(
        'userId' => 'USER_ID',
        'bonuses' => 'BONUSES',
        'orders' => 'ORDERS',
        'writeOff' => 'WRITE_OFF',
        'accrued' => 'ACCRUED',
        'dateWriteOff' => 'DATE_WRITE_OFF',
        'groupId' => 'GROUP_ID',
        'registrationDate' => 'REGISTRATION_DATE',
        'bonusesAtRegistration' => 'BONUSES_AT_REGISTRATION',
        'bonusesInHoliday' => 'BONUSES_IN_HOLIDAY',
        'holidayDate' => 'HOLIDAY_DATE',
    ),
    'bonusesGroups' => array(
        1 => array (
            'name' => 'Чемпион покупок',
            'image' => '',
            'discount' => 10,
            'ordersSum' => 9999
        ),
        2 => array (
            'name' => 'Кандидат в мастера покупок',
            'image' => '',
            'discountPercent' => 15,
            'ordersSum' => 19999
        ),
        3 => array (
            'name' => 'Мастер покупок',
            'image' => '',
            'discount' => 20,
            'ordersSum' => 49999
        ),
        4 => array (
            'name' => 'Заслуженный мастер покупок',
            'image' => '',
            'discountPercent' => 25,
            'ordersSum' => 99999
        )
    ),
    'lastGroupId' => 4,
);