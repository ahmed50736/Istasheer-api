<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AboutUpdateRequest;
use Exception;
use App\helpers\ErrorMailSending;
use App\helpers\ApiResponse;
use App\Http\Resources\AboutResource;
use App\Models\aboutus;
use Illuminate\Http\JsonResponse;

class AboutUsController extends Controller
{
    private $aboutus;

    public function __construct(aboutus $aboutus)
    {
        $this->aboutus = $aboutus;
    }

    /**
     * About Update
     * @authenticated
     * @group Admin
     * @param AboutUpdateRequest $request
     * @return JsonResponse
     */
    public function aboutUpdate(AboutUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            $checkAbout = $this->aboutus::where('id', $data['id'])->first();
            $checkAbout->details = $data['details'];
            $checkAbout->save();
            return ApiResponse::sucessResponse(200, new AboutResource($checkAbout), trans('messages.about_update'));
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }

    /**
     * About Us
     * @group setting
     * @urlParam user string required The user type. Possible values: user, attorney.
     * @return JsonResponse
     */
    public function aboutUs(string $user): JsonResponse
    {
        try {
            $lang = app()->getLocale();
            if ($lang == 'ar') {
                $lang = 2;
            } else {
                $lang = 1;
            }
            if ($user == 'user') {
                $usr = 3;
            } else {
                $usr = 2;
            }
            $data = $this->aboutus::select('id', 'details')->where('user_type', $usr)->where('language_type', $lang)->first();
            return ApiResponse::sucessResponse(200, $data);
        } catch (Exception $e) {
            ErrorMailSending::sendErrorMailToDev($e->getMessage(), $e->getFile(), $e->getLine());
            return ApiResponse::serverError();
        }
    }
}
