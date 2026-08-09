(() => {
  const navToggle = document.querySelector('[data-nav-toggle]');
  const nav = document.querySelector('[data-nav]');
  navToggle?.addEventListener('click', () => {
    const open = nav?.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });

  const countryFilter = document.querySelector('[data-country-filter]');
  const countryCards = [...document.querySelectorAll('[data-country]')];
  const countryEmpty = document.querySelector('[data-country-empty]');
  const normalize = value => (value || '').trim().toLowerCase().replace(/\s+/g, ' ');
  const filterCountries = () => {
    if (!countryCards.length) return;
    const term = normalize(countryFilter?.value);
    let visible = 0;
    countryCards.forEach(card => {
      const match = normalize(card.dataset.country).includes(term);
      card.hidden = !match;
      card.setAttribute('aria-hidden', match ? 'false' : 'true');
      if (match) visible += 1;
    });
    if (countryEmpty) countryEmpty.hidden = visible !== 0;
  };
  countryFilter?.addEventListener('input', filterCountries);
  filterCountries();

  const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
  const sidebar = document.querySelector('[data-sidebar]');
  sidebarToggle?.addEventListener('click', () => sidebar?.classList.toggle('open'));
  document.addEventListener('click', event => {
    if (!sidebar || !sidebarToggle || window.innerWidth > 850) return;
    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) sidebar.classList.remove('open');
  });

  const countryScope = document.querySelector('[data-country-scope]');
  const regimeScope = document.querySelector('[data-regime-scope]');
  const syncScope = () => {
    if (!countryScope || !regimeScope) return;
    regimeScope.disabled = Boolean(countryScope.value);
    if (countryScope.value) regimeScope.value = '';
  };
  countryScope?.addEventListener('change', syncScope);
  syncScope();

  document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', () => {
      const label = document.querySelector(`label[for="${input.id}"] strong`);
      if (label && input.files?.[0]) label.textContent = input.files[0].name;
    });
  });
})();
