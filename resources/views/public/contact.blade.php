<x-public-layout title="Contact">
    @php
        $contactInformation = $settings['contact_information'] ?? null;
        $supportEmail = $settings['support_email'] ?? null;
        $supportPhone = $settings['support_phone'] ?? null;
        $address = $settings['address'] ?? null;
        $platformEmail = $settings['platform_email'] ?? null;
    @endphp

    <main>
        <section class="public-page-hero bg-gradient-to-br from-slate-950 via-slate-900 to-teal-950 py-20 text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase text-teal-100">Contact</p>
                    <h1 class="mt-3 text-4xl font-bold leading-tight">General enquiries</h1>
                    <p class="mt-5 text-base leading-7 text-gray-100">
                        General enquiries are separate from support cases. Do not include sensitive case details in this form.
                    </p>
                </div>
            </div>
        </section>

        <section class="bg-white py-12">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
                <div class="space-y-6 lg:col-span-2">
                    @include('admin.partials.feedback')

                    <form method="POST" action="{{ route('public.contact.store') }}" class="space-y-6 rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                        @csrf

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <x-input-label for="name" value="Name" />
                                <x-text-input id="name" name="name" :value="old('name')" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="email" value="Email" />
                                <x-text-input id="email" name="email" type="email" :value="old('email')" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="subject" value="Subject" />
                            <x-text-input id="subject" name="subject" :value="old('subject')" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="message" value="Message" />
                            <textarea id="message" name="message" rows="7" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('message') }}</textarea>
                            <x-input-error :messages="$errors->get('message')" class="mt-2" />
                        </div>

                        <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                            This form is for general platform enquiries only. Use the registered or anonymous support request options for support needs.
                        </div>

                        <x-primary-button>Send Enquiry</x-primary-button>
                    </form>
                </div>

                <aside class="space-y-6">
                    <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900">Platform contact</h2>
                        @if ($contactInformation)
                            <p class="mt-3 text-sm leading-6 text-gray-600">{{ $contactInformation }}</p>
                        @endif

                        <dl class="mt-5 space-y-4 text-sm">
                            @if ($supportEmail)
                                <div>
                                    <dt class="font-medium text-gray-900">Support email</dt>
                                    <dd class="mt-1 break-all text-gray-600">{{ $supportEmail }}</dd>
                                </div>
                            @endif

                            @if ($supportPhone)
                                <div>
                                    <dt class="font-medium text-gray-900">Support phone</dt>
                                    <dd class="mt-1 text-gray-600">{{ $supportPhone }}</dd>
                                </div>
                            @endif

                            @if ($address)
                                <div>
                                    <dt class="font-medium text-gray-900">Address</dt>
                                    <dd class="mt-1 whitespace-pre-line text-gray-600">{{ $address }}</dd>
                                </div>
                            @endif

                            @if (! $supportEmail && ! $supportPhone && ! $address && $platformEmail)
                                <div>
                                    <dt class="font-medium text-gray-900">Platform email</dt>
                                    <dd class="mt-1 break-all text-gray-600">{{ $platformEmail }}</dd>
                                </div>
                            @endif
                        </dl>
                    </article>

                    <article class="rounded-md border border-rose-200 bg-rose-50 p-6">
                        <h2 class="text-lg font-semibold text-gray-900">Need support?</h2>
                        <p class="mt-3 text-sm leading-6 text-gray-700">
                            Contact messages are not converted into support cases. Use the support workflows if you need help with a private support request.
                        </p>
                        <a href="{{ route('public.get-support') }}" class="mt-5 inline-flex rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800">Get Support</a>
                    </article>
                </aside>
            </div>
        </section>
    </main>
</x-public-layout>
