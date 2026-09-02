// resources/js/i18n.ts
import { createI18n } from 'vue-i18n';
import pl from './lang/pl';
import en from './lang/en';

// Importujesz w globalnym withApp() z .use(i18n)
const i18n = createI18n({
    legacy: false, // Wymagane dla Composition API / useI18n()
    locale: 'pl',
    fallbackLocale: 'en',
    availableLocales: ['pl', 'en'],
    messages: {
        pl,
        en,
    },
});

export default i18n;
