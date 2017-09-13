var Undercover = (function() {

  /**
   * @param id
   * @returns {HTMLElement}
   */
  var ge = function(id) {
    return document.getElementById(id);
  };

  /**
   * @param className
   * @param {HTMLElement} el
   * @returns {*}
   */
  var hasClass = function(className, el) {
    return el.classList.contains(className);
  };

  /**
   * @param className
   * @param {HTMLElement} el
   * @returns {*}
   */
  var removeClass = function(className, el) {
    return el.classList.remove(className);
  };

  /**
   * @param className
   * @param {HTMLElement} el
   * @returns {*}
   */
  var addClass = function(className, el) {
    return el.classList.add(className);
  };

  return {
    menuToggle: function() {
      var menulink = ge('header_menulink');
      var menu = ge('menu');
      var content = ge('content');
      var debug = ge('debug');

      if (hasClass('menu-open', menu)) {
        removeClass('menu-open', menu);
        removeClass('header__menulink-active', menulink);
        removeClass('content-openmenu', content);
        removeClass('debug-openmenu', debug);
      } else {
        addClass('menu-open', menu);
        addClass('header__menulink-active', menulink);
        addClass('content-openmenu', content);
        addClass('debug-openmenu', debug);
      }
    }
  }
})();