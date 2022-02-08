/**
 * Компонент меню пользователя
 */
export default {
    props: {
        /**
         * Отображать пункт "Мои заказы"
         * @type {Boolean}
         */
        hasOrders: {
            type: Boolean,
            default: false,
        },
        /**
         * Пользователь
         * @type {Object}
         */
        user: {
            type: Object,
            required: true,
        },
    },
    data: function () {
        let translations = {
            EDIT_PROFILE: 'Редактирование профиля',
            MY_ORDERS: 'Мои заказы',
            LOG_OUT: 'Выйти',
            LOG_IN: 'Войти',
        };
        if (typeof window.translations == 'object') {
            Object.assign(translations, window.translations);
        }
        let result = {
            translations,
        };
        return result;
    },
    computed: {
        referer: function () {
            if ((window.location.pathname != '/') && 
                !/login/.test(window.location.pathname) && 
                !/register/.test(window.location.pathname)
            ) {
                return window.location.pathname;
            }
            return '';
        },
    }
};