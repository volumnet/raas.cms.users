/**
 * Компонент уведомления об активации аккаунта
 */
export default {
    props: {
        /**
         * Маркер успешной активации
         * @type {Boolean}
         */
        success: {
            type: Boolean,
            default: false,
        },
        /**
         * Список ошибок
         * @type {String[]}
         */
        errors: {
            type: Array,
            default() {
                return [];
            },
        },
    },
    data() {
        let translations = {
            YOUR_ACCOUNT_HAS_BEEN_SUCCESSFULLY_ACTIVATED: 'Ваша учетная запись была успешно активирована',
        };
        if (typeof window.translations == 'object') {
            Object.assign(translations, window.translations);
        }
        let result = {
            translations,
        };
        return result;
    },
};