/**
 * Компонент формы входа
 * @requires AJAXForm
 * @requires AJAXFormStandalone
 * @requires ULogin
 */
export default {
    props: {
        /**
         * ID# блока формы входа
         * @type {Number}
         */
        blockId: {
            type: Number,
            required: true,
        },
        /**
         * Использовать адрес электронной почты в качестве имени пользователя
         * @type {Object}
         */
        emailAsLogin: {
            type: Boolean,
            default: false,
        },
        /**
         * Варианты сохранения пароля
         * @type {Number} 0 - не сохранять, 
         *       1 - запомнить по галочке, 
         *       -1 - не запоминать по галочке (чужой компьютер)
         */
        passwordSaveType: {
            type: Number,
            default: 0,
        },
        /**
         * Разрешить вход через социальные сети
         * @type {Object}
         */
        allowSocialLogin: {
            type: Boolean,
            default: false,
        }
    },
    data: function () {
        let translations = {
            DO_LOGIN: 'Войти',
            EMAIL: 'E-mail',
            FOREIGN_COMPUTER: 'Чужой компьютер',
            LOG_IN_WITH_SOCIAL_NETWORKS: 'Войти через социальные сети',
            LOGIN: 'Логин',
            LOST_PASSWORD: 'Забыли пароль?',
            PASSWORD: 'Пароль',
            REGISTER_AN_ACCOUNT: 'Зарегистрировать учетную запись',
            SAVE_PASSWORD: 'Сохранить пароль',
        };
        if (typeof window.translations == 'object') {
            Object.assign(translations, window.translations);
        }
        let result = {
            translations,
            fields: {
                login: {
                    datatype: (this.emailAsLogin ? 'email' : 'text'),
                    name: translations[
                        this.emailAsLogin ? 
                        'EMAIL' : 
                        'LOGIN'
                    ],
                    urn: 'login',
                    htmlId: 'login_' + this.blockId,
                    required: true,
                },
                password: {
                    datatype: 'password',
                    name: translations.PASSWORD,
                    urn: 'password',
                    htmlId: 'password_' + this.blockId,
                    required: true,
                },
                save_password: {
                    datatype: 'checkbox',
                    name: translations.SAVE_PASSWORD,
                    urn: 'save_password',
                    htmlId: 'save_password_' + this.blockId,
                    defval: 1,
                },
                foreign_computer: {
                    datatype: 'checkbox',
                    name: translations.FOREIGN_COMPUTER,
                    urn: 'foreign_computer',
                    htmlId: 'foreign_computer_' + this.blockId,
                    defval: 1,
                },
            },
        };
        return result;
    },
    mounted: function () {
        if (this.allowSocialLogin) {
            this.appendSocial();
        }
    },
    methods: {
        processSocialData: function (data) {
            this.handle(data);
        },
        handle: function (data) {
            throw new Error('handle method must be overriden');
        },
    },
    computed: {
        /**
         * Возвращает набор полей в зависимости от настроек
         * @return {Object[]}
         */
        fieldSet: function () {
            let result = [this.fields.login, this.fields.password];
            if (this.passwordSaveType != 0) {
                if (this.passwordSaveType > 0) {
                    result.push(this.fields.save_password);
                } else {
                    result.push(this.fields.foreign_computer);
                }
            }
            return result;
        },
    },
};