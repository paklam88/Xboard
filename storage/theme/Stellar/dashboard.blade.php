<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <link rel="icon" type="image/svg+xml" href="/theme/{{ $theme }}/favicon.svg" />
  <title>{{ $title ?? 'XBoard' }}</title>
  <style>
    .stellar-page-loader{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:#eef6f9}
    .stellar-page-loader__spinner{width:36px;height:36px;border:3px solid rgba(14,116,144,.18);border-top-color:#0e7490;border-radius:50%;animation:stellar-loader-spin .75s linear infinite}
    @keyframes stellar-loader-spin{to{transform:rotate(360deg)}}
    @media (prefers-color-scheme: dark){
      .stellar-page-loader{background:#0b1220}
      .stellar-page-loader__spinner{border-color:rgba(34,211,238,.18);border-top-color:#22d3ee}
    }
  </style>
  <link rel="stylesheet" href="/theme/{{ $theme }}/auth-atmosphere.css?v=20260919a" />
  <script>
    window.routerBase = '/';
    window.settings = window.settings || {};
  </script>
  <script src="/theme/{{ $theme }}/env.js"></script>
  <script>
    if (window.settings) {
      window.settings.title = @json($title ?? 'XBoard');
      // Prefer themed copy from env.js when backend still has the old empty slogan.
      (function () {
        var backendDesc = @json($description ?? '');
        var bad = !backendDesc || /^\s*$/.test(backendDesc) || /^xboard\s+is\s+best[!?.…]*$/i.test(backendDesc.trim());
        if (!bad) {
          window.settings.description = backendDesc;
        } else {
          // Keep env.js copy; fall back if unset
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
