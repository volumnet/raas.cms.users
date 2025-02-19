/**
 * Компонент формы регистрации / редактирования профиля
 * @requires bootstrap/alert
 * @requires AJAXForm
 * @requires AJAXFormStandalone
 * @requires ULogin
 */
export default {
    props: {
        /**
         * Пользователь системы
         * @type {Object}
         */
        user: {
            type: Object,
            required: true,
        },
        /**
         * Тип активации согласно константам Block_Register::ACTIVATION_TYPE_...
         * 0 - администратором
         * 1 - пользователем
         * 2 - уже активирована
         * @type {Number}
         */
        activationType: {
            type: Number,
            required: true,
        },
        /**
         * Разрешить редактирование соц. сетей
         * @type {Object}
         */
        allowEditSocial: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        let translations = {
            ASTERISK_MARKED_FIELDS_ARE_REQUIRED: 'Поля, помеченные звездочкой (*), обязательны для заполнения',
            DO_REGISTER: 'Зарегистрироваться',
            NOW_YOU_CAN_LOG_IN_INTO_THE_SYSTEM: 'Сейчас Вы можете авторизоваться в системе',
            PASSWORD_CONFIRM: 'Подтверждение пароля',
            PLEASE_ACTIVATE_BY_EMAIL: 'Вам отправлено письмо со ссылкой для активации вашей учетной записи.',
            PLEASE_WAIT_FOR_ADMINISTRATOR_TO_ACTIVATE: 'В ближайшее время администратор сайта активирует вашу учетную запись.',
            PROFILE_SUCCESSFULLY_UPDATED: 'Ваш профиль был успешно обновлен',
            SAVE: 'Сохранить',
            SOCIAL_NETWORKS: 'Социальные сети',
            YOU_HAVE_SUCCESSFULLY_REGISTERED: 'Вы успешно зарегистрировались.',
        };
        if (typeof window.translations == 'object') {
            Object.assign(translations, window.translations);
        }
        let result = {
            translations,
        };
        return result;
    },
    mounted() {
        if (this.allowEditSocial) {
            this.appendSocial();
        }
    },
    methods: {
        processSocialData(data) {
            if (data.social && (this.formData.social.indexOf(data.social) == -1)) {
                this.formData.social.push(data.social);
            }
        },
        /**
         * Поле подтверждение пароля
         * @param {Object} passwordField Поле пароля
         * @return {Object}
         */
        passwordConfirmField(passwordField) {
            let result = Object.assign({}, passwordField, {
                name: this.translations.PASSWORD_CONFIRM,
                urn: passwordField.urn + '@confirm',
                htmlId: passwordField.htmlId.replace(/_\w+$/gi, '@confirm$0'),
            });
            return result;
        },
        
    },
    computed: {
        /**
         * Сообщение об успешной отправке формы
         * @return {[type]} [description]
         */
        successCaption() {
            let result = '';
            if (this.user.id) {
                result = this.translations.PROFILE_SUCCESSFULLY_UPDATED;
            } else {
                result = this.translations.YOU_HAVE_SUCCESSFULLY_REGISTERED + ' ';
                switch (this.activationType) {
                    case 0: // Активация администратором (Block_Register::ACTIVATION_TYPE_ADMINISTRATOR)
                        result += this.translations.PLEASE_WAIT_FOR_ADMINISTRATOR_TO_ACTIVATE;
                        break;
                    case 1: // Активация пользователем (Block_Register::ACTIVATION_TYPE_USER)
                        result += this.translations.PLEASE_ACTIVATE_BY_EMAIL;
                        break;
                    case 2: // Уже активирована (Block_Register::ACTIVATION_TYPE_ALREADY_ACTIVATED)
                        result += this.translations.NOW_YOU_CAN_LOG_IN_INTO_THE_SYSTEM;
                        break;
                }
            }
            return result;
        },
    },
};
