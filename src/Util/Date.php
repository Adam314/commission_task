<?php

namespace Adam314\CommissionTask\Util;

class Date
{
    /**
     * For any given date returns datetime objects for the surrounding week
     *
     * @param \DateTime $date
     * @return \DateTime[]
     */
    public static function getWeekBoundaries(\DateTimeInterface $date): array
    {
        $monday = clone $date;
        $sunday = clone $date;

        $weekday = intval($date->format('w'));
        if ($weekday === 0) {
            $weekday = 7;
        }

        $monday->modify(sprintf('-%d days', $weekday - 1));
        $sunday->modify(sprintf('+%d days', 7 - $weekday));

        return [$monday, $sunday];
    }
}
