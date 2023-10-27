<?php

namespace App\helpers;

use App\Models\law_case;
use Illuminate\Support\Facades\DB;

class CaseHelper
{
  public static function createOrderNumber()
  {
    // $last_case = law_case::latest('create_time')->withTrashed()->first();
    $latestCode = DB::select(DB::raw('SELECT MAX(CAST(SUBSTRING(order_no, 7) AS UNSIGNED)) AS largest_order_number
                FROM law_cases'));

    if ($latestCode) {
      $code = $latestCode[0]->largest_order_number + 1;
      if ($code < 100) {
        if ($code < 10) {
          $new_code = '00' . $code;
        } else {
          $new_code = '0' . $code;
        }
      } else {
        $new_code = $code;
      }
      $caseCode = 'order-' . $new_code;
    } else {
      $caseCode = 'order-001';
    }
    return $caseCode;
    /* if ($last_case) {
      $final_code = $last_case->order_no;
      $code = substr($final_code, 6) + 1;
      if ($code < 100) {
        if ($code < 10) {
          $new_code = '00' . $code;
        } else {
          $new_code = '0' . $code;
        }
      } else {
        $new_code = $code;
      }
      $last_case_code = 'order-' . $new_code;
    } else {
      $last_case_code = 'order-001';
    } */
  }

  /**
   * Setting case type request
   * @param string $trype
   * @return int $type
   */
  public static function getCaseTypeOnCaseOrders(string $type): int
  {
    switch ($type) {
      case 'open':
        $type = 0;
        break;
      case 'closed':
        $type = 1;
        break;
      case 'new':
        $type = 3;
        break;
      default:
        $type = 0;
    }
    return $type;
  }

  /**
   * getting extra service order id
   * @param string $caseId
   * @return string
   */
  public static function createExtraServiceOrderNo(string $caseId): string
  {
    $caseInfo = law_case::where('id', $caseId)->with('extraServices')->first();
    $orderNumber = count($caseInfo->extraServices) + 1;
    return $caseInfo->order_no . '-' . 'Extra-' . $orderNumber;
  }
}
