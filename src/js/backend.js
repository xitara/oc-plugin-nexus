// import { qs, qsa, $on, $delegate } from './utils';
import { qs, $on } from './utils';

$on(document, 'DOMContentLoaded', () => {
    /**
     * mobile nav trigger
     */
    if (qs('#mobile-show')) {
        $on(qs('#mobile-show'), 'click', () => {
            qs('#mobile-hide').classList.add('active');
            qs('.sidenav-tree > .layout').classList.add('active');
        });
    }
    if (qs('#mobile-hide')) {
        $on(qs('#mobile-hide'), 'click', () => {
            qs('#mobile-hide').classList.remove('active');
            qs('.sidenav-tree > .layout').classList.remove('active');
        });
    }

    /**
     * add dark mode checkbox to bacend user menu
     */
    let theme = localStorage.getItem('backendTheme'),
        $body = qs('body'),
        lang = qs('html').getAttribute('lang'),
        textCheckbox = {
            de: 'Dunkel Modus',
            en: 'Dark Mode',
        },
        localeText = textCheckbox[lang] ? textCheckbox[lang] : 'Dark Mode';

    theme == 'darkmode' ? $body.classList.add('darkmode') : 0;

    const darkModeId = 'darkmodeTheme';

    let toggleDarkMode = document.createElement('li');
    toggleDarkMode.className = 'darkmode-theme';

    let darkModeHtml = `
        <div class="checkbox custom-checkbox" style="margin:5px 30px 4px">
            <label for="${darkModeId}">${localeText}</label>
        </div>
    `;

    toggleDarkMode.innerHTML = darkModeHtml;

    let checkDarkMode = document.createElement('input');
    checkDarkMode.id = darkModeId;
    checkDarkMode.setAttribute('name', 'checkbox');
    checkDarkMode.setAttribute('type', 'checkbox');
    theme == 'darkmode' ? checkDarkMode.setAttribute('checked', 'checked') : 0;

    qs('label', toggleDarkMode).before(checkDarkMode);
    qs('.mainmenu-accountmenu li.divider').before(toggleDarkMode);

    checkDarkMode.addEventListener('input', () => {
        if (checkDarkMode.checked) {
            $body.classList.add('darkmode');
            localStorage.setItem('backendTheme', 'darkmode');
        } else {
            $body.classList.remove('darkmode');
            localStorage.removeItem('backendTheme');
        }
    });
});

import '../scss/backend.scss';
