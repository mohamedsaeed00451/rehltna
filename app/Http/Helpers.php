<?php

use App\Models\Setting;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

if (!function_exists('get_setting')) {
    function get_setting($key)
    {
        return Setting::query()->where('key', $key)->first()?->value;
    }
}

if (!function_exists('get_active_langs')) {
    function get_active_langs(): array
    {
        $defaults = ['ar', 'en', 'fr', 'de'];

        $dbSettings = get_setting('active_langs');

        if (is_string($dbSettings)) {
            $decoded = json_decode($dbSettings, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return $decoded;
            }
        }

        if (is_array($dbSettings) && count($dbSettings) > 0) {
            return $dbSettings;
        }

        return $defaults;
    }
}

if (!function_exists('get_lang_enabled')) {
    function get_lang_enabled(string $lang): bool
    {
        return in_array($lang, get_active_langs());
    }
}

if (!function_exists('hasArabic')) {
    function hasArabic(): bool
    {
        return get_lang_enabled('ar');
    }
}

if (!function_exists('hasEnglish')) {
    function hasEnglish(): bool
    {
        return get_lang_enabled('en');
    }
}

if (!function_exists('hasFrench')) {
    function hasFrench(): bool
    {
        return get_lang_enabled('fr');
    }
}

if (!function_exists('hasGerman')) {
    function hasGerman(): bool
    {
        return get_lang_enabled('de');
    }
}

if (!function_exists('colClass')) {

    function colClass(): string
    {
        $count = count(get_active_langs());

        return match ($count) {
            1 => 'col-md-12',
            2 => 'col-md-6',
            3 => 'col-md-4',
            4 => 'col-md-3',
            default => 'col-md-12',
        };
    }
}

if (!function_exists('getTenantInfo')) {
    function getTenantInfo(): Tenant|Collection|Model|null
    {
        return Tenant::query()->find(getTenantId());
    }
}

if (!function_exists('checkIfAdmin')) {
    function checkIfAdmin(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'admin';
    }
}

if (!function_exists('downloadAndSaveImage')) {

    function downloadAndSaveImage(?string $url, string $type, $prefix = null): ?string
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        try {
            $imageContent = file_get_contents($url);
            if ($imageContent === false) {
                return null;
            }
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $fileName = uniqid() . '_' . time() . ($prefix ? "_{$prefix}" : '') . '.' . $extension;
            $path = public_path("uploads/tenant_" . getTenantId() . "/{$type}/");
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            file_put_contents($path . $fileName, $imageContent);
            return "uploads/tenant_" . getTenantId() . "/{$type}/" . $fileName;
        } catch (\Exception $e) {
            return null;
        }
    }

}

if (!function_exists('uploadFile')) {
    function uploadFile(UploadedFile $file, string $folder, string $prefix = null): string
    {
        $path = "uploads/tenant_" . getTenantId() . "/{$folder}";
        $fileName = uniqid() . '_' . time() . ($prefix ? "_{$prefix}" : '') . '.' . $file->getClientOriginalExtension();
        if (!file_exists(public_path($path))) {
            mkdir(public_path($path), 0777, true);
        }
        $file->move(public_path($path), $fileName);
        return "{$path}/{$fileName}";
    }
}

if (!function_exists('deleteFiles')) {
    function deleteFiles(array $files): void
    {
        foreach ($files as $file) {
            if ($file) {
                $relativePath = str_replace(url('/') . '/', '', $file);
                $normalizedPath = ltrim($relativePath, '/');
                $fullPath = public_path($normalizedPath);
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
        }
    }
}


if (!function_exists('getTenantId')) {
    function getTenantId(): bool|int
    {
        if (session()->has('tenant_id')) {
            return session('tenant_id');
        }

        $request = request();
        if ($request->hasHeader('X-Tenant-ID')) {
            return $request->header('X-Tenant-ID');
        }

        if ($request->has('tenant_id')) {
            return $request->get('tenant_id');
        }

        return false;
    }
}

if (!function_exists('safeParseDate')) {
    function safeParseDate($value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        $value = str_replace(' - ', ' ', $value);
        if (preg_match('/\b(1[3-9]|2[0-3]):\d{2}\s?(AM|PM)\b/i', $value)) {
            $value = preg_replace('/\s?(AM|PM)\b/i', '', $value);
        }

        return Carbon::parse($value);
    }
}

if (!function_exists('transDB')) {
    function transDB($model, $field)
    {
        if (!$model) return '';
        $currentLang = session('lang', app()->getLocale());
        if (!empty($model->{$field . '_' . $currentLang})) {
            return $model->{$field . '_' . $currentLang};
        }

        $fallbacks = ['en', 'ar', 'fr', 'de'];

        foreach ($fallbacks as $lang) {
            if ($lang === $currentLang) continue;
            $value = $model->{$field . '_' . $lang} ?? null;
            if (!empty($value)) {
                return $value;
            }
        }

        return '';
    }
}
