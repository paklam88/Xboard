/**
 * Auth chrome: back-to-home + keep theme toggle reachable.
 * Landing and auth share stellar_dark / isDark — do not force dark.
 */
(function () {
  var path = (location.pathname || '/').replace(/\/+$/, '') || '/';
  if (!/^\/(login|register|forget)$/.test(path)) return;

  function mount() {
    if (document.querySelector('.xb-auth-nav')) return;
    var page = document.querySelector('.auth-page');
    if (!page) return false;

    var nav = document.createElement('nav');
    nav.className = 'xb-auth-nav';
    nav.setAttribute('aria-label', 'auth');
    nav.innerHTML =
      '<a class="xb-auth-back" href="/">' +
      '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
      '<path d="M15 18l-6-6 6-6"/>' +
      '</svg>' +
      '<span class="xb-auth-back-label">返回首页</span>' +
      '</a>' +
      '<div class="xb-auth-nav-right"></div>';

    var right = nav.querySelector('.xb-auth-nav-right');
    var themeBtn = document.querySelector('.auth-page .auth-top-actions .header-btn');
    if (themeBtn && right) {
      themeBtn.classList.add('xb-auth-theme-btn');
      themeBtn.setAttribute('aria-label', '切换深浅色');
      themeBtn.title = '切换深浅色';
      right.appendChild(themeBtn);
    }

    page.insertBefore(nav, page.firstChild);
    return true;
  }

  function tryMount() {
    if (mount()) return;
    var tries = 0;
    var timer = setInterval(function () {
      tries += 1;
      if (mount() || tries > 40) clearInterval(timer);
    }, 150);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tryMount);
  } else {
    tryMount();
  }

  // SPA navigations between login/register/forget
  var _push = history.pushState;
  history.pushState = function () {
    _push.apply(this, arguments);
    setTimeout(tryMount, 50);
  };
  window.addEventListener('popstate', function () {
    setTimeout(tryMount, 50);
  });
})();
