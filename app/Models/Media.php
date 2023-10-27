<?php

namespace App\Models;

use App\Http\Traits\UUID;
use App\Jobs\ProcessUploadedFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
//use Intervention\Image\Facades\Image;

class Media extends Model
{
    use HasFactory, UUID, SoftDeletes;

    protected $table = 'medias';

    protected $fillable = [
        'photo',
        'mediaable_id',
        'mediaable_type',
        'file_name',
        'details',
        'created_by',
        'updated_by',
        'deleted_by'
    ];


    public function mediaable()
    {
        return $this->morphTo();
    }

    public function getPhotoUrlAttribute()
    {
        return asset($this->photo);
    }

    /**
     * upload image
     * @param  $request reuest image file
     * @param string $path
     * @return string
     */
    public static function uploadFileToMedia($file, string $directory): string
    {
        $path = config('setting.media.path') . '/' . $directory;
        $fullName = time() . '.' . $file->getClientOriginalExtension();
        Storage::disk(config('setting.media.disc'))->putFileAs($path, $file, $fullName);
        return 'storage/' . $path . '/' . $fullName;
    }

    /**
     * Delete a file by its url
     * @param string $url
     * @param string $path
     * @param string $directory
     * @param string $disk
     * @return boolean
     */
    public static function deleteFileFromMedia(string $url, string $disk, string $filePath, string $directory): bool
    {
        $path = parse_url($url, PHP_URL_PATH);
        $filename = basename($path);
        return Storage::disk($disk)->delete($filePath . '/' . $directory . '/' . $filename);
    }


    /**
     * upload videos
     */
    public static function uploadVideo($file, string $directory): string
    {
        
        $path = config('setting.media.path') . '/' . $directory;

        $name = time(); //setting unique name with time
        $fileRealPath = $file->getRealPath();

        $fullName = $name . '.' . $file->getClientOriginalExtension();
         //Storage::disk(config('setting.media.disc'))->putFileAs($path, $file, $fullName);
        //ProcessUploadedFile::dispatch($fileRealPath, $path, $fullName);

        Storage::disk(config('setting.media.disc'))->putFileAs($path, $file, $fullName);
        return 'storage/' . $path . '/' . $fullName;
        
    }
}
