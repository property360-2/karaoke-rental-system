// Theme toggle logic
function setTheme(mode) {
    document.body.classList.remove('dark-mode', 'light-mode');
    document.body.classList.add(mode + '-mode');
}

function getPreferredTheme() {
    if (localStorage.getItem('theme')) {
        return localStorage.getItem('theme');
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

document.addEventListener('DOMContentLoaded', function () {
    const themeToggle = document.getElementById('themeToggle');
    const preferred = getPreferredTheme();
    setTheme(preferred);
    if (themeToggle) {
        themeToggle.checked = preferred === 'dark';

        themeToggle.addEventListener('change', function () {
            const mode = this.checked ? 'dark' : 'light';
            setTheme(mode);
            localStorage.setItem('theme', mode);
        });

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (!localStorage.getItem('theme')) {
                setTheme(e.matches ? 'dark' : 'light');
                themeToggle.checked = e.matches;
            }
        });
    }
}); 