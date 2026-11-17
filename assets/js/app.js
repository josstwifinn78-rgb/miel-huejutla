const menuButton = document.querySelector('.menu-toggle');
const menu = document.querySelector('.main-nav');
if (menuButton && menu) menuButton.addEventListener('click', () => {
  const open = menu.classList.toggle('is-open');
  menuButton.setAttribute('aria-expanded', String(open));
});

const mainProductImage = document.querySelector('#imagenPrincipalProducto');
document.querySelectorAll('.gallery-thumb').forEach((button) => {
  button.addEventListener('click', () => {
    if (!mainProductImage) return;
    mainProductImage.src = button.dataset.image;
    mainProductImage.alt = button.dataset.alt || '';
  });
});
