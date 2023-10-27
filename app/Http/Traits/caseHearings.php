<?php
namespace App\Http\Traits;
use App\Models\hearings;
use Carbon\Carbon;

trait caseHearings{

    public function getHearingAttorney($attorneyId){
            $today=hearings::where('attorney_id',$attorneyId)->whereDate('date','=',Carbon::today())->get();
            $tommorow=hearings::where('attorney_id',$attorneyId)->whereDate('date','=',Carbon::tomorrow())->get();
            $week=hearings::where('attorney_id',$attorneyId)->whereBetween('date',[Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->get();

            return ['today'=>$today,'tommorow'=>$tommorow,'week'=>$week];
    }

    public function getAdminHearingList(){
        $today=hearings::whereDate('date','=',Carbon::today())->get();
        $tommorow=hearings::whereDate('date','=',Carbon::tomorrow())->get();
        $week=hearings::whereBetween('date',[Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->get();

        return ['today'=>$today,'tommorow'=>$tommorow,'week'=>$week];
    }
}