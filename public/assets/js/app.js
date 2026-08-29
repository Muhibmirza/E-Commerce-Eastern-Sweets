(() => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  document.querySelector('[data-nav-toggle]')?.addEventListener('click', () => {
    document.querySelector('[data-nav]')?.classList.toggle('is-open');
    document.querySelector('.nav-actions')?.classList.toggle('is-open');
  });

  const slider = document.querySelector('[data-slider]');
  if (slider) {
    const slides = [...slider.querySelectorAll('.hero-slide')];
    const dots = [...slider.querySelectorAll('[data-slide-dot]')];
    let current = 0;
    let timer;
    if (slides.length > 1) {
      const show = (idx) => {
        current = (idx + slides.length) % slides.length;
        slides.forEach((slide, i) => slide.classList.toggle('is-active', i === current));
        dots.forEach((dot, i) => dot.classList.toggle('is-active', i === current));
        slider.classList.remove('is-timing');
        void slider.offsetWidth;
        slider.classList.add('is-timing');
      };
      const play = () => {
        clearInterval(timer);
        timer = setInterval(() => show(current + 1), 2000);
      };
      slider.querySelector('[data-slide-prev]')?.addEventListener('click', () => { show(current - 1); play(); });
      slider.querySelector('[data-slide-next]')?.addEventListener('click', () => { show(current + 1); play(); });
      dots.forEach((dot, i) => dot.addEventListener('click', () => { show(i); play(); }));
      slider.addEventListener('mouseenter', () => clearInterval(timer));
      slider.addEventListener('mouseleave', play);
      slider.classList.add('is-timing');
      play();
    }
  }

  const ensureLogoutModal = () => {
    let modal = document.querySelector('[data-logout-modal]');
    if (modal) return modal;
    modal = document.createElement('div');
    modal.className = 'modal-backdrop';
    modal.dataset.logoutModal = '';
    modal.innerHTML = `
      <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="logout-title">
        <h2 id="logout-title">Confirm logout</h2>
        <p>Are you sure you want to logout?</p>
        <div class="button-row">
          <button class="btn btn-outline" type="button" data-logout-cancel>Cancel</button>
          <a class="btn btn-primary" href="#" data-logout-confirm>Logout</a>
        </div>
      </div>`;
    document.body.appendChild(modal);
    modal.addEventListener('click', event => {
      if (event.target === modal || event.target.matches('[data-logout-cancel]')) {
        modal.classList.remove('is-open');
      }
    });
    return modal;
  };

  document.querySelectorAll('[data-confirm-logout]').forEach(link => {
    link.addEventListener('click', event => {
      event.preventDefault();
      const modal = ensureLogoutModal();
      modal.querySelector('[data-logout-confirm]').href = link.href;
      modal.classList.add('is-open');
    });
  });

  const escapeHtml = (value = '') => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  const ensureCartDrawer = () => {
    let drawer = document.querySelector('[data-cart-drawer]');
    if (drawer) return drawer;
    drawer = document.createElement('aside');
    drawer.className = 'cart-drawer';
    drawer.dataset.cartDrawer = '';
    drawer.setAttribute('aria-hidden', 'true');
    drawer.innerHTML = `
      <div class="cart-drawer-backdrop" data-cart-drawer-close></div>
      <div class="cart-drawer-panel" role="dialog" aria-modal="true" aria-labelledby="cart-drawer-title">
        <header>
          <div>
            <p class="eyebrow">Added to cart</p>
            <h2 id="cart-drawer-title">Your sweets box</h2>
          </div>
          <button class="icon-button" type="button" data-cart-drawer-close aria-label="Close cart">x</button>
        </header>
        <div class="cart-drawer-body" data-cart-drawer-items></div>
        <footer data-cart-drawer-summary></footer>
      </div>`;
    document.body.appendChild(drawer);
    drawer.addEventListener('click', event => {
      if (event.target.matches('[data-cart-drawer-close]')) closeCartDrawer();
    });
    return drawer;
  };

  const openCartDrawer = (payload) => {
    if (!payload) return;
    const drawer = ensureCartDrawer();
    const items = drawer.querySelector('[data-cart-drawer-items]');
    const summary = drawer.querySelector('[data-cart-drawer-summary]');
    const rows = payload.items || [];

    items.innerHTML = rows.length ? rows.map(item => `
      <article class="drawer-cart-item">
        <img src="${escapeHtml(item.image)}" alt="">
        <div>
          <strong>${escapeHtml(item.name)}</strong>
          <span>${escapeHtml(item.variant)} x ${escapeHtml(item.quantity)}</span>
          <small>${escapeHtml(item.line_total)}</small>
        </div>
      </article>`).join('') : '<div class="empty-state">Your cart is empty.</div>';

    summary.innerHTML = `
      <p><span>Subtotal</span><strong>${escapeHtml(payload.subtotal || 'Rs. 0')}</strong></p>
      <p><span>Delivery</span><strong>${escapeHtml(payload.delivery || 'Rs. 0')}</strong></p>
      <p><span>Discount</span><strong>-${escapeHtml(payload.discount || 'Rs. 0')}</strong></p>
      <p class="total"><span>Total</span><strong>${escapeHtml(payload.total || 'Rs. 0')}</strong></p>
      <div class="button-row">
        <a class="btn btn-outline" href="${escapeHtml(payload.cart_url || '/cart')}">View Cart</a>
        <a class="btn btn-primary" href="${escapeHtml(payload.checkout_url || '/checkout')}">Checkout</a>
      </div>
      <a class="continue-link" href="${escapeHtml(payload.shop_url || '/shop')}" data-cart-drawer-close>Continue Shopping</a>`;

    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    document.body.classList.add('drawer-open');
  };

  const closeCartDrawer = () => {
    const drawer = document.querySelector('[data-cart-drawer]');
    if (!drawer) return;
    drawer.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('drawer-open');
  };

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closeCartDrawer();
  });

  document.querySelectorAll('[data-account-tabs]').forEach(tabList => {
    tabList.addEventListener('click', event => {
      const button = event.target.closest('[data-account-tab]');
      if (!button) return;
      const name = button.dataset.accountTab;
      tabList.querySelectorAll('[data-account-tab]').forEach(tab => {
        tab.classList.toggle('is-active', tab.dataset.accountTab === name);
      });
      document.querySelectorAll('[data-account-panel]').forEach(panel => {
        panel.classList.toggle('is-active', panel.dataset.accountPanel === name);
      });
    });
  });

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) entry.target.classList.add('is-visible');
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.reveal').forEach((el, index) => {
    el.style.setProperty('--reveal-delay', `${Math.min(index % 8, 7) * 55}ms`);
    observer.observe(el);
  });

  if (window.matchMedia('(pointer: fine)').matches) {
    document.querySelectorAll('.category-card, .product-card, .stat-card, .summary-card, .map-card').forEach(card => {
      card.classList.add('tilt-card');
      card.addEventListener('mousemove', event => {
        const rect = card.getBoundingClientRect();
        const rotateY = ((event.clientX - rect.left) / rect.width - .5) * 7;
        const rotateX = (((event.clientY - rect.top) / rect.height - .5) * -7);
        card.style.setProperty('--card-rx', `${rotateX.toFixed(2)}deg`);
        card.style.setProperty('--card-ry', `${rotateY.toFixed(2)}deg`);
      });
      card.addEventListener('mouseleave', () => {
        card.style.setProperty('--card-rx', '0deg');
        card.style.setProperty('--card-ry', '0deg');
      });
    });
  }

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const bannerObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        bannerObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.35 });

  document.querySelectorAll('[data-category-banner]').forEach(banner => {
    bannerObserver.observe(banner);
    const isMithaiSpin = banner.classList.contains('is-mithai-spin');
    const isSlide3d = banner.classList.contains('is-slide-3d') || banner.classList.contains('is-basic-banner');
    if (isMithaiSpin) {
      const plate = banner.querySelector('.category-plate-wrap');
      if (reducedMotion) {
        banner.classList.add('is-visible');
        return;
      }
      plate?.addEventListener('animationend', event => {
        if (event.animationName === 'mithaiPlateEntry') {
          banner.classList.add('is-idle');
        }
      }, { once: true });
      return;
    }
    if (isSlide3d) {
      const plate = banner.querySelector('.category-plate-wrap');
      if (reducedMotion) {
        banner.classList.add('is-visible', 'is-settled');
        return;
      }
      plate?.addEventListener('animationend', event => {
        if (event.animationName === 'categorySlide3dEntry') {
          banner.classList.add('is-settled');
        }
      }, { once: true });
    }
    if (reducedMotion || !window.matchMedia('(pointer: fine)').matches) return;

    const stage = banner.querySelector('.category-plate-stage');
    if (!stage) return;
    let targetX = 2;
    let targetY = -5;
    let currentX = 2;
    let currentY = -5;
    let raf = null;

    const animateTilt = () => {
      currentX += (targetX - currentX) * 0.12;
      currentY += (targetY - currentY) * 0.12;
      stage.style.setProperty('--tilt-x', `${currentX.toFixed(2)}deg`);
      stage.style.setProperty('--tilt-y', `${currentY.toFixed(2)}deg`);
      raf = requestAnimationFrame(animateTilt);
    };

    banner.addEventListener('mousemove', event => {
      const rect = banner.getBoundingClientRect();
      const x = ((event.clientX - rect.left) / rect.width) - 0.5;
      const y = ((event.clientY - rect.top) / rect.height) - 0.5;
      targetX = Math.max(-8, Math.min(8, y * -14 + 2));
      targetY = Math.max(-10, Math.min(10, x * 18 - 5));
      if (!raf) animateTilt();
    });

    banner.addEventListener('mouseleave', () => {
      targetX = 2;
      targetY = -5;
    });
  });

  document.querySelectorAll('.ajax-cart').forEach(form => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const button = form.querySelector('button[type="submit"]');
      const original = button?.textContent;
      if (button) button.textContent = 'Adding...';
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-CSRF-Token': csrf }
      });
      const data = await response.json();
      if (button) button.textContent = original;
      if (data.ok) {
        const badge = document.querySelector('[data-cart-count]');
        if (badge) {
          badge.textContent = data.count;
          badge.closest('.cart-link')?.classList.add('pop');
          setTimeout(() => badge.closest('.cart-link')?.classList.remove('pop'), 300);
        }
        openCartDrawer(data.drawer);
      } else {
        alert(data.message || 'Unable to update cart.');
      }
    });
  });

  document.querySelectorAll('input[type="file"][data-live-preview]').forEach(input => {
    input.addEventListener('change', () => {
      const preview = document.querySelector(input.dataset.livePreview);
      const file = input.files && input.files[0];
      if (!preview || !file) return;
      preview.src = URL.createObjectURL(file);
      preview.classList.remove('is-empty');
    });
  });

  document.querySelectorAll('.cart-update input[name="quantity"]').forEach(input => {
    input.addEventListener('change', () => input.form?.requestSubmit());
  });
  document.querySelectorAll('.cart-update').forEach(form => {
    form.addEventListener('submit', async event => {
      event.preventDefault();
      await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'X-CSRF-Token': csrf } });
      window.location.reload();
    });
  });
  document.querySelectorAll('[data-remove-cart]').forEach(button => {
    button.addEventListener('click', async () => {
      const form = document.querySelector(`[data-cart-row="${button.dataset.removeCart}"] .cart-update`);
      if (!form) return;
      form.querySelector('input[name="quantity"]').value = '0';
      form.requestSubmit();
    });
  });

  document.querySelectorAll('[data-step]').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.parentElement.querySelector('input');
      const next = Math.max(1, Number(input.value || 1) + Number(btn.dataset.step));
      input.value = String(next);
    });
  });
  document.querySelector('[data-variant-select]')?.addEventListener('change', event => {
    document.querySelector('[data-price]').textContent = event.target.selectedOptions[0].dataset.price;
  });
  document.querySelectorAll('[data-thumb]').forEach(btn => {
    btn.addEventListener('click', () => {
      const main = document.querySelector('[data-main-image]');
      if (main) main.src = btn.dataset.thumb;
    });
  });
  document.querySelectorAll('[data-tabs]').forEach(tabs => {
    tabs.addEventListener('click', event => {
      if (!event.target.matches('[data-tab]')) return;
      const tab = event.target.dataset.tab;
      tabs.querySelectorAll('[data-tab]').forEach(btn => btn.classList.toggle('is-active', btn.dataset.tab === tab));
      document.querySelectorAll('[data-panel]').forEach(panel => panel.classList.toggle('is-active', panel.dataset.panel === tab));
    });
  });
})();
