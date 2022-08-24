<?php

class BonusesUserGroup
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Method return group params by group id
     *
     * @param int $groupId
     *
     * @return array
     */
    public function getGroupParams(int $groupId): array
    {
        return ($this->config['bonusesGroups'][$groupId]) ? $this->config['bonusesGroups'][$groupId] : array();
    }

    /**
     * Method get id bonuses group user by order sum
     *
     * @param int $sum
     *
     * @return int
     */
    public function getIdGroupBySumUserOrders(int $sum) : int
    {
        $id = 1;
        $lastGroup = $this->config['lastGroupId'];

        foreach ($this->config['bonusesGroups'] as $key => $value) {
            if ($key === $lastGroup || $value['ordersSum'] >= $sum) {
                $id = $key;
                break;
            }
        }
        return $id;
    }
}