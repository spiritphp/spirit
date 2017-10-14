let Debug = {

  /**
   * @param el
   * @returns {Element}
   */
  ge: (el) => {
    return document.getElementById(el);
  },

  status: {
    minisize: false,
    open: false,
    type: false
  },

  open: {
    click: () => {
      if (Debug.status.open) {
        Debug.open.off();
      } else {
        Debug.open.on();
      }
    },

    on: () => {
      Debug.ge('debug__content').classList.remove('debug-b-hide');
      Debug.ge('debug__handle').classList.remove('debug-b-hide');
      Debug.ge('debug__nav').classList.remove('debug-b-hide');
      Debug.ge('debug__switch').classList.add('debug-b-hide');

      Debug.status.open = 1;
      window.localStorage.setItem('debug_open', 'on');

      Debug.minisize.init();
    },
    off: () => {
      Debug.ge('debug__content').classList.add('debug-b-hide');
      Debug.ge('debug__handle').classList.add('debug-b-hide');
      Debug.ge('debug__nav').classList.add('debug-b-hide');
      Debug.ge('debug__switch').classList.remove('debug-b-hide');

      Debug.status.open = 0;
      window.localStorage.setItem('debug_open', 'off');
    },

    init: () => {
      Debug.status.open = window.localStorage.getItem('debug_open');

      if (Debug.status.open === 'on') {
        Debug.open.on();
      } else {
        Debug.open.off();
      }
    }
  },

  minisize: {
    init: () => {
      Debug.status.minisize = window.localStorage.getItem('debug_minisize');

      if (Debug.status.minisize === 'on') {
        Debug.minisize.on();
      } else {
        Debug.status.minisize = 'off';
      }
    },

    click: () => {
      if (Debug.status.minisize === 'on') {
        Debug.minisize.off();
      } else {
        Debug.minisize.on();
      }
    },

    on: () => {
      Debug.ge('debug__content').classList.add('debug-b-hide');

      let items = Debug.ge('debug__content').querySelectorAll('a');

      [].forEach.call(items, (item) => {
        item.classList.remove('-current');
      });

      Debug.status.minisize = 'on';
      window.localStorage.setItem('debug_minisize', 'on');
    },

    off: (fromContentOpen) => {
      Debug.ge('debug__content').classList.remove('debug-b-hide');

      if (!fromContentOpen) {
        if (Debug.status.type) {
          let el;
          if (el = Debug.ge('debug__nav__menu__' + Debug.status.type)) {
            Debug.content.open(el);
          }
        }
      }

      Debug.status.minisize = 'off';
      window.localStorage.setItem('debug_minisize', 'off');
    }
  },

  init: () => {
    Debug.content.hideAll();
    Debug.open.init();
    Debug.minisize.init();
    Debug.content.init();
  },

  content: {

    handle: (e) => {
      let x = e.pageX;
      let y = e.pageY;

      let content = Debug.ge('debug__content');
      let h = content.offsetHeight * 1;

      document.querySelector('body').classList.add('debug-b-unselectable');


      document.onmousemove = (e) => {
        let x2 = e.pageX;
        let y2 = e.pageY;

        let newH = h + y - y2;

        window.localStorage.setItem('debug_content_height', newH);

        content.style.height = newH + 'px';
      };

      document.onmouseup = () => {

        document.onmousemove = null;
        document.querySelector('body').classList.remove('debug-b-unselectable');
      };

    },

    hideAll: () => {

      let items = Debug.ge('debug').querySelectorAll('.debug__content__connect');

      [].forEach.call(items, (item) => {
        item.classList.add('debug-b-hide');
      });
    },

    init: () => {
      let content_height = window.localStorage.getItem('debug_content_height');
      if (content_height && content_height > 0) {
        Debug.ge('debug__content').style.height = content_height * 1 + 'px';
      }

      let items = Debug.ge('debug__nav__menu').querySelectorAll('a');

      [].forEach.call(items, (item) => {
        item.addEventListener('click', (event) => {
          event.preventDefault();
          Debug.content.open(item);
          return false;
        }, false);
      });

      let type = window.localStorage.getItem('debug_content_type');

      if (type && Debug.status.minisize === 'off') {
        let el = Debug.ge('debug__nav__menu__' + type);
        if (el) {
          Debug.content.open(el);
        }
      }

      Debug.status.type = type;
    },

    open: (o) => {

      let type = o.dataset.connect;
      let connect_id = 'debug__content__connect__' + type;

      if (type === Debug.status.type && Debug.status.minisize === 'off') {
        Debug.minisize.on();
        return;
      }

      let items = Debug.ge('debug__nav__menu').querySelectorAll('a');

      [].forEach.call(items, (item) => {
        item.classList.remove('-current');
      });

      o.classList.add('-current');

      Debug.content.hideAll();

      Debug.ge(connect_id).classList.remove('debug-b-hide');


      window.localStorage.setItem('debug_content_type', type);
      Debug.status.type = type;

      if (Debug.status.minisize === 'on') {
        Debug.minisize.off(true);
      }
    }
  }
};