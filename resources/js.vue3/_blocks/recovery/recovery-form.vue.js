/**
 * Компонент формы восстановления пароля
 * @requires AJAXForm
 * @requires AJAXFormStandalone
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
         * Второй этап восстановления (смена пароля)
         * @type {Boolean}
         */
        proceed: {
            type: Boolean,
            default: false,
        },
        /**
         * Неверный ключ восстановления 
         * @type {Boolean}
         */
        keyIsInvalid: {
            type: Boolean,
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
    },
    data() {
        let translations = {
            ENTER_LOGIN_OR_EMAIL: 'Введите логин или e-mail',
            ENTER_EMAIL: 'Введите e-mail',
            CHANGE: 'Изменить',
            YOUR_PASSWORD_WAS_SUCCESSFULLY_CHANGED: 'Ваш пароль был успешно изменен',
            RECOVERY_KEY_WAS_SENT: 'На ваш адрес электронной почты было отправлено письмо со ссылкой для смены пароля',
            NEW_PASSWORD: 'Новый пароль',
            PASSWORD_CONFIRM: 'Подтверждение пароля',
            SEND: 'Отправить',
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
                        'ENTER_EMAIL' : 
                        'ENTER_LOGIN_OR_EMAIL'
                    ],
                    urn: 'login',
                    htmlId: 'login_' + this.blockId,
                    required: true,
                },
                password: {
                    datatype: 'password',
                    name: translations.NEW_PASSWORD,
                    urn: 'password',
                    htmlId: 'password_' + this.blockId,
                    required: true,
                },
                passwordConfirm: {
                    datatype: 'password',
                    name: translations.PASSWORD_CONFIRM,
                    urn: 'password@confirm',
                    htmlId: 'password@confirm_' + this.blockId,
                    required: true,
                },
            },
        };
        return result;
    },
    computed: {
        /**
         * Сообщение об успешной отправке формы
         * @return {String}
         */
        successCaption() {
            let result = this.translations[
                this.proceed ? 
                'YOUR_PASSWORD_WAS_SUCCESSFULLY_CHANGED' : 
                'RECOVERY_KEY_WAS_SENT'
            ];
            return result;
        },
        /**
         * Текст кнопки
         * @return {String}
         */
        buttonCaption() {
            let result = this.translations[this.proceed ? 'CHANGE' : 'SEND'];
            return result;
        },
        /**
         * Возвращает набор полей в зависимости от шага восстановления
         * @return {Object[]}
         */
        fieldSet() {
            if (this.proceed) {
                return [this.fields.password, this.fields.passwordConfirm];
            } else {
                return [this.fields.login];
            }
        },
    },
};