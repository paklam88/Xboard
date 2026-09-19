<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="UTF-8">
    <link href="/theme/{{ $theme }}/favicon.ico" rel="icon">
    <link href="https://fonts.googlefonts.cn/css?family=Fredoka+One|Rubik:500" rel="stylesheet">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>{{ $title ?? 'XBoard' }}</title>
    <script src="/theme/{{ $theme }}/config.js"></script>
    <script>
      // keep title in sync with panel settings when available
      if (window.config) {
        window.config.title = @json($title ?? 'XBoard Demo');
      }
    </script>
    <script type="module" crossorigin src="/theme/{{ $theme }}/assets/index-trPnz5BS.js"></script>
    <link rel="stylesheet" crossorigin href="/theme/{{ $theme }}/assets/index-BRrM5O5l.css">
  </head>
  <body>
    <div id="app"></div>
    {!! $theme_config['custom_html'] ?? '' !!}
  </body>
</html>
