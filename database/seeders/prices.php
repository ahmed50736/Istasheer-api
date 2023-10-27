<?php

namespace Database\Seeders;

use App\Models\pricelist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class prices extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {       
        pricelist::create(['id'=>Str::uuid(),'name'=>'Compliant & Report','price'=>20,'type'=>1]);
        pricelist::create(['id'=>Str::uuid(),'name'=>'Consulation memo','price'=>20,'type'=>1]);
        pricelist::create(['id'=>Str::uuid(),'name'=>'Defense Memo (Court or expert)','price'=>30,'type'=>1]);
        pricelist::create(['id'=>Str::uuid(),'name'=>'Drafting Contact','price'=>30,'type'=>1]);
    }
}
