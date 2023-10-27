<?php

namespace App\helpers;

class DirtyValueChecker
{
    /**
     * checking dirty value on update model data
     * @param object $modelData
     * @param array $attibutes
     * @return bool
     */
    public static function dirtyValueChecker(object $modelData, array $attributes): bool
    {
        foreach ($attributes as $feildName => $value) {
            if ($modelData->$feildName !== $value) {
                $modelData->$feildName = $value;
            }
        }

        if ($modelData->isDirty()) {
            return true;
        } else {
            return false;
        }
    }
}
