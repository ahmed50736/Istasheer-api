<?php

namespace App\Services;

use App\helpers\DirtyValueChecker;
use App\Models\Guideline;

class GuidelineService
{

    /**
     * create guideline
     * @param array $requestData
     * @return object
     */
    public static function storeGuideline(array $requestData): object
    {
        unset($requestData['video']);

        if (isset($requestData['id'])) {
            return self::updateGuideline($requestData);
        } else {
            return Guideline::create($requestData);
        }
    }

    /**
     * update guideline
     * @param array $requestData
     * @return object
     */
    private static function updateGuideline(array $requestData): object
    {
        $guideline = Guideline::where('id', $requestData['id'])->first();
        if (DirtyValueChecker::dirtyValueChecker($guideline, $requestData)) {
            $guideline->update($requestData);
            $guideline->refresh();
        }

        return $guideline;
    }
}
