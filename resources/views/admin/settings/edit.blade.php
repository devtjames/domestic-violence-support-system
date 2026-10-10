<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">Administration area</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">System Settings</h2>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex w-fit justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Dashboard</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')

            <section class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                Keep contact details, homepage text, Quick Exit destination, and case reference format configurable here. Quick Exit redirects away from this platform, but it does not erase browser history or device activity.
            </section>

            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Platform Identity</h3>
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div>
                            <x-input-label for="platform_name" value="Platform Name" />
                            <x-text-input id="platform_name" name="platform_name" :value="old('platform_name', $settings['platform_name'])" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('platform_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="platform_email" value="Platform Email" />
                            <x-text-input id="platform_email" name="platform_email" type="email" :value="old('platform_email', $settings['platform_email'])" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('platform_email')" class="mt-2" />
                        </div>
                    </div>
                </section>

                <section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Support Contact Details</h3>
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div>
                            <x-input-label for="support_email" value="Support Email" />
                            <x-text-input id="support_email" name="support_email" type="email" :value="old('support_email', $settings['support_email'])" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('support_email')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="support_phone" value="Support Phone" />
                            <x-text-input id="support_phone" name="support_phone" :value="old('support_phone', $settings['support_phone'])" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('support_phone')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="address" value="Address" />
                            <textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $settings['address']) }}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="contact_information" value="Contact Page Information" />
                            <textarea id="contact_information" name="contact_information" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('contact_information', $settings['contact_information']) }}</textarea>
                            <x-input-error :messages="$errors->get('contact_information')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="critical_case_guidance" value="Critical Case Guidance" />
                            <textarea id="critical_case_guidance" name="critical_case_guidance" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('critical_case_guidance', $settings['critical_case_guidance']) }}</textarea>
                            <p class="mt-2 text-xs leading-5 text-gray-500">Shown when someone selects Critical urgency. Keep the wording configurable, neutral, and accurate.</p>
                            <x-input-error :messages="$errors->get('critical_case_guidance')" class="mt-2" />
                        </div>
                    </div>
                </section>

                <section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Privacy and Case References</h3>
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div>
                            <x-input-label for="quick_exit_url" value="Quick Exit Destination" />
                            <x-text-input id="quick_exit_url" name="quick_exit_url" type="url" :value="old('quick_exit_url', $settings['quick_exit_url'])" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('quick_exit_url')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="case_reference_prefix" value="Case Reference Prefix" />
                            <x-text-input id="case_reference_prefix" name="case_reference_prefix" :value="old('case_reference_prefix', $settings['case_reference_prefix'])" class="mt-1 block w-full uppercase" required />
                            <p class="mt-2 text-xs text-gray-500">Example output: {{ old('case_reference_prefix', $settings['case_reference_prefix']) }}-{{ now()->year }}-000001</p>
                            <x-input-error :messages="$errors->get('case_reference_prefix')" class="mt-2" />
                        </div>
                    </div>
                </section>

                <section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Homepage and Footer</h3>
                    <div class="mt-5 grid gap-5">
                        <div>
                            <x-input-label for="homepage_eyebrow" value="Homepage Eyebrow" />
                            <x-text-input id="homepage_eyebrow" name="homepage_eyebrow" :value="old('homepage_eyebrow', $settings['homepage_eyebrow'])" class="mt-1 block w-full" />
                            <x-input-error :messages="$errors->get('homepage_eyebrow')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="homepage_heading" value="Homepage Heading" />
                            <x-text-input id="homepage_heading" name="homepage_heading" :value="old('homepage_heading', $settings['homepage_heading'])" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('homepage_heading')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="homepage_intro" value="Homepage Intro" />
                            <textarea id="homepage_intro" name="homepage_intro" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('homepage_intro', $settings['homepage_intro']) }}</textarea>
                            <x-input-error :messages="$errors->get('homepage_intro')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="footer_information" value="Footer Information" />
                            <textarea id="footer_information" name="footer_information" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('footer_information', $settings['footer_information']) }}</textarea>
                            <x-input-error :messages="$errors->get('footer_information')" class="mt-2" />
                        </div>
                    </div>
                </section>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <x-primary-button>Save Settings</x-primary-button>
                    <a href="{{ route('home') }}" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">View Public Site</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
