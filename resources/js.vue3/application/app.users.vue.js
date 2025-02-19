/**
 * Mixin приложения с пользователями
 */
export default {
    data() {
        return {
            user: {},
        };
    },
    mounted() {
        this.updateUser();
    },
    methods: {
        async updateUser() {
            const result = await this.api(this.userURL);
            this.user = result;
        },
    },
    computed: {
        userURL() {
            return '/ajax/user/';
        },
    },
}