<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemSetting extends Model
{
    public const KEY_PLATFORM_NAME = 'platform_name';

    public const KEY_PLATFORM_EMAIL = 'platform_email';

    public const KEY_SUPPORT_PHONE = 'support_phone';

    public const KEY_SUPPORT_EMAIL = 'support_email';

    public const KEY_ADDRESS = 'address';

    public const KEY_QUICK_EXIT_URL = 'quick_exit_url';

    public const KEY_CASE_REFERENCE_PREFIX = 'case_reference_prefix';

    public const KEY_CONTACT_INFORMATION = 'contact_information';

    public const KEY_CRITICAL_CASE_GUIDANCE = 'critical_case_guidance';

    public const KEY_HOMEPAGE_EYEBROW = 'homepage_eyebrow';

    public const KEY_HOMEPAGE_HEADING = 'homepage_heading';

    public const KEY_HOMEPAGE_INTRO = 'homepage_intro';

    public const KEY_FOOTER_INFORMATION = 'footer_information';

    protected $fillable = [
        'setting_key',
        'value',
    ];

    /**
     * @return array<string, string|null>
     */
    public static function defaults(): array
    {
        return [
            self::KEY_PLATFORM_NAME => config('app.name', 'Domestic Support'),
            self::KEY_PLATFORM_EMAIL => config('mail.from.address'),
            self::KEY_SUPPORT_PHONE => null,
            self::KEY_SUPPORT_EMAIL => config('mail.from.address'),
            self::KEY_ADDRESS => null,
            self::KEY_QUICK_EXIT_URL => config('support.quick_exit_url', 'https://www.google.com'),
            self::KEY_CASE_REFERENCE_PREFIX => 'DV',
            self::KEY_CONTACT_INFORMATION => 'General enquiries are separate from support cases. Do not include sensitive case details in contact messages.',
            self::KEY_CRITICAL_CASE_GUIDANCE => 'If there is immediate danger, use a verified local emergency service or trusted local support contact. This academic platform does not dispatch emergency assistance.',
            self::KEY_HOMEPAGE_EYEBROW => 'Private awareness and support',
            self::KEY_HOMEPAGE_HEADING => config('app.name', 'Domestic Support'),
            self::KEY_HOMEPAGE_INTRO => 'Learn about domestic violence, understand warning signs, and find a safer path to request support.',
            self::KEY_FOOTER_INFORMATION => 'An academic awareness and support platform. It is not an emergency, legal, medical, or counselling service.',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function editableKeys(): array
    {
        return array_keys(self::defaults());
    }

    /**
     * @return array<string, string|null>
     */
    public static function allAsArray(): array
    {
        $settings = self::defaults();

        try {
            if (! Schema::hasTable((new self)->getTable())) {
                return $settings;
            }

            static::query()
                ->whereIn('setting_key', self::editableKeys())
                ->get(['setting_key', 'value'])
                ->each(function (SystemSetting $setting) use (&$settings) {
                    $settings[$setting->setting_key] = $setting->value;
                });
        } catch (Throwable) {
            return $settings;
        }

        return $settings;
    }

    public static function getValue(string $key, ?string $fallback = null): ?string
    {
        $fallback ??= self::defaults()[$key] ?? null;

        try {
            if (! Schema::hasTable((new self)->getTable())) {
                return $fallback;
            }

            $setting = static::query()
                ->where('setting_key', $key)
                ->first(['value']);

            if (! $setting) {
                return $fallback;
            }

            return filled($setting->value) ? (string) $setting->value : null;
        } catch (Throwable) {
            return $fallback;
        }
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public static function setMany(array $values): void
    {
        foreach (self::editableKeys() as $key) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            static::query()->updateOrCreate(
                ['setting_key' => $key],
                ['value' => filled($values[$key]) ? (string) $values[$key] : null]
            );
        }
    }

    public static function platformName(): string
    {
        return self::getValue(self::KEY_PLATFORM_NAME, config('app.name', 'Domestic Support')) ?: 'Domestic Support';
    }

    public static function quickExitUrl(): string
    {
        return self::getValue(self::KEY_QUICK_EXIT_URL, config('support.quick_exit_url', 'https://www.google.com')) ?: 'https://www.google.com';
    }

    public static function caseReferencePrefix(): string
    {
        $prefix = self::getValue(self::KEY_CASE_REFERENCE_PREFIX, 'DV') ?: 'DV';

        return strtoupper($prefix);
    }
}
