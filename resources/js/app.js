// نقطة الدخول: وحدات مشتركة + تحميل كسول لكل صفحة
document.documentElement.classList.add('js');

import { initNav } from './lib/nav.js';
import { initBars, initCopy, initConfirm } from './lib/ui.js';

initNav();
initBars();
initCopy();
initConfirm();

const pages = {
  home: () => import('./pages/home.js'),
  participate: () => import('./pages/participate.js'),
  success: () => import('./pages/success.js'),
  explore: () => import('./pages/explore.js'),
  display: () => import('./pages/display.js'),
  admin: () => import('./pages/admin.js'),
};

const page = document.body.dataset.page;
pages[page]?.().then((m) => m.default?.());
