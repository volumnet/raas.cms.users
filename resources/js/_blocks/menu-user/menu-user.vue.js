/**
 * Меню пользователя
 */
/**
 * Пользовательское меню
 */
export default {
    props: {
        /**
         * Пользователь
         */
        user: {
            type: Object,
            required: true,
        },
    },
    computed: {
        self: function () {
            return { ...this };
        },
    },
}