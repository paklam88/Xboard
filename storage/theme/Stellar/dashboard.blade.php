<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <link rel="icon" type="image/svg+xml" href="/theme/{{ $theme }}/favicon.svg" />
  <title>{{ $title ?? 'XBoard' }}</title>
  <script>
    // Auth pages must follow landing dark shell (landing_theme_mode: dark).
    // Force before Vue boot so Naive UI does not paint white-on-white inputs.
    (function () {
      var path = (location.pathname || '/').replace(/\/+$/, '') || '/';
      if (/^\/(login|register|forget)$/.test(path)) {
        try { localStorage.setItem('stellar_dark', 'true'); } catch (e) {}
        document.documentElement.classList.add('dark');
      }
    })();
  </script>
  <style>
    .stellar-page-loader{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:#0a0f1a}
    .stellar-page-loader__spinner{width:36px;height:36px;border:3px solid rgba(148,163,184,.22);border-top-color:#3b82f6;border-radius:50%;animation:stellar-loader-spin .75s linear infinite}
    @keyframes stellar-loader-spin{to{transform:rotate(360deg)}}
  </style>
  <link rel="stylesheet" href="/theme/{{ $theme }}/auth-atmosphere.css?v=20260919c" />
  <script>
    window.routerBase = '/';
    window.settings = window.settings || {};
  </script>
  <script src="/theme/{{ $theme }}/env.js"></script>
  <script>
    if (window.settings) {
      window.settings.title = @json($title ?? 'XBoard');
      (function () {
        var backendDesc = @json($description ?? '');
        var bad = !backendDesc || /^\s*$/.test(backendDesc) || /^xboard\s+is\s+best[!?.…]*$/i.test(backendDesc.trim());
        if (!bad) {
          window.settings.description = backendDesc;
        } else {
          window.settings.description = window.settings.description || '安全连接，畅行全球';
        }
      })();
      window.settings.assets_path = '/theme/{{ $theme }}/assets';
      if (window.settings.api) {
        window.settings.api.url_mode = 'auto';
        window.settings.api.backend_type = 'xboard';
        window.settings.api.static_base_urls = [window.location.origin];
      }
      window.settings.landing_page_enabled = true;
    }
  </script>
  <script type="module" crossorigin src="/theme/{{ $theme }}/assets/stellar.js"></script>
  <link rel="stylesheet" crossorigin href="/theme/{{ $theme }}/assets/index-C-z4KtI8.css">
</head>
<body>
  <div id="app">
    <div class="stellar-page-loader"><div class="stellar-page-loader__spinner"></div></div>
  </div>
</body>
</html>
