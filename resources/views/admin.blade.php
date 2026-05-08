<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', config('cms.admin_locale', config('cms.default_locale', 'en'))) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('cms::cms.admin_panel_title') }}</title>
    <script>
        @php
            $locale = config('cms.admin_locale', config('cms.default_locale', 'en'));
        @endphp
        window.CMS_CONFIG = {
            adminPrefix: '{{ config('cms.admin_prefix', 'admin') }}',
            locale: '{{ $locale }}',
        };
    </script>
    @php
        $adminScriptUrl = getCmsBuildAssetUrl('admin.js') ?? asset('vendor/reno/cms/build/admin.js');
        $adminCssUrls = getCmsBuildCssUrls('admin.js');
        $translationsScriptUrl = asset('vendor/reno/cms/build/i18n/' . $locale . '.js');
        $fallbackTranslationsScriptUrl = asset('vendor/reno/cms/build/i18n/en.js');
    @endphp
    @foreach ($adminCssUrls as $adminCssUrl)
        <link rel="stylesheet" href="{{ $adminCssUrl }}">
    @endforeach
    <script src="{{ $translationsScriptUrl }}" onerror="this.onerror=null;this.src='{{ $fallbackTranslationsScriptUrl }}';"></script>
    <script type="module" src="{{ $adminScriptUrl }}"></script>
</head>
<body>
    <div id="admin-app"></div>
</body>
</html>

