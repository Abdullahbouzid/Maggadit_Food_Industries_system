<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::with('organization')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('importance')) {
            $query->where('importance', $request->importance);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'priority':
                $query->orderByRaw("FIELD(importance, 'urgent', 'high', 'medium', 'low') DESC")
                    ->orderByDesc('created_at');
                break;
            case 'unread':
                $query->whereIn('status', ['new', 'read'])->orderByDesc('created_at');
                break;
            default:
                $query->latest();
        }

        $messages = $query->paginate(20)->appends($request->query());

        return view('dashboard.contact_messages.index', compact('messages'));
    }

    public function show(ContactMessage $contactMessage)
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->update(['status' => 'read']);
        }

        $organizations = Organization::pluck('name', 'id');
        $statuses = ['new', 'read', 'replied', 'closed'];
        $importances = ['low', 'medium', 'high', 'urgent'];

        return view('dashboard.contact_messages.show', compact('contactMessage', 'organizations', 'statuses', 'importances'));
    }

    public function updateStatus(Request $request, ContactMessage $contactMessage)
    {
        $request->validate([
            'status' => 'required|in:new,read,replied,closed',
        ]);

        $contactMessage->update(['status' => $request->status]);

        return back()->with('success', 'تم تحديث حالة الرسالة');
    }

    public function updateImportance(Request $request, ContactMessage $contactMessage)
    {
        $request->validate([
            'importance' => 'required|in:low,medium,high,urgent',
        ]);

        $contactMessage->update(['importance' => $request->importance]);

        return back()->with('success', 'تم تحديث أهمية الرسالة');
    }

    public function reply(Request $request, ContactMessage $contactMessage)
    {
        $request->validate([
            'reply' => 'required|string',
        ]);

        $contactMessage->update([
            'status' => 'replied',
        ]);

        return back()->with('success', 'تم تسجيل الرد بنجاح');
    }

    public function close(ContactMessage $contactMessage)
    {
        $contactMessage->update(['status' => 'closed']);

        return back()->with('success', 'تم إغلاق الرسالة');
    }

    public function forward(ContactMessage $contactMessage)
    {
        $organization = $contactMessage->organization;

        $recipient = $organization?->contact_email_recipient ?? $organization?->email ?? config('mail.from.address');

        if (! $recipient) {
            return back()->with('error', 'لا يوجد بريد إلكتروني مستلم. يرجى تحديده في إعدادات المنظمة.');
        }

        try {
            Mail::to($recipient)->send(new ContactMessageMail($contactMessage));

            $contactMessage->update([
                'forwarded_to_email' => true,
                'forwarded_at' => now(),
            ]);

            return back()->with('success', 'تم تحويل الرسالة إلى البريد الإلكتروني بنجاح');
        } catch (\Throwable $e) {
            return back()->with('error', 'فشل إرسال البريد: '.$e->getMessage());
        }
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return redirect()->route('contact-messages.index')->with('success', 'تم حذف الرسالة');
    }
}
