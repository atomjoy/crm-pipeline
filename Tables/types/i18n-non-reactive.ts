// resources/js/i18n.ts

// Tego można użyć tylko w <script setup></script>
// import { useI18n } from 'vue-i18n';
// Ale można to przekazać do komponentu jako t i będzie reagować na zmiany locale.value = 'en'
// const { t, locale } = useI18n({ useScope: 'global' });

// Tylko przykład może to gubić reaktywność i nie reaguje na locale.value = 'en'
import { createI18n } from 'vue-i18n';

const i18n = createI18n({
    legacy: false, // Wymagane dla Composition API / useI18n()
    locale: 'pl', // Domyślny język aplikacji
    fallbackLocale: 'en', // Język awaryjny
    messages: {
        pl: {
            users: {
                id: 'ID',
                name: 'Imię i nazwisko',
                email: 'Adres Email',
                status: 'Status',
                actions: {
                    edit: 'Edytuj',
                    delete: 'Usuń {name}',
                },
            },
        },
    },
});

// Eksportujemy czyste 't' lecz moze to gubic reaktywność
// i nie reaguje na locale.value = 'en'
export const t = i18n.global.t;

export default i18n;
