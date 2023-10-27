<?php

namespace App\Jobs;

use App\helpers\ErrorMailSending;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Support\Facades\Log;

class ProcessUploadedFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $path,  $fileName,  $tmpPath;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($realPath, $path,  $fileName)
    {

        $this->path = $path;

        $this->fileName = $fileName;


        $this->tmpPath = $realPath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            // $destinationPath = storage_path("app/public/{$this->path}");


            //$moved = rename($this->tmpPath, "{$destinationPath}/{$this->fileName}");

            // Process the uploaded file
            Storage::disk(config('setting.media.disc'))->putFileAs($this->path, file_get_contents($this->tmpPath), $this->fileName);
        } catch (Exception $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }
}
