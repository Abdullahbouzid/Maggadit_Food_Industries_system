<?php

return [
    'accepted' => 'يجب قبول حقل :attribute.',
    'accepted_if' => 'يجب قبول حقل :attribute عندما يكون :other بقيمة :value.',
    'active_url' => 'حقل :attribute ليس عنوان URL صحيحًا.',
    'after' => 'حقل :attribute يجب أن يكون تاريخًا بعد :date.',
    'after_or_equal' => 'حقل :attribute يجب أن يكون تاريخًا بعد أو يساوي :date.',
    'alpha' => 'حقل :attribute قد يحتوي على أحرف فقط.',
    'alpha_dash' => 'حقل :attribute قد يحتوي على أحرف وأرقام وشرطات وشرطات سفلية فقط.',
    'alpha_num' => 'حقل :attribute قد يحتوي على أحرف وأرقام فقط.',
    'array' => 'حقل :attribute يجب أن يكون مصفوفة.',
    'before' => 'حقل :attribute يجب أن يكون تاريخًا قبل :date.',
    'before_or_equal' => 'حقل :attribute يجب أن يكون تاريخًا قبل أو يساوي :date.',
    'between' => [
        'numeric' => 'حقل :attribute يجب أن يكون بين :min و :max.',
        'file' => 'حقل :attribute يجب أن يكون بين :min و :max كيلوبايت.',
        'string' => 'حقل :attribute يجب أن يكون بين :min و :max أحرف.',
        'array' => 'حقل :attribute يجب أن يحتوي بين :min و :max عناصر.',
    ],
    'boolean' => 'حقل :attribute يجب أن يكون صحيح أو خطأ.',
    'confirmed' => 'تأكيد حقل :attribute غير متطابق.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => 'حقل :attribute ليس تاريخًا صحيحًا.',
    'date_equals' => 'حقل :attribute يجب أن يكون تاريخًا يساوي :date.',
    'date_format' => 'حقل :attribute لا يطابق الصيغة :format.',
    'declined' => 'يجب رفض حقل :attribute.',
    'declined_if' => 'يجب رفض حقل :attribute عندما يكون :other بقيمة :value.',
    'different' => 'حقل :attribute و :other يجب أن يكونا مختلفين.',
    'digits' => 'حقل :attribute يجب أن يكون :digits أرقام.',
    'digits_between' => 'حقل :attribute يجب أن يكون بين :min و :max أرقام.',
    'dimensions' => 'حقل :attribute له أبعاد صورة غير صحيحة.',
    'distinct' => 'حقل :attribute له قيمة مكررة.',
    'email' => 'حقل :attribute يجب أن يكون عنوان بريد إلكتروني صحيح.',
    'ends_with' => 'حقل :attribute يجب أن ينتهي بأحد القيم التالية: :values.',
    'exists' => 'حقل :attribute المحدد غير موجود.',
    'file' => 'حقل :attribute يجب أن يكون ملف.',
    'filled' => 'حقل :attribute يجب أن يحتوي على قيمة.',
    'gt' => [
        'numeric' => 'حقل :attribute يجب أن يكون أكبر من :value.',
        'file' => 'حقل :attribute يجب أن يكون أكبر من :value كيلوبايت.',
        'string' => 'حقل :attribute يجب أن يكون أطول من :value أحرف.',
        'array' => 'حقل :attribute يجب أن يحتوي على أكثر من :value عناصر.',
    ],
    'gte' => [
        'numeric' => 'حقل :attribute يجب أن يكون أكبر من أو يساوي :value.',
        'file' => 'حقل :attribute يجب أن يكون أكبر من أو يساوي :value كيلوبايت.',
        'string' => 'حقل :attribute يجب أن يكون أطول من أو يساوي :value أحرف.',
        'array' => 'حقل :attribute يجب أن يحتوي على :value عناصر أو أكثر.',
    ],
    'image' => 'حقل :attribute يجب أن يكون صورة.',
    'in' => 'حقل :attribute المحدد غير صحيح.',
    'in_array' => 'حقل :attribute غير موجود في :other.',
    'integer' => 'حقل :attribute يجب أن يكون عددًا صحيحًا.',
    'ip' => 'حقل :attribute يجب أن يكون عنوان IP صحيح.',
    'ipv4' => 'حقل :attribute يجب أن يكون عنوان IPv4 صحيح.',
    'ipv6' => 'حقل :attribute يجب أن يكون عنوان IPv6 صحيح.',
    'json' => 'حقل :attribute يجب أن يكون سلسلة JSON صحيحة.',
    'lt' => [
        'numeric' => 'حقل :attribute يجب أن يكون أقل من :value.',
        'file' => 'حقل :attribute يجب أن يكون أقل من :value كيلوبايت.',
        'string' => 'حقل :attribute يجب أن يكون أقصر من :value أحرف.',
        'array' => 'حقل :attribute يجب أن يحتوي على أقل من :value عناصر.',
    ],
    'lte' => [
        'numeric' => 'حقل :attribute يجب أن يكون أقل من أو يساوي :value.',
        'file' => 'حقل :attribute يجب أن يكون أقل من أو يساوي :value كيلوبايت.',
        'string' => 'حقل :attribute يجب أن يكون أقصر من أو يساوي :value أحرف.',
        'array' => 'حقل :attribute يجب أن يحتوي على :value عناصر أو أقل.',
    ],
    'max' => [
        'numeric' => 'حقل :attribute قد لا يكون أكبر من :max.',
        'file' => 'حقل :attribute قد لا يكون أكبر من :max كيلوبايت.',
        'string' => 'حقل :attribute قد لا يكون أطول من :max أحرف.',
        'array' => 'حقل :attribute قد لا يحتوي على أكثر من :max عناصر.',
    ],
    'mimes' => 'حقل :attribute يجب أن يكون ملف من النوع: :values.',
    'mimetypes' => 'حقل :attribute يجب أن يكون ملف من النوع: :values.',
    'min' => [
        'numeric' => 'حقل :attribute يجب أن يكون على الأقل :min.',
        'file' => 'حقل :attribute يجب أن يكون على الأقل :min كيلوبايت.',
        'string' => 'حقل :attribute يجب أن يكون على الأقل :min أحرف.',
        'array' => 'حقل :attribute يجب أن يحتوي على الأقل :min عناصر.',
    ],
    'multiple_of' => 'حقل :attribute يجب أن يكون مضاعفًا لـ :value.',
    'not_in' => 'حقل :attribute المحدد غير صحيح.',
    'not_regex' => 'صيغة حقل :attribute غير صحيحة.',
    'numeric' => 'حقل :attribute يجب أن يكون رقمًا.',
    'password' => 'كلمة المرور غير صحيحة.',
    'present' => 'حقل :attribute يجب أن يكون موجودًا.',
    'regex' => 'صيغة حقل :attribute غير صحيحة.',
    'required' => 'حقل :attribute مطلوب.',
    'required_if' => 'حقل :attribute مطلوب عندما يكون :other بقيمة :value.',
    'required_unless' => 'حقل :attribute مطلوب إلا إذا كان :other بقيمة :values.',
    'required_with' => 'حقل :attribute مطلوب عندما يكون :values موجودًا.',
    'required_with_all' => 'حقل :attribute مطلوب عندما يكون :values موجودًا.',
    'required_without' => 'حقل :attribute مطلوب عندما لا يكون :values موجودًا.',
    'required_without_all' => 'حقل :attribute مطلوب عندما لا يكون أي من :values موجودًا.',
    'same' => 'حقل :attribute و :other يجب أن يتطابقا.',
    'size' => [
        'numeric' => 'حقل :attribute يجب أن يكون :size.',
        'file' => 'حقل :attribute يجب أن يكون :size كيلوبايت.',
        'string' => 'حقل :attribute يجب أن يكون :size أحرف.',
        'array' => 'حقل :attribute يجب أن يحتوي على :size عناصر.',
    ],
    'starts_with' => 'حقل :attribute يجب أن يبدأ بأحد القيم التالية: :values.',
    'string' => 'حقل :attribute يجب أن يكون سلسلة.',
    'timezone' => 'حقل :attribute يجب أن يكون منطقة زمنية صحيحة.',
    'unique' => 'حقل :attribute مستخدم بالفعل.',
    'uploaded' => 'فشل تحميل حقل :attribute.',
    'url' => 'صيغة حقل :attribute غير صحيحة.',
    'uuid' => 'حقل :attribute يجب أن يكون UUID صحيح.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
