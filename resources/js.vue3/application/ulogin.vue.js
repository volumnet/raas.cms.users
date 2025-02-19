/**
 * Mixin компонента для работы с uLogin
 * Запрос идет к той же странице
 */
export default {
    methods: {
        /**
         * Добавляет скрипт соц. сетей
         */
        appendSocial() {
            window.setTimeout(() => {
                $('body').append('<script src="//ulogin.ru/js/ulogin.js"><' + '/script>');
                window.uLoginToken = (token) => {
                    $.post(
                        window.location.href, 
                        { token, 'AJAX': this.blockId }, 
                        this.processSocialData.bind(this),
                        'json'
                    ); 
                }
            }, 100);
        },
        /**
         * Обрабатывает данные, полученные после ввода соц. сетей
         * @param  {Object} data Входные данные
         */
        processSocialData(data) {
            throw new Error('processSocialData needs to be overriden');
        },
        /**
         * Возвращает объект классов для соц. сети
         * @param  {String} url Адрес страницы в соц. сети
         * @param  {String} baseClass Базовый класс элемента
         * @return {Object}
         */
        getSocialClass(url, baseClass)
        {
            let rxes = {
                vk: /(vk\.com)|(vkontakte\.ru)/gi,
                facebook: /(fb\.com)|(facebook\.com)/gi,
                odnoklassniki: /(ok\.ru)|(odnoklassniki\.ru)/gi,
                mailru: /my\.mail\.ru/gi,
                twitter: /twitter\.(com|ru)/gi,
                livejournal: /livejournal\.(com|ru)/gi,
                'google-plus': /google\.(com|ru)/gi,
                yandex: /yandex\.(com|ru)/gi,
                webmoney: /webmoney\.(com|ru)/gi,
                youtube: /youtube\.(com|ru)/gi,
                instagram: /instagram\.(com|ru)/gi,
                whatsapp: /(whatsapp|wa)\.(com|ru|me)/gi,
            };
            let result = {};
            for (let key in rxes) {
                let rx = rxes[key];
                if (rx.test(url)) {
                    result[baseClass + '_' + key] = true;
                }
            }
            return result;
        },
    },
    computed: {
        /**
         * Данные для блока uLogin
         * @return {String}
         */
        uLoginBlockData() {
            let result = {
                display: 'panel',
                optional: [
                    'first_name',
                    'last_name',
                    'phone',
                    'email',
                    'sex',
                    'nickname',
                    'bdate',
                    'city',
                    'country'
                ],
                providers: [
                    'vkontakte',
                    // 'facebook',
                    // 'twitter',
                    // 'google',
                    'yandex',
                    'odnoklassniki',
                    'mailru'
                ],
                // hidden: [
                //     'livejournal',
                //     'youtube',
                //     'webmoney',
                // ],
                callback: 'uLoginToken',
            };
            return result;
        },
        /**
         * Данные для блока uLogin (строка)
         * @return {String}
         */
        uLoginBlockString() {
            let data = this.uLoginBlockData;
            let result = '';
            for (let key in data) {
                result += key + '=' 
                    + (
                        (data[key] instanceof Array) ? 
                        data[key].join(',') : 
                        data[key]
                    ) + ';';
            }
            return result;
        },
    },
}