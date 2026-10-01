<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\Support\Stamped;

/**
 * Customer surveys plus the operational/audit tables (audit log, permission
 * pivots and inert runtime rows).
 */
class OpsDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->surveys();
        $this->auditLogs();
        $this->modelHasPermissions();
        $this->runtimeRows();
    }

    private function surveys(): void
    {
        $customers = DB::table('customers')->orderBy('id')->pluck('id');
        $adminId = DB::table('users')->orderBy('id')->value('id');

        if ($customers->isEmpty()) {
            return;
        }

        $surveys = [
            ['title' => 'استبيان جودة التوصيل', 'description' => 'قيّم تجربة التوصيل الأخيرة.', 'questions' => [
                ['question_text' => 'كيف كان وقت التوصيل؟', 'question_type' => 'rating', 'is_required' => true],
                ['question_text' => 'هل كانت البضاعة سليمة؟', 'question_type' => 'yes_no', 'is_required' => true],
                ['question_text' => 'ملاحظات إضافية', 'question_type' => 'text', 'is_required' => false],
            ]],
            ['title' => 'استبيان رضا العملاء', 'description' => 'رأيك في الخدمة والأسعار.', 'questions' => [
                ['question_text' => 'تقييم عام للخدمة', 'question_type' => 'rating', 'is_required' => true],
                ['question_text' => 'هل التوصيات مناسبة؟', 'question_type' => 'yes_no', 'is_required' => false],
                ['question_text' => 'ما الذي يمكن تحسينه؟', 'question_type' => 'text', 'is_required' => false],
            ]],
            ['title' => 'استبيان منتج جديد', 'description' => 'قيّم اهتمامك في المنتجات الجديدة.', 'questions' => [
                ['question_text' => 'هل تفضل هذا المنتج؟', 'question_type' => 'yes_no', 'is_required' => true],
                ['question_text' => 'تقييم السعر', 'question_type' => 'rating', 'is_required' => true],
                ['question_text' => 'تعليق', 'question_type' => 'text', 'is_required' => false],
            ]],
            ['title' => 'استبيان سرعة الخدمة', 'description' => 'قيّم سرعة الاستجابة والدعم.', 'questions' => [
                ['question_text' => 'كم استغرق الرد على طلبك؟', 'question_type' => 'rating', 'is_required' => true],
                ['question_text' => 'هل الدعم الفني متعاون؟', 'question_type' => 'yes_no', 'is_required' => true],
                ['question_text' => 'اقتراح', 'question_type' => 'text', 'is_required' => false],
            ]],
            ['title' => 'استبيان الأسعار', 'description' => 'رأيك في سياسات الأسعار والعروض.', 'questions' => [
                ['question_text' => 'الأسعار مناسبة؟', 'question_type' => 'yes_no', 'is_required' => true],
                ['question_text' => 'تقييم العروض', 'question_type' => 'rating', 'is_required' => false],
                ['question_text' => 'ملاحظات', 'question_type' => 'text', 'is_required' => false],
            ]],
        ];

        foreach ($surveys as $surveyIndex => $survey) {
            $surveyId = Stamped::upsert('surveys', 
                ['title' => $survey['title']], 
                [
                    'title' => $survey['title'],
                    'description' => $survey['description'],
                    'is_active' => true,
                    'estimated_minutes' => 3,
                    'starts_at' => now()->subWeek(),
                    'ends_at' => now()->addMonth(),
                    'created_by' => $adminId,
                ]);

            $surveyRecordId = DB::table('surveys')->where('title', $survey['title'])->value('id');
            $questionIds = [];

            foreach ($survey['questions'] as $order => $question) {
                $questionId = Stamped::upsert('survey_questions', 
                    ['survey_id' => $surveyRecordId, 'question_text' => $question['question_text']], 
                    [
                        'survey_id' => $surveyRecordId,
                        'question_text' => $question['question_text'],
                        'question_type' => $question['question_type'],
                        'options' => $question['question_type'] === 'yes_no' ? json_encode(['نعم', 'لا']) : null,
                        'is_required' => $question['is_required'],
                        'sort_order' => $order + 1,
                    ]);

                $questionIds[] = DB::table('survey_questions')
                    ->where('survey_id', $surveyRecordId)
                    ->where('question_text', $question['question_text'])
                    ->value('id');
            }

            // One response per customer, with an answer for each question.
            foreach ($customers as $customerId) {
                $responseId = Stamped::upsert('survey_responses', 
                    ['survey_id' => $surveyRecordId, 'customer_id' => $customerId], 
                    [
                        'survey_id' => $surveyRecordId,
                        'customer_id' => $customerId,
                        'submitted_at' => now()->subDays($surveyIndex + 1),
                    ]);

                $actualResponseId = DB::table('survey_responses')
                    ->where('survey_id', $surveyRecordId)
                    ->where('customer_id', $customerId)
                    ->value('id');

                foreach ($questionIds as $questionId) {
                    if (! $questionId) {
                        continue;
                    }

                    $question = DB::table('survey_questions')->where('id', $questionId)->first();

                    Stamped::upsert('survey_answers', 
                        ['survey_response_id' => $actualResponseId, 'survey_question_id' => $questionId], 
                        [
                            'survey_response_id' => $actualResponseId,
                            'survey_question_id' => $questionId,
                            'answer_text' => match ($question->question_type) {
                                'rating' => (string) (3 + ($customerId % 3)),
                                'yes_no' => $customerId % 2 === 0 ? 'نعم' : 'لا',
                                default => 'ملاحظة من العميل '.$customerId,
                            },
                        ]);
                }
            }
        }
    }

    private function auditLogs(): void
    {
        $adminId = DB::table('users')->orderBy('id')->value('id');
        $invoiceId = DB::table('invoices')->orderBy('id')->value('id');
        $orderId = DB::table('orders')->orderBy('id')->value('id');

        if (! $invoiceId && ! $orderId) {
            return;
        }

        $events = [
            ['auditable_type' => 'App\\Models\\Invoice', 'auditable_id' => $invoiceId, 'event' => 'approved', 'new_values' => ['status' => 'approved']],
            ['auditable_type' => 'App\\Models\\Invoice', 'auditable_id' => $invoiceId, 'event' => 'payment_recorded', 'new_values' => ['paid_amount' => 100]],
            ['auditable_type' => 'App\\Models\\Order', 'auditable_id' => $orderId, 'event' => 'status_changed', 'new_values' => ['status' => 'delivered']],
            ['auditable_type' => 'App\\Models\\Order', 'auditable_id' => $orderId, 'event' => 'created', 'new_values' => ['status' => 'new']],
            ['auditable_type' => 'App\\Models\\Customer', 'auditable_id' => 1, 'event' => 'wallet_deposit', 'new_values' => ['amount' => 250]],
        ];

        foreach ($events as $index => $event) {
            if (! $event['auditable_id']) {
                continue;
            }

            Stamped::insert('audit_logs', [
                'auditable_type' => $event['auditable_type'],
                'auditable_id' => $event['auditable_id'],
                'event' => $event['event'],
                'old_values' => null,
                'new_values' => json_encode($event['new_values'], JSON_UNESCAPED_UNICODE),
                'user_id' => $adminId,
                'user_type' => 'user',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'seeder',
                'created_at' => now()->subDays($index),
            ]);
        }
    }

    private function modelHasPermissions(): void
    {
        $roleIds = DB::table('model_has_roles')->limit(5)->pluck('role_id');
        $permissionIds = DB::table('permissions')->orderBy('id')->limit(10)->pluck('id');

        if ($roleIds->isEmpty() || $permissionIds->isEmpty()) {
            return;
        }

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                Stamped::upsert('model_has_permissions', 
                    [
                        'permission_id' => $permissionId,
                        'model_type' => 'App\\Models\\Role',
                        'model_id' => $roleId,
                    ], 
                    []);
            }
        }
    }

    /**
     * Fills the Laravel infrastructure tables with rows that are inert at
     * runtime: expired cache entries, finished job batches and failed jobs.
     *
     * The `jobs`, `personal_access_tokens` and `password_reset_tokens` tables
     * are deliberately left empty, because a row there would either be picked
     * up by a real worker or hand out working credentials.
     */
    private function runtimeRows(): void
    {
        $past = now()->subHour();

        for ($i = 1; $i <= 5; $i++) {
            Stamped::upsert('cache', 
                ['key' => 'seeded:cache:'.$i], 
                ['value' => 'seeded', 'expiration' => $past->getTimestamp()]);
        }

        for ($i = 1; $i <= 5; $i++) {
            Stamped::upsert('cache_locks', 
                ['key' => 'seeded:lock:'.$i], 
                ['owner' => 'seeded', 'expiration' => $past->getTimestamp()]);
        }

        for ($i = 1; $i <= 5; $i++) {
            Stamped::upsert('sessions', 
                ['id' => 'seeded-session-'.$i], 
                [
                    'user_id' => null,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'seeder',
                    'payload' => base64_encode(serialize([])),
                    'last_activity' => $past->getTimestamp(),
                ]);
        }

        for ($i = 1; $i <= 5; $i++) {
            Stamped::upsert('job_batches', 
                ['id' => 'seeded-batch-'.$i], 
                [
                    'name' => 'seeded',
                    'total_jobs' => 0,
                    'pending_jobs' => 0,
                    'failed_jobs' => 0,
                    'failed_job_ids' => '[]',
                    'options' => '[]',
                    'cancelled_at' => $past->getTimestamp(),
                    'created_at' => $past->getTimestamp(),
                    'finished_at' => $past->getTimestamp(),
                ]);
        }

        for ($i = 1; $i <= 5; $i++) {
            Stamped::upsert('failed_jobs', 
                ['uuid' => sprintf('00000000-0000-4000-8000-%012d', $i)], 
                [
                    'connection' => 'database',
                    'queue' => 'default',
                    'payload' => json_encode(['seeded' => true]),
                    'exception' => 'Seeded row for demonstration purposes.',
                    'failed_at' => $past,
                ]);
        }
    }
}
