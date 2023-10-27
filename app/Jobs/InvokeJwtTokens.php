<?php

namespace App\Jobs;

use App\helpers\ErrorMailSending;
use App\Models\User;
use App\Models\UserToken;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tymon\JWTAuth\Contracts\Providers\JWT as ProvidersJWT;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;
use Tymon\JWTAuth\JWTAuth as JWTAuthJWTAuth;

class InvokeJwtTokens implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {
            $tokens = UserToken::where('user_id', $this->userId)->get();

            foreach ($tokens as  $token) {
                $ValidateToken = new \Tymon\JWTAuth\Token($token->token);
                JWTAuth::manager()->invalidate($ValidateToken, $forever = true);

                $token->delete();
            }
        } catch (JWTException $e) {
            ErrorMailSending::sendingErrorMail($e);
        }
    }
}
