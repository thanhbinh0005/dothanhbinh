(() => {
  const toggle = document.querySelector('.menu-toggle');
  const navigation = document.querySelector('.navigation');
  const closeMenu = () => { toggle?.setAttribute('aria-expanded', 'false'); navigation?.classList.remove('is-open'); };
  toggle?.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') !== 'true';
    toggle.setAttribute('aria-expanded', String(open)); navigation.classList.toggle('is-open', open);
  });
  navigation?.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
  document.addEventListener('keydown', event => { if (event.key === 'Escape') closeMenu(); });
  const cards = [...document.querySelectorAll('.product-card')];
  const counter = document.querySelector('.product-count');
  if (counter) counter.textContent = `${cards.length} giải pháp`;
  document.querySelectorAll('[data-filter]').forEach(tab => tab.addEventListener('click', () => {
    document.querySelectorAll('[data-filter]').forEach(item => { item.classList.toggle('active', item === tab); item.setAttribute('aria-pressed', String(item === tab)); });
    cards.forEach(card => { card.hidden = tab.dataset.filter !== 'all' && !card.dataset.category.split(' ').includes(tab.dataset.filter); });
    if (counter) counter.textContent = `${cards.filter(card => !card.hidden).length} giải pháp`;
  }));
  const form = document.querySelector('.quote-form');
  if (!form || !window.fetch) return;
  form.addEventListener('submit', async event => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const button = form.querySelector('button[type="submit"]');
    const label = button.querySelector('span');
    const feedback = document.querySelector('#quote-feedback');
    if (button.disabled) return;
    button.disabled = true; label.textContent = 'Đang gửi yêu cầu…';
    feedback.hidden = true;
    const data = new FormData(form); data.set('tb_json', '1');
    try {
      const response = await fetch(form.getAttribute('action'), { method: 'POST', body: data, credentials: 'same-origin', headers: { Accept: 'application/json' } });
      const result = await response.json();
      feedback.textContent = result.message || 'Chưa gửi được yêu cầu. Vui lòng thử lại.';
      feedback.classList.toggle('success', !!result.success);
      if (result.success) form.reset();
    } catch {
      feedback.classList.remove('success');
      feedback.textContent = 'Chưa xác định được kết quả gửi do kết nối gián đoạn. Vui lòng kiểm tra với công ty trước khi gửi lại.';
    } finally {
      feedback.hidden = false; feedback.setAttribute('tabindex', '-1'); feedback.focus();
      button.disabled = false; label.textContent = 'Gửi yêu cầu báo giá';
    }
  });
})();
