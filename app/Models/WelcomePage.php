<?php

namespace App\Models;

use App\Http\Resources\AboutResource;
use App\Http\Resources\FaqResource;
use App\Http\Resources\TermsResource;
use App\Http\Resources\WelcomePageResource;
use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Media;
use Symfony\Component\VarDumper\Cloner\Data;

class WelcomePage extends Model
{
    use HasFactory, UUID;

    protected $table = 'welcome_pages';

    protected $fillable = [
        'lang',
        'title',
        'description',
        'image',
        'user_type'
    ];

    public function media()
    {
        return $this->morphOne(Media::class, 'mediaable');
    }

    /**
     * getting data for user welcome page
     * @param string $userType [user,attorney] 
     * @return object
     */
    public function getWelcomeData(string $userType): object
    {
        $data = self::with('media')->where('user_type', $userType)->where('lang', app()->getLocale())->get();
        return WelcomePageResource::collection($data);
    }

    /**
     * create or update welcomepage data
     * @param array $data
     * @return object
     */
    public function createOrUpdatePageData(array $data): object
    {
        unset($data['image']);
        if (isset($data['id'])) {
            $getData = self::find($data['id']);
            $getData->update($data);
            $createData = $getData->refresh();
        } else {
            $createData = self::create($data);
        }
        return $createData;
    }

    /**
     * get application all setting data like about terms etc
     * @return array $data
     */
    public function getSettingPageData(): array
    {
        $lang = app()->getLocale();
        if ($lang == 'ar') {
            $lang = 2;
        } else {
            $lang = 1;
        }

        $about = aboutus::where('language_type', $lang)->get();
        $terms = terms::where('language_type', $lang)->get();
        $faq = Faq::where('language_type', $lang)->get();

        $data['about']['user'] = AboutResource::collection($about->where('user_type', 3));
        $data['about']['attorney'] = AboutResource::collection($about->where('user_type', 2));
        $data['terms']['user'] = TermsResource::collection($terms->where('user_type', 3));
        $data['terms']['attorney'] = TermsResource::collection($terms->where('user_type', 2));
        $data['faq']['user'] = FaqResource::collection($faq->where('user_type', 3));
        $data['faq']['attorney'] = FaqResource::collection($faq->where('user_type', 2));

        return $data;
    }
}
