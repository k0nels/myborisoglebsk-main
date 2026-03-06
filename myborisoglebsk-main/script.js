document.addEventListener("DOMContentLoaded", () => {

    /**
     * 1. Инициализация AOS (Animate On Scroll)
     */
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });
    }
    
    /**
     * 2. Эффект печатной машинки (Typed.js)
     */
    const typedSubtitle = document.getElementById('typed-subtitle');
    if (typeof Typed !== 'undefined' && typedSubtitle) {
        new Typed('#typed-subtitle', {
            strings: ['новости.', 'события.', 'афишу.', 'многое другое.'],
            typeSpeed: 70,
            backSpeed: 40,
            loop: true,
            backDelay: 2000,
        });
    }

    /**
     * 3. Переключатель тем
     */
    const themeSwitcher = document.getElementById('theme-switcher');
    if (themeSwitcher) {
        const body = document.body;
        const applyTheme = (theme) => {
            body.classList.toggle('dark-theme', theme === 'dark');
            themeSwitcher.checked = (theme === 'dark');
        };
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(savedTheme || (prefersDark ? 'dark' : 'light'));
        themeSwitcher.addEventListener('change', () => {
            const newTheme = themeSwitcher.checked ? 'dark' : 'light';
            localStorage.setItem('theme', newTheme);
            applyTheme(newTheme);
        });
    }

    /**
     * 4. Анимация строки поиска в шапке
     */
    const searchWrapper = document.getElementById('search-wrapper');
    const searchInput = document.getElementById('search-field');
    if (searchWrapper && searchInput) {
        searchWrapper.addEventListener('click', (e) => {
            if (e.target.id !== 'search-field') {
                searchWrapper.classList.add('active');
                searchInput.focus();
            }
        });
        searchInput.addEventListener('blur', () => { if (!searchInput.value) searchWrapper.classList.remove('active'); });
    }

    /**
     * 5. Десктопное МЕГА-МЕНЮ
     */
    const menuToggle = document.getElementById('desktop-menu-toggle');
    const megaMenu = document.getElementById('mega-menu');
    if (menuToggle && megaMenu) {
        const menuToggleText = menuToggle.querySelector('.burger-text');
        menuToggle.addEventListener('click', (e) => {
            e.stopPropagation(); 
            const isActive = menuToggle.classList.toggle('active');
            megaMenu.classList.toggle('active');
            if (menuToggleText) menuToggleText.textContent = isActive ? 'ЗАКРЫТЬ' : 'МЕНЮ';
        });
    }
    
    /**
     * 7. Swiper Slider
     */
    const attractionsSlider = document.querySelector('.attractions-slider');
    if (typeof Swiper !== 'undefined' && attractionsSlider) {
        new Swiper(attractionsSlider, {
            slidesPerView: 'auto',
            spaceBetween: 30,
            loop: true,
            autoplay: { delay: 2500, disableOnInteraction: false },
        });
    }

    /**
     * 8. Кнопка "Наверх"
     */
    const backToTopButton = document.querySelector('.back-to-top');
    if (backToTopButton) {
        window.addEventListener('scroll', () => { backToTopButton.classList.toggle('active', window.scrollY > 300); });
        backToTopButton.addEventListener('click', (e) => { e.preventDefault(); window.scrollTo({ top: 0, behavior: 'smooth' }); });
    }

    /**
     * 10. Инициализация всплывающих подсказок Bootstrap
     */
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (typeof bootstrap !== 'undefined' && tooltipTriggerList.length > 0) {
        [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    }

    /**
     * 11. Валидация и ОТПРАВКА формы обратной связи (Ваш оригинальный код + AJAX)
     */
    const contactForm = document.getElementById('contact-form');
    const feedbackStatus = document.getElementById('feedback-status');

    if (contactForm) {
        contactForm.addEventListener('submit', event => {
            // Ваша оригинальная валидация
            if (!contactForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                // Если валидация пройдена - отменяем перезагрузку и шлем AJAX
                event.preventDefault();
                const formData = new FormData(contactForm);
                formData.append('action', 'send_feedback');

                fetch('auth_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (feedbackStatus) {
                        feedbackStatus.innerHTML = '<div class="alert alert-success rounded-4 border-0 shadow-sm">Спасибо! Ваше сообщение отправлено.</div>';
                    }
                    contactForm.reset();
                    contactForm.classList.remove('was-validated');
                    contactForm.style.display = 'none'; // Скрываем форму после успеха
                });
            }
            contactForm.classList.add('was-validated');
        }, false);
    }

    /**
     * 12. Логика модального окна входа/регистрации (БЕЗОПАСНАЯ)
     */
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    if (loginForm) {
        loginForm.addEventListener('submit', () => {
            // Данные уйдут в PHP через action формы
            console.log('Вход...');
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            const password = document.getElementById('register-password');
            const confirm = document.getElementById('register-confirm-password');
            if (password && confirm && password.value !== confirm.value) {
                e.preventDefault();
                alert('Пароли не совпадают!');
            }
        });
    }

    /**
     * 13. Интерактивная карта
     */
    const mapCityNameEl = document.getElementById('map-city-name');
    const mapTravelTimeEl = document.getElementById('map-travel-time');

    if (mapCityNameEl && mapTravelTimeEl) {
        const mapData = [
            { city: 'Воронеж', time: '3 часа' },
            { city: 'Тамбов', time: '2 часа' },
            { city: 'Пенза', time: '4.5 часа' },
            { city: 'Саратов', time: '3.5 часа' },
            { city: 'Липецк', time: '3 часа 45 минут' },
        ];
        let currentMapIndex = 0;
        setInterval(() => {
            mapCityNameEl.style.opacity = '0';
            mapTravelTimeEl.style.opacity = '0';
            setTimeout(() => {
                mapCityNameEl.textContent = mapData[currentMapIndex].city;
                mapTravelTimeEl.textContent = mapData[currentMapIndex].time;
                mapCityNameEl.style.opacity = '1';
                mapTravelTimeEl.style.opacity = '1';
                currentMapIndex = (currentMapIndex + 1) % mapData.length;
            }, 300);
        }, 3000);
    }
    
    /**
     * 14. Анимация чисел
     */
    const numbersSection = document.getElementById('numbers-section');
    if (numbersSection) {
        const animateCountUp = (el) => {
            const target = parseInt(el.dataset.target.replace(/\s/g, ''), 10);
            let count = 0;
            const update = () => {
                const speed = target / 100;
                if (count < target) {
                    count += speed;
                    el.innerText = Math.floor(count).toLocaleString('ru-RU');
                    requestAnimationFrame(update);
                } else {
                    el.innerText = target.toLocaleString('ru-RU');
                }
            };
            update();
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.numbers-value');
                    counters.forEach(animateCountUp);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        observer.observe(numbersSection);
    }
});