/**
 * Auth chrome outside Vue tree (body), so Vue re-renders cannot wipe it.
 * - 返回首页 → /
 * - 深浅色：驱动与落地页相同的 stellar_dark + 点击内置 header-btn
 */
(function () {
  var NAV_CLASS = 'xb-auth-nav';

  function path() {
    return (location.pathname || '/').replace(/\/+$/, '') || '/';
  }

  function isAuth() {
    return /^\/(login|register|forget)$/.test(path());
  }

  function isDark() {
    return document.documentElement.classList.contains('dark') ||
      localStorage.getItem('stellar_dark') === 'true';
  }

  function themeSvg(dark) {
    // dark mode → show sun (switch to light); light → show moon
    if (dark) {
      return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>';
    }
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
  }

  function syncThemeButton(nav) {
    var btn = nav && nav.querySelector('.xb-auth-theme-btn');
    if (!btn) return;
    var dark = isDark();
    btn.innerHTML = themeSvg(dark);
    btn.setAttribute('aria-label', dark ? '切换到浅色' : '切换到深色');
    btn.title = dark ? '切换到浅色' : '切换到深色';
  }

  function toggleTheme() {
    var builtIn = document.querySelector('.auth-page .auth-top-actions .header-btn');
    if (builtIn) {
      builtIn.click();
      setTimeout(function () {
        syncThemeButton(document.querySelector('.' + NAV_CLASS));
      }, 50);
      return;
    }
    var next = !isDark();
    try {
      localStorage.setItem('stellar_dark', next ? 'true' : 'false');
    } catch (e) {}
    document.documentElement.classList.toggle('dark', next);
    syncThemeButton(document.querySelector('.' + NAV_CLASS));
  }

  function buildNav() {
    var nav = document.createElement('nav');
    nav.className = NAV_CLASS;
    nav.setAttribute('aria-label', 'auth navigation');
    nav.innerHTML =
      '<a class="xb-auth-back" href="/">' +
      '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>' +
      '<span class="xb-auth-back-label">返回首页</span>' +
      '</a>' +
      '<div class="xb-auth-nav-right">' +
      '<button type="button" class="xb-auth-theme-btn" aria-label="切换深浅色"></button>' +
      '</div>';
    nav.querySelector('.xb-auth-theme-btn').addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      toggleTheme();
    });
    syncThemeButton(nav);
    return nav;
  }

  function ensure() {
    var existing = document.querySelector('.' + NAV_CLASS);
    if (!isAuth()) {
      if (existing) existing.remove();
      document.documentElement.classList.remove('xb-auth-route');
      return;
    }
    document.documentElement.classList.add('xb-auth-route');
    if (!existing) {
      document.body.appendChild(buildNav());
    } else {
      syncThemeButton(existing);
    }
  }

  var scheduled = false;
  function schedule() {
    if (scheduled) return;
    scheduled = true;
    requestAnimationFrame(function () {
      scheduled = false;
      ensure();
    });
  }

  ensure();
  document.addEventListener('DOMContentLoaded', ensure);
  window.addEventListener('load', ensure);
  window.addEventListener('popstate', schedule);

  var _push = history.pushState;
  var _replace = history.replaceState;
  history.pushState = function () {
    _push.apply(this, arguments);
    schedule();
  };
  history.replaceState = function () {
    _replace.apply(this, arguments);
    schedule();
  };

  new MutationObserver(schedule).observe(document.documentElement, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['class'],
  });

  setInterval(ensure, 800);
})();
