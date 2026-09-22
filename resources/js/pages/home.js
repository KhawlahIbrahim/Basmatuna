import { fingerprintField } from '../lib/field.js';

export default function () {
  const canvas = document.querySelector('[data-field]');
  if (canvas) fingerprintField(canvas, { density: window.innerWidth < 600 ? 0.7 : 1 });
}
