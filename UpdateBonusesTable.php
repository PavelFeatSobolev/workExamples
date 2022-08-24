<?php

namespace BonusesProgram;

class UpdateBonusesTable extends BonusesTable
{
    /**
     * Method update user bonuses account
     *
     * @param int $elementId
     * @param array $arParamsAccount
     *
     * @return bool
     */
    public function updateBonusesUserAccount(int $elementId, array $arParamsAccount)
    {
        $element = self::getUserDataByUserId($this->userId);
        $update = $this->setElementsPropsValue();
        if ($update) {
            return $this->obElement->save();
        }
    }

    protected function setElementsPropsValue(): bool
    {
        if (is_object($this->obElement)) {
            $confProps = $this->config['user_props'];
            foreach ($confProps as $key => $value) {
                $this->obElement->$key->setValue($this->$key);
            }
            return true;
        }
        return false;
    }
}