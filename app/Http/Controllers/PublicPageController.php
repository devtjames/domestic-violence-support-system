<?php

namespace App\Http\Controllers;

use App\Models\AwarenessArticle;
use App\Models\AwarenessCategory;
use App\Models\FaqCategory;
use App\Models\SystemSetting;
use App\Models\SupportResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'articles' => AwarenessArticle::query()
                ->published()
                ->with(['category', 'author'])
                ->latest('published_at')
                ->take(3)
                ->get(),
            'resources' => SupportResource::query()
                ->active()
                ->orderBy('organization_name')
                ->take(3)
                ->get(),
            'abuseTypes' => array_slice($this->abuseTypeItems(), 0, 6),
            'warningSigns' => array_slice($this->warningSignItems(), 0, 6),
            'settings' => SystemSetting::allAsArray(),
        ]);
    }

    public function about(): View
    {
        return view('public.page', [
            'title' => 'About Domestic Violence',
            'intro' => 'Domestic violence is a pattern of abusive behavior used by one person to gain or keep power and control over another person in a close or family relationship.',
            'sections' => [
                [
                    'heading' => 'It can happen in different relationships',
                    'body' => 'Abuse may affect spouses, partners, former partners, children, older relatives, and other household members. It can happen to people of any background.',
                ],
                [
                    'heading' => 'Abuse is not always physical',
                    'body' => 'Control, fear, isolation, intimidation, humiliation, financial restriction, and digital monitoring can all be part of an abusive pattern.',
                ],
                [
                    'heading' => 'Support should be private and respectful',
                    'body' => 'This platform is designed for awareness and support requests. It does not replace emergency services or professional legal, medical, or counselling support.',
                ],
            ],
        ]);
    }

    public function types(): View
    {
        return view('public.collection', [
            'title' => 'Types of Abuse',
            'intro' => 'Abuse can appear in several forms. Understanding the signs can help people name what is happening and consider safer support options.',
            'items' => $this->abuseTypeItems(),
        ]);
    }

    public function warningSigns(): View
    {
        return view('public.collection', [
            'title' => 'Warning Signs',
            'intro' => 'Warning signs often appear as repeated patterns of control, fear, isolation, or intimidation.',
            'items' => $this->warningSignItems(),
        ]);
    }

    public function safety(): View
    {
        return view('public.collection', [
            'title' => 'Safety Information',
            'intro' => 'Safety planning is personal. Use these points as general awareness information and seek trusted professional support where possible.',
            'items' => [
                ['title' => 'Choose safer moments', 'description' => 'Consider when and where it may be safer to reach out, especially if your devices or messages may be monitored.'],
                ['title' => 'Use trusted contacts', 'description' => 'Identify people or organizations you trust and decide what information is safe to share with them.'],
                ['title' => 'Keep important items accessible', 'description' => 'Where safe, keep copies of key documents, medication, money, and essential phone numbers available.'],
                ['title' => 'Protect device privacy', 'description' => 'Use a safer device when possible. Browser history, shared accounts, and notifications may reveal visits.'],
                ['title' => 'Plan for children and dependants', 'description' => 'Think through safe routes, trusted adults, school contacts, and emergency arrangements for dependants.'],
                ['title' => 'Use emergency services when in immediate danger', 'description' => 'This academic platform does not dispatch emergency help. Contact verified emergency services if immediate danger exists.'],
            ],
        ]);
    }

    public function awareness(Request $request): View
    {
        $articles = AwarenessArticle::query()
            ->published()
            ->with(['category', 'author'])
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', fn ($query) => $query->where('slug', $request->string('category')->toString()));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('public.awareness', [
            'articles' => $articles,
            'categories' => AwarenessCategory::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function article(string $slug): View
    {
        $article = AwarenessArticle::query()
            ->published()
            ->with(['category', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('public.article', [
            'article' => $article,
        ]);
    }

    public function resources(Request $request): View
    {
        $resources = SupportResource::query()
            ->active()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('organization_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('resource_type'), fn ($query) => $query->where('resource_type', $request->string('resource_type')->toString()))
            ->when($request->filled('location'), fn ($query) => $query->where('location', 'like', '%'.$request->string('location')->toString().'%'))
            ->orderBy('organization_name')
            ->paginate(9)
            ->withQueryString();

        return view('public.resources', [
            'resources' => $resources,
            'resourceTypes' => SupportResource::resourceTypes(),
        ]);
    }

    public function faqs(): View
    {
        return view('public.faqs', [
            'categories' => FaqCategory::query()
                ->active()
                ->whereHas('faqs', fn ($query) => $query->active())
                ->with(['faqs' => fn ($query) => $query->active()->orderBy('sort_order')->orderBy('question')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function getSupport(): View
    {
        return view('public.get-support');
    }

    public function contact(): View
    {
        return view('public.contact', [
            'settings' => SystemSetting::allAsArray(),
        ]);
    }

    public function quickExit(): RedirectResponse
    {
        return redirect()
            ->away(SystemSetting::quickExitUrl())
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
            ]);
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    private function abuseTypeItems(): array
    {
        return [
            ['title' => 'Physical Abuse', 'description' => 'Use of physical force, threats of harm, restraint, or intimidation involving the body.'],
            ['title' => 'Emotional Abuse', 'description' => 'Repeated insults, humiliation, blame, manipulation, or behavior that damages confidence and wellbeing.'],
            ['title' => 'Psychological Abuse', 'description' => 'Threats, coercion, isolation, monitoring, intimidation, or actions that create fear and dependency.'],
            ['title' => 'Financial Abuse', 'description' => 'Controlling money, blocking employment, taking income, creating debt, or restricting access to essentials.'],
            ['title' => 'Sexual Abuse', 'description' => 'Any unwanted sexual contact, pressure, coercion, or exploitation within or outside a relationship.'],
            ['title' => 'Digital Abuse', 'description' => 'Monitoring devices, passwords, messages, location, social media, or online accounts without consent.'],
            ['title' => 'Verbal Abuse', 'description' => 'Yelling, threats, insults, degrading comments, or language used to frighten or control another person.'],
            ['title' => 'Coercive Control', 'description' => 'A pattern of rules, threats, surveillance, isolation, and punishment that limits another person\'s freedom.'],
            ['title' => 'Child-related Abuse', 'description' => 'Using children to threaten, manipulate, intimidate, or control a parent or caregiver.'],
        ];
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    private function warningSignItems(): array
    {
        return [
            ['title' => 'Fear of a partner or family member', 'description' => 'A person may seem unusually anxious about upsetting someone or asking permission for everyday choices.'],
            ['title' => 'Isolation from support', 'description' => 'Friends, family, work, study, or community connections may be restricted or closely monitored.'],
            ['title' => 'Constant monitoring', 'description' => 'Calls, messages, location, passwords, or online activity may be checked repeatedly.'],
            ['title' => 'Control over money', 'description' => 'Access to cash, banking, employment, transport, or essentials may be limited.'],
            ['title' => 'Threats or intimidation', 'description' => 'Threats may be made toward the person, children, relatives, pets, property, or reputation.'],
            ['title' => 'Blame and humiliation', 'description' => 'The abusive person may shift blame, belittle concerns, or make the person feel responsible for the abuse.'],
            ['title' => 'Pressure to withdraw reports', 'description' => 'Someone may be pressured to deny, minimize, or hide what has happened.'],
            ['title' => 'Sudden changes in behavior', 'description' => 'Stress, withdrawal, missed appointments, or guarded communication can be signs that support is needed.'],
        ];
    }
}
