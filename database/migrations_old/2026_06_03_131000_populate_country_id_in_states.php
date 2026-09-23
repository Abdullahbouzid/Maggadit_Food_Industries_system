<?php

use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        // إذا كانت هناك states بدون country_id، يمكن ربطها بأول دولة أو بدولة افتراضية
        // هذا مثال - قد تحتاج إلى تعديل حسب بيانات مشروعك

        // الحل 1: إذا كانت هناك دولة واحدة فقط، ربط جميع الأقاليم بها
        $countryCount = Country::count();
        if ($countryCount == 1) {
            $country = Country::first();
            State::whereNull('country_id')->update(['country_id' => $country->id]);
        }

        // الحل 2: إذا لم تكن هناك states بدون country_id، فلا تفعل شيء
        // (الأقاليم الجديدة ستحتاج إلى country_id عند الإنشاء)
    }

    public function down()
    {
        State::whereNotNull('country_id')->update(['country_id' => null]);
    }
};
