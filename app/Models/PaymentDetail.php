<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\UUID;
use App\Mail\MyfatoorahDevTestMail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Mail;

class PaymentDetail extends Model
{
    use HasFactory, UUID, SoftDeletes;

    protected $table = 'payment_details';

    protected $fillable = [
        'user_id',
        'case_id',
        'extra_service_id',
        'invoice_id',
        'transaction_id',
        'transection_status',
        'transection_date',
        'gateway_refarence_id',
        'amount'
    ];

    public function user(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function caseDetails(){
        return $this->hasOne(law_case::class, 'id', 'case_id');
    }

    public function extraService(){
        return $this->hasOne(extraService::class, 'id', 'extra_service_id');
    }

    /**
     * Storing payment details
     * @param object $data
     * @return void
     */
    public function storePaymentDetails(object $paymentData): void
    {
        $userDetails = User::where('email', $paymentData->CustomerEmail)->orwhere('phone_no',substr($paymentData->CustomerMobile,1))->first();
        
        //prepare data for insert
        $data = [];
        $data['user_id'] = $userDetails->id;
        $data['case_id'] = $paymentData->CustomerReference;
        $data['extra_service_id'] = isset($paymentData->ExtraServiceId) ? $paymentData->ExtraServiceId : null;
        $data['invoice_id'] = $paymentData->InvoiceId;
        $data['transaction_id'] = $paymentData->focusTransaction->TransactionId;
        $data['transection_status'] = $paymentData->InvoiceStatus;
        $data['transection_date'] = $paymentData->focusTransaction->TransactionDate;
        $data['gateway_refarence_id'] = $paymentData->focusTransaction->ReferenceId;
        $data['amount'] = $paymentData->InvoiceValue;

        //creating payment details
        self::create($data);
    }
}
