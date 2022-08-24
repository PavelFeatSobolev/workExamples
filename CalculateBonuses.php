<?php

namespace BonusesProgram;

class CalculateBonuses
{
    protected array $config;

    protected int $sumFreeDelivery = 0;
    protected int $percentDiscount;

    public function __construct(array $config, array $userData)
    {
        $this->config = $config;
    }

    public function setFreeDelivery(int $value)
    {
        $this->sumFreeDelivery = $value;
    }

    protected function setPercentDiscount(int $value)
    {
        $this->percentDiscount = $value;
    }

    public function calculateWriteOff(object $basket, int $bonusesCount): array
    {
        $basketSum = $basket->getPrice();
        $basketItems = $basket->getOrderableItems();

        $freeDeliveryStep = $basketSum - $this->sumFreeDelivery;

        return $this->getItemBonuses($basketItems, $bonusesCount, $freeDeliveryStep);
    }

    protected function getItemBonuses(object $basketItems, int $userBonuses, int $freeDeliverySum) : array
    {
        $balance = $userBonuses;
        $sumDiscount = 0;
        $arBonusesItem = array();
        $stopCalculate = false;

        foreach ($basketItems as $item) {

            if (!$this->checkBasketItem($item)) continue;

            $discount = floor($item->getPrice() * ($this->percentDiscount / 100)) * $item->getQuantity();
            $discount = ($discount <= $balance) ? $discount : floor($balance);

            if ($this->config['notFreeDelivery'] === 'Y') {
                if (($sumDiscount + $discount) >= $freeDeliverySum) {
                    $discount = $freeDeliverySum - $sumDiscount;
                    $stopCalculate = true;
                }
            }

            $sumDiscount += $discount;
            $balance -= $discount;

            $arBonusesItem['items'][$item->getId()]['bonusesPay'] = $discount / $item->getQuantity();

            if ($stopCalculate) break;
        }
        $arBonusesItem['sumBonusesPay'] = $sumDiscount;

        return $arBonusesItem;
    }

    /**
     * Method check product on restriction
     *
     * @param object $obBasketItem
     *
     * @return bool
     */
    protected function checkBasketItem(object $obBasketItem): bool
    {
        $discount = $obBasketItem->getDiscountPrice();
        $priceType = intval($obBasketItem->getField('PRICE_TYPE_ID'));

        return (($discount > 0 && $this->config['notDiscount'] === 'Y') ||
            ($priceType === 2 && $this->config['notSocial'] === 'Y')
        )? false : true;
    }
}