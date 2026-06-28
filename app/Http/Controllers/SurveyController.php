<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
    /**
     * قائمة الاستبيانات
     */
    public function index()
    {
        $surveys = Survey::withCount('responses')
            ->with('questions')
            ->latest()
            ->get();

        return view('dashboard.surveys.index', compact('surveys'));
    }

    /**
     * نموذج إنشاء استبيان جديد
     */
    public function create()
    {
        return view('dashboard.surveys.create');
    }

    /**
     * حفظ استبيان جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'estimated_minutes'=> 'nullable|integer|min:1',
            'is_active'        => 'sometimes|boolean',
            'starts_at'        => 'nullable|date',
            'ends_at'          => 'nullable|date|after_or_equal:starts_at',
            'questions'        => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.question_type' => 'required|in:text,choice,rating,multiselect,yesno',
            'questions.*.options'       => 'nullable|array',
            'questions.*.is_required'   => 'sometimes|boolean',
        ]);

        DB::transaction(function () use ($request) {
            $survey = Survey::create([
                'title'             => $request->title,
                'description'       => $request->description,
                'estimated_minutes' => $request->estimated_minutes ?? 5,
                'is_active'         => $request->boolean('is_active', true),
                'starts_at'         => $request->starts_at,
                'ends_at'           => $request->ends_at,
                'created_by'        => Auth::id(),
            ]);

            foreach ($request->questions as $index => $q) {
                $survey->questions()->create([
                    'question_text' => $q['question_text'],
                    'question_type' => $q['question_type'],
                    'options'       => $q['options'] ?? null,
                    'is_required'   => isset($q['is_required']) ? (bool)$q['is_required'] : true,
                    'sort_order'    => $index,
                ]);
            }
        });

        return redirect()->route('surveys.index')
            ->with('success', 'تم إنشاء الاستبيان بنجاح وسيظهر إشعار للعملاء!');
    }

    /**
     * نموذج تعديل الاستبيان
     */
    public function edit(Survey $survey)
    {
        $survey->load('questions');
        return view('dashboard.surveys.edit', compact('survey'));
    }

    /**
     * تحديث الاستبيان
     */
    public function update(Request $request, Survey $survey)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'estimated_minutes'=> 'nullable|integer|min:1',
            'is_active'        => 'sometimes|boolean',
            'starts_at'        => 'nullable|date',
            'ends_at'          => 'nullable|date|after_or_equal:starts_at',
            'questions'        => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.question_type' => 'required|in:text,choice,rating,multiselect,yesno',
            'questions.*.options'       => 'nullable|array',
            'questions.*.is_required'   => 'sometimes|boolean',
        ]);

        DB::transaction(function () use ($request, $survey) {
            $survey->update([
                'title'             => $request->title,
                'description'       => $request->description,
                'estimated_minutes' => $request->estimated_minutes ?? 5,
                'is_active'         => $request->boolean('is_active', true),
                'starts_at'         => $request->starts_at,
                'ends_at'           => $request->ends_at,
            ]);

            // حذف الأسئلة القديمة وإعادة إنشائها
            $survey->questions()->delete();

            foreach ($request->questions as $index => $q) {
                $survey->questions()->create([
                    'question_text' => $q['question_text'],
                    'question_type' => $q['question_type'],
                    'options'       => $q['options'] ?? null,
                    'is_required'   => isset($q['is_required']) ? (bool)$q['is_required'] : true,
                    'sort_order'    => $index,
                ]);
            }
        });

        return redirect()->route('surveys.index')
            ->with('success', 'تم تحديث الاستبيان بنجاح.');
    }

    /**
     * عرض نتائج الاستبيان
     */
    public function show(Survey $survey)
    {
        $survey->load(['questions.answers.response.customer', 'responses.customer']);

        // إحصائيات لكل سؤال
        $stats = [];
        foreach ($survey->questions as $question) {
            $answers = $question->answers;
            $stat = [
                'question'  => $question->question_text,
                'type'      => $question->question_type,
                'total'     => $answers->count(),
                'breakdown' => [],
            ];

            if (in_array($question->question_type, ['choice', 'rating', 'multiselect', 'yesno'])) {
                $breakdown = $answers->groupBy('answer_text')
                    ->map(fn($group) => $group->count());
                $stat['breakdown'] = $breakdown->toArray();
            } else {
                $stat['text_answers'] = $answers->pluck('answer_text')->filter()->values()->toArray();
            }

            $stats[] = $stat;
        }

        return view('dashboard.surveys.show', compact('survey', 'stats'));
    }

    /**
     * حذف الاستبيان
     */
    public function destroy(Survey $survey)
    {
        $survey->delete();
        return redirect()->route('surveys.index')
            ->with('success', 'تم حذف الاستبيان بنجاح.');
    }

    /**
     * تغيير حالة النشاط (toggle)
     */
    public function toggle(Survey $survey)
    {
        $survey->update(['is_active' => !$survey->is_active]);
        $status = $survey->is_active ? 'نشط' : 'غير نشط';
        return redirect()->route('surveys.index')
            ->with('success', "تم تغيير حالة الاستبيان إلى: {$status}");
    }
}
