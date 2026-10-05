document.addEventListener('DOMContentLoaded', () => {
  const page = document.body?.dataset?.page || '';
  if (!page || page !== 'article') {
    return;
  }

  const widgets = document.querySelectorAll('.button, .btn-action, .primary-btn, .secondary-btn');
  widgets.forEach((button) => {
    button.addEventListener('mouseenter', () => {
      button.style.transform = 'translateY(-1px)';
    });

    button.addEventListener('mouseleave', () => {
      button.style.transform = '';
    });
  });
});
