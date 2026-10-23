import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import './style.css';

const app = createApp(App);

app.directive('reveal', {
    mounted(element, binding) {
        if (typeof IntersectionObserver === 'undefined') {
            element.classList.add('is-visible');
            return;
        }

        const delay = binding.value?.delay;

        if (typeof delay === 'number') {
            element.style.setProperty('--reveal-delay', `${delay}ms`);
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -48px 0px' }
        );

        element.__revealObserver = observer;
        observer.observe(element);
    },
    unmounted(element) {
        element.__revealObserver?.disconnect();
    },
});

app.use(router);
app.mount('#app');
