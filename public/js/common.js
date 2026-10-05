document.addEventListener('DOMContentLoaded', () => {
  const alertItems = document.querySelectorAll('.alert');
  alertItems.forEach((alert) => {
    if (!alert.dataset.autoHide) {
      alert.dataset.autoHide = 'true';
    }
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-6px)';
      alert.style.transition = 'all 0.25s ease';
      setTimeout(() => alert.remove(), 260);
    }, 5000);
  });

  document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
      const selector = button.getAttribute('data-toggle-password');
      const input = selector ? document.querySelector(selector) : null;
      if (!input) {
        return;
      }

      const shouldShow = input.type === 'password';
      input.type = shouldShow ? 'text' : 'password';
      button.innerHTML = shouldShow
        ? '<i class="fa-regular fa-eye-slash"></i>'
        : '<i class="fa-regular fa-eye"></i>';
      button.setAttribute('aria-label', shouldShow ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
      input.focus();
    });
  });

  const page = document.body.dataset.page;
  if (page) {
    document.body.classList.add('page-' + page);
  }
});
