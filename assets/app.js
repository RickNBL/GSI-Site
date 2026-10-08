/* Navegação local para prévia do site salvo, sem trocar os URLs originais. */
(function () {
  'use strict';
  const toggle = document.querySelector('.nav-toggle');
  const nav = document.querySelector('.site-nav');
  const closeMenu = () => {
    document.body.classList.remove('menu-open');
    if (toggle) toggle.setAttribute('aria-expanded', 'false');
  };
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const isOpen = document.body.classList.toggle('menu-open');
      toggle.setAttribute('aria-expanded', String(isOpen));
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') closeMenu();
    });
    document.addEventListener('click', (event) => {
      if (document.body.classList.contains('menu-open') && !event.target.closest('.site-header')) closeMenu();
    });
  }
  const known = new Set([
    '/', '/a-gsi/', '/a-sitcon/', '/solucoes/',
    '/solucoes/gestao-e-suporte-de-ti/', '/solucoes/locacao-de-equipamentos/',
    '/solucoes/outsourcing-de-impressao/', '/segmentos/', '/cases/',
    '/conteudos/', '/contato/', '/area-do-cliente/'
  ]);
  const root = document.documentElement.getAttribute('data-site-root') || './';
  const rootUrl = new URL(root, window.location.href);
  document.addEventListener('click', (event) => {
    const anchor = event.target.closest('a[href]');
    if (!anchor || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || anchor.hasAttribute('download')) return;
    const href = anchor.getAttribute('href');
    if (!href || href.startsWith('#')) return;
    let target;
    try { target = new URL(href, window.location.href); } catch (_) { return; }
    if (target.hostname !== 'gsi.tec.br' && target.hostname !== 'www.gsi.tec.br') return;
    const pathname = target.pathname.endsWith('/') ? target.pathname : target.pathname + '/';
    if (!known.has(pathname)) return;
    /* Se hospedado no domínio original, mantém os links oficiais exatamente como estão. */
    if (window.location.protocol !== 'file:' && ['gsi.tec.br','www.gsi.tec.br'].includes(location.hostname)) return;
    event.preventDefault();
    closeMenu();
    const route = pathname === '/' ? 'index.html' : pathname.slice(1) + 'index.html';
    window.location.assign(new URL(route + target.hash, rootUrl).href);
  });
})();

/* Resposta do formulário de contato, sem afetar o conteúdo estático do site. */
(function () {
  const form = document.querySelector('.sitcon-commercial-form');
  if (!form) return;
  const result = new URLSearchParams(location.search).get('envio');
  if (result !== 'ok' && result !== 'erro') return;
  const message = document.createElement('p');
  message.setAttribute('role', 'status');
  message.style.cssText = 'margin:0 0 1rem;padding:0.8rem 1rem;border:1px solid #cbd5e1;border-radius:6px;color:#172231;background:#f8fafc;font-weight:600';
  message.textContent = result === 'ok' ? 'Solicitação enviada com sucesso.' : 'Não foi possível enviar sua solicitação. Tente novamente.';
  form.before(message);
})();
