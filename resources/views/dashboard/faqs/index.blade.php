@extends('dashboard.partials.layout')

@section('title', 'الأسئلة الشائعة')

@section('content')
<div class="container-fluid p-0">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-3">الأسئلة الشائعة</h1>
            <a href="{{ route('faqs.create') }}" class="btn btn-primary">إضافة سؤال جديد</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="alert-message">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الفئة</th>
                                <th>السؤال</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($faqs as $faq)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $faq->category ?? 'بدون فئة' }}</td>
                                    <td>{{ $faq->question }}</td>
                                    <td>
                                        <a href="{{ route('faqs.edit', $faq->id) }}" class="btn btn-sm btn-info">تعديل</a>
                                        <form action="{{ route('faqs.destroy', $faq->id) }}" method="POST" class="d-inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا السؤال؟')">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">لا توجد أسئلة شائعة حتى الآن.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
