var Debug = {

    /**
     * @param el
     * @returns {Element}
     */
    ge: function(el)
    {
        return document.getElementById(el);
    },

    status: {
        minisize: false,
        open: false,
        type: false
    },

    open: {
        click: function () {
            if (Debug.status.open) {
                this.off();
            } else {
                this.on();
            }
        },

        on: function () {
            Debug.ge('debug__content').classList.remove('debug-b-hide');
            Debug.ge('debug__handle').classList.remove('debug-b-hide');
            Debug.ge('debug__nav').classList.remove('debug-b-hide');
            Debug.ge('debug__switch').classList.add('debug-b-hide');

            Debug.status.open = 1;
            window.localStorage.setItem('debug_open', 'on');

            Debug.minisize.init();
        },
        off: function () {
            Debug.ge('debug__content').classList.add('debug-b-hide');
            Debug.ge('debug__handle').classList.add('debug-b-hide');
            Debug.ge('debug__nav').classList.add('debug-b-hide');
            Debug.ge('debug__switch').classList.remove('debug-b-hide');

            Debug.status.open = 0;
            window.localStorage.setItem('debug_open', 'off');
        },

        init: function () {
            Debug.status.open = window.localStorage.getItem('debug_open');

            if (Debug.status.open === 'on') {
                this.on();
            } else {
                this.off();
            }
        }
    },

    minisize: {
        init: function () {
            Debug.status.minisize = window.localStorage.getItem('debug_minisize');

            if (Debug.status.minisize === 'on') {
                this.on();
            } else {
                Debug.status.minisize = 'off';
            }
        },

        click: function () {
            if (Debug.status.minisize === 'on') {
                this.off();
            } else {
                this.on();
            }
        },

        on: function () {
            Debug.ge('debug__content').classList.add('debug-b-hide');

            var items = Debug.ge('debug__content').querySelectorAll('a');

            [].forEach.call(items, function(item) {
                item.classList.remove('-current');
            });

            Debug.status.minisize = 'on';
            window.localStorage.setItem('debug_minisize', 'on');
        },

        off: function (fromContentOpen) {
            Debug.ge('debug__content').classList.remove('debug-b-hide');

            if (!fromContentOpen) {
                if (Debug.status.type) {
                    var el;
                    if (el = Debug.ge('debug__nav__menu__' + Debug.status.type)) {
                        Debug.content.open(el);
                    }
                }
            }

            Debug.status.minisize = 'off';
            window.localStorage.setItem('debug_minisize', 'off');
        }
    },

    init: function () {
        this.content.hideAll();
        this.open.init();
        this.minisize.init();
        this.content.init();
    },

    content: {

        handle: function (e) {
            var x = e.pageX;
            var y = e.pageY;

            var content = Debug.ge('debug__content');
            var h = content.offsetHeight * 1;

            document.querySelector('body').classList.add('debug-b-unselectable');


            document.onmousemove = function (e) {
                var x2 = e.pageX;
                var y2 = e.pageY;

                var newH = h + y - y2;

                window.localStorage.setItem('debug_content_height', newH);

                content.style.height = newH + 'px';
            };

            document.onmouseup = function () {

                document.onmousemove = null;
                document.querySelector('body').classList.remove('debug-b-unselectable');
            };

        },

        hideAll: function () {

            var items = Debug.ge('debug').querySelectorAll('.debug__content__connect');

            [].forEach.call(items, function(item) {
                item.classList.add('debug-b-hide');
            });
        },

        init: function () {
            var content_height = window.localStorage.getItem('debug_content_height');
            if (content_height && content_height > 0) {
                Debug.ge('debug__content').style.height = content_height * 1 + 'px';
            }

            var items = Debug.ge('debug__nav__menu').querySelectorAll('a');

            [].forEach.call(items, function(item) {
                item.addEventListener('click',function(event){
                    event.preventDefault();
                    Debug.content.open(this);
                    return false;
                },false);
            });

            var type = window.localStorage.getItem('debug_content_type');

            if (type && Debug.status.minisize == 'off') {
                var el = Debug.ge('debug__nav__menu__' + type);
                if (el) {
                    Debug.content.open(el);
                }
            }

            Debug.status.type = type;
        },

        open: function (o) {

            var type = o.dataset.connect;
            var connect_id = 'debug__content__connect__' + type;

            if (type == Debug.status.type && Debug.status.minisize == 'off') {
                Debug.minisize.on();
                return;
            }

            var items = Debug.ge('debug__nav__menu').querySelectorAll('a');

            [].forEach.call(items, function(item) {
                item.classList.remove('-current');
            });

            o.classList.add('-current');

            this.hideAll();

            Debug.ge(connect_id).classList.remove('debug-b-hide');


            window.localStorage.setItem('debug_content_type', type);
            Debug.status.type = type;

            if (Debug.status.minisize == 'on') {
                Debug.minisize.off(true);
            }
        }
    }
};