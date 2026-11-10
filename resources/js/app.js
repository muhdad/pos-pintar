import './bootstrap';
import './thermal-printer';
import { posTerminal } from './pos-offline';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.data('posTerminal', posTerminal);

Alpine.start();
