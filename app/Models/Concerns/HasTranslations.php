<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Catalog copy in the fallback locale (English) lives on the model's own
 * columns — that is what order snapshots, admin search, and the API's
 * `name` field already read. Every other locale lives in one row per locale
 * in the model's `*_translations` table. Reads fall back to the base column
 * when a locale has no translation (or the field is blank), so a partially
 * translated catalog still renders.
 *
 * @property-read Collection<int, Model> $translations
 */
trait HasTranslations
{
    abstract public function translations(): HasMany;

    public static function baseLocale(): string
    {
        return config('app.fallback_locale', 'en');
    }

    /**
     * @return list<string>
     */
    public static function translationLocales(): array
    {
        return array_values(array_diff(array_keys(config('app.supported_locales', [])), [static::baseLocale()]));
    }

    public function translation(?string $locale = null): ?Model
    {
        $locale ??= app()->getLocale();

        if ($locale === static::baseLocale()) {
            return null;
        }

        return $this->translations->firstWhere('locale', $locale);
    }

    /**
     * The field in the given (default: current) locale, falling back to English.
     */
    public function translated(string $field, ?string $locale = null): ?string
    {
        $value = $this->translation($locale)?->{$field};

        return filled($value) ? $value : $this->{$field};
    }

    /**
     * The field in exactly the given locale, or null when it has not been translated.
     */
    public function translationFor(string $field, string $locale): ?string
    {
        $value = $locale === static::baseLocale() ? $this->{$field} : $this->translation($locale)?->{$field};

        return filled($value) ? $value : null;
    }

    /**
     * The field in the "other" display language, shown under the primary name
     * so shoppers can recognise a product in either script: English when
     * browsing in a translated locale, otherwise the first available translation.
     */
    public function alternateTranslated(string $field): ?string
    {
        $current = app()->getLocale();

        if ($current !== static::baseLocale()) {
            return $this->translationFor($field, $current) !== null ? $this->{$field} : null;
        }

        foreach (static::translationLocales() as $locale) {
            if (($value = $this->translationFor($field, $locale)) !== null) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Upsert per-locale copy. A locale submitted with a blank name is treated
     * as "not translated" and its row removed, since `name` is the one field
     * every translation row must carry.
     *
     * @param  array<string, array<string, string|null>>  $translations  keyed by locale
     */
    public function saveTranslations(array $translations): void
    {
        foreach ($translations as $locale => $fields) {
            if (! in_array($locale, static::translationLocales(), true)) {
                continue;
            }

            if (blank($fields['name'] ?? null)) {
                $this->translations()->where('locale', $locale)->delete();

                continue;
            }

            $this->translations()->updateOrCreate(
                ['locale' => $locale],
                array_map(fn ($value) => filled($value) ? $value : null, $fields),
            );
        }

        $this->unsetRelation('translations');
    }
}
