<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\OrganizationAbout;
use App\Services\Customer\ImagePathService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerPagesController extends Controller
{
    use CustomerAppHelpers;

    public function __construct(ImagePathService $imagePathService)
    {
        $this->imagePathService = $imagePathService;
    }

    public function more(Request $request)
    {
        $customer = $this->getCustomer($request);

        return view('app_android.more', compact('customer'));
    }

    public function aboutUsWeb(Request $request)
    {
        $about = null;
        $customer = $this->getCustomer($request);

        if ($customer) {
            $activeOrg = $customer->activeOrganization;
            if (! $activeOrg) {
                $activeOrg = $customer->activeOrganizations()->first();
            }
            if ($activeOrg) {
                $about = $activeOrg->about()->where('is_active', true)->first() ?: $activeOrg->about;
            }
        }
        if (! $about) {
            $about = OrganizationAbout::where('is_active', true)->first() ?: OrganizationAbout::first();
        }

        if (! $about) {
            $about = (object) [
                'id' => 1,
                'title' => 'ماجاديت للصناعات الغذائية',
                'subtitle' => 'جودة تذوقها الثقة والتميز',
                'description' => 'تعد شركة ماجاديت للصناعات الغذائية إحدى الشركات الرائدة في تصنيع وتوزيع المنتجات الغذائية عالية الجودة، حيث نلتزم بأعلى معايير الجودة والسلامة الغذائية.',
                'vision' => 'أن نكون الخيار الأول والشركة الرائدة في مجال الصناعات الغذائية في المنطقة.',
                'mission' => 'تقديم منتجات غذائية صحية وآمنة وعالية الجودة تلبي تطلعات عملائنا.',
                'values' => [
                    ['name' => 'الجودة العالية والالتزام بالمعايير'],
                    ['name' => 'الابتكار المستمر والتطوير'],
                    ['name' => 'الشفافية والأمانة في التعامل'],
                    ['name' => 'رضا العملاء والشغف بالتميز'],
                ],
                'statistics' => [
                    'سنوات الخبرة' => '10+',
                    'المنتجات' => '50+',
                    'العملاء السعداء' => '10,000+',
                ],
                'image' => null,
                'cover_image' => null,
                'image_url' => null,
                'cover_image_url' => null,
            ];
        }

        return view('app_android.more.about_us', compact('about'));
    }

    public function aboutUs(Request $request)
    {
        $customer = $this->getCustomer($request);
        $about = null;

        if ($customer) {
            $activeOrg = $customer->activeOrganization;
            if (! $activeOrg) {
                $activeOrg = $customer->activeOrganizations()->first();
            }
            if ($activeOrg) {
                $about = $activeOrg->about()->where('is_active', true)->first() ?: $activeOrg->about;
            }
        }

        return response()->json([
            'success' => true,
            'content' => $about ? [
                'id' => $about->id,
                'title' => $about->title,
                'subtitle' => $about->subtitle,
                'description' => $about->description,
                'vision' => $about->vision,
                'mission' => $about->mission,
                'values' => $this->parseJsonField($about->values),
                'statistics' => $this->parseStatsField($about->statistics),
                'image_url' => $about->image_url,
                'cover_image_url' => $about->cover_image_url,
            ] : null,
        ]);
    }

    public function faqIndex()
    {
        $faqs = Faq::all();
        $faqsByCategory = $faqs->groupBy('category');

        return view('app_android.more.faq', compact('faqsByCategory'));
    }

    public function contactUs(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $organizationId = $request->query('organization_id');

        if (! $organizationId) {
            $customer = $this->getCustomer($request);
            if ($customer) {
                $organizationId = $customer->active_organization_id;
                if (! $organizationId) {
                    $activeOrg = $customer->activeOrganizations()->first();
                    if ($activeOrg) {
                        $organizationId = $activeOrg->id;
                    }
                }
            }
        }

        $contactMessage = \App\Models\ContactMessage::create([
            'organization_id' => $organizationId,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'new',
        ]);

        $organization = null;
        if ($organizationId) {
            $organization = \App\Models\Organization::find($organizationId);
        }

        if (! $organization) {
            $organization = \App\Models\Organization::first();
        }

        if ($organization && $organization->forward_contact_messages_to_email) {
            try {
                $recipient = $organization->contact_email_recipient ?? $organization->email;
                if ($recipient) {
                    \Illuminate\Support\Facades\Mail::to($recipient)->send(new \App\Mail\ContactMessageMail($contactMessage));
                    $contactMessage->update([
                        'forwarded_to_email' => true,
                        'forwarded_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::error('Failed to forward contact message: '.$e->getMessage());
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رسالتك بنجاح',
            ]);
        }

        return redirect()->back()->with('success', 'تم إرسال رسالتك بنجاح');
    }

    private function parseJsonField($value)
    {
        if (empty($value)) {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($value) ? $value : [];
    }

    private function parseStatsField($value)
    {
        if (empty($value)) {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($value) ? $value : [];
    }
}
