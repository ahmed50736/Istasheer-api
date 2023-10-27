<?php

namespace App\Http\Controllers;

use App\Services\CaseCategoryService;
use App\helpers\ErrorMailSending;
use App\helpers\ApiResponse;
use Exception;
use App\Http\Requests\SubCategory;
use App\Http\Requests\UpdateSubCategory;
use App\Models\CaseCategory;
use App\Models\CaseSubCategory;
use Illuminate\Http\JsonResponse;



class CategoryController extends Controller
{
    private $categoryService;

    public function __construct(CaseCategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * create sub category
     * @param SubCategory $request
     * @param CaseCategoryService $caseCategory
     * @return JsonResponse
     */
    public function createCaseSubcategory(SubCategory $request)
    {
        $data = $request->validated();
        try {
            $subCategory = $this->categoryService->createSubCategory($data);
            return ApiResponse::sucessResponse(201, $subCategory, trans('messages.create_sub_category'));
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * get category list
     * @return JsonResponse
     */
    public function getCategoryList(): JsonResponse
    {
        $categoryList = CaseCategory::all();
        return ApiResponse::sucessResponse(200, $categoryList);
    }

    /**
     * update sub category
     * @param UpdateSubCategory $request
     * @return JsonResponse
     */
    public function updateSubCategory(UpdateSubCategory $request): JsonResponse
    {
        try {
            $updateData = $request->validated();

            $updateData = $this->categoryService->updateSubCategoryData($updateData);

            return ApiResponse::sucessResponse(201, $updateData, trans('messages.update_sub_category'));
        } catch (Exception $e) {

            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }

    /**
     * Delete Sub Category
     * @param $subCategoryID
     * @return JsonResponse
     */
    public function deleteSubCategory(string $subCategoryID): JsonResponse
    {
        try {

            $subCategoryCheck = CaseSubCategory::where('id', $subCategoryID)->first();

            if ($subCategoryCheck) {

                $subCategoryCheck->delete();

                return ApiResponse::sucessResponse(200, [], trans('messages.delete_sub_category'));
            } else {
                return ApiResponse::errorResponse(400, trans('messages.not_found_sub_category'));
            }
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
            return ApiResponse::serverError();
        }
    }
}
