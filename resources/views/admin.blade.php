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
    @endphp
    @foreach ($adminCssUrls as $adminCssUrl)
        <link rel="stylesheet" href="{{ $adminCssUrl }}">
    @endforeach
    <script type="module" src="{{ $adminScriptUrl }}"></script>
</head>
<body>
    <div id="admin-app"></div>
</body>
</html>

