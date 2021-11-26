/**
 * Mixin приложения с пользователями
 */
export default {
    data: function () {
        return {
            user: {},
        };
    },
    mounted: function () {
        $.getJSON(this.userURL, (result) => {
            this.user = result;
        });
    },
    computed: {
        userURL: function () {
            return '/ajax/user/';
        },
    },
}