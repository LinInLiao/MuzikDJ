/* eslint-disable */
'use strict';

let ga = window.ga || function () { (ga.q = ga.q || []).push(arguments); };
let gaExt = gaExt || {};

gaExt = {
  conf: {
    selector: '#gaConf',
    scriptPath: '//www.google-analytics.com/analytics.js',
    legalTag: [
      'a',
      'input',
      'button',
      'select'
    ],
    inputType: [
      'checkbox',
      'radio',
      'submit',
      'button',
      'image',
      'file'
    ],
    localStorage: undefined,
    tracker: undefined,
    ready: undefined
  },
  data: {
    cookieDomain: 'none',
    sampleRate: 100,
    alwaysSendReferrer: true,
    allowLinker: true,
    userId: null,
    plugins: ['displayfeatures'],
    signEsc: 'ga-esc',
    markId: 'gaExtMark',
    customDefinitions: {},
    trackedMods: {}
  },
  cdMap: {
  },
  trackedMods: [],
  addModule: function (mod, gaMods, sendBeacon) {
    sendBeacon = (typeof sendBeacon === 'boolean') ? sendBeacon : true;
    if (!this.conf.ready || typeof mod.nodeType === 'undefined' || mod.nodeType !== 1 || this.trackedMods.indexOf(mod) !== -1) {
      return;
    }
    this.trackedMods.push(mod);
    mod.setAttribute('data-ga-mod', gaMods);
    mod.addEventListener('click', this.modClick, false);
    if (sendBeacon) {
      this.doModuleViewBeacon(mod);
    }
  },
  removeModule: function (mod) {
    if (!this.conf.ready || typeof mod.nodeType === 'undefined' || mod.nodeType !== 1 || this.trackedMods.indexOf(mod) === -1) {
      return;
    }
    this.trackedMods.splice(this.trackedMods.indexOf(mod), 1);
    mod.removeAttribute('data-ga-mod');
    mod.removeEventListener('click', this.modClick, false);
  },
  getTarget: function (e) {
    var target;
    e = e._event || e;
    if (window.event && window.event.srcElement) {
      target = window.event.srcElement;
    } else if (e && e.target) {
      target = e.target;
    }
    return target;
  },
  fetchMods: function (e) {
    var target, mod;
    target = e;
    while (target !== null) {
      if (target.tagName && (target.tagName.toLowerCase() === 'body' || target.hasAttribute('data-ga-mod'))) {
        break;
      }
      target = target.parentNode;
    }//end if
    if (target && target.tagName.toLowerCase() !== 'body' && target.hasAttribute('data-ga-mod')) {
      mod = target;
    }
    return mod;
  },
  fetchClickAble: function (e) {
    var target;
    target = this.getTarget(e);
    while (target !== null) {
      if (target.nodeType == 1 && (this.conf.legalTag.indexOf(target.tagName.toLowerCase()) !== -1 || target.tagName.toLowerCase() == 'body')) {
        break;
      }
      target = target.parentNode;
    }//end while
    if (target && target.tagName.toLowerCase() !== 'body') {
      if (target.tagName.toLowerCase() === 'input' && (target.disabled || this.conf.inputType.indexOf(target.type.toLowerCase()) === -1)) {
        target = null;
      }
    } else {
      target = null;
    }//end if
    return target;
  },
  modClick: function (e) {
    var target = gaExt.fetchClickAble(e);
    if (!target || target.classList.contains(gaExt.data.signEsc)) {
      return;
    }
    gaExt.doEventBeacon(target);
  },
  doEventBeacon: function (target, data) {
    var e, mod;
    if (!gaExt.conf.ready || !target) {
      return;
    }

    e = {
      category: gaExt.data.customDefinitions.document_group || 'category',
      action: 'action',
      label: 'none'
    };

    if (typeof target !== 'boolean') {
      mod = gaExt.fetchMods(target);
      if (mod) {
        e = {
          category: gaExt.data.customDefinitions.document_group || 'modClick',
          action: mod.getAttribute('data-ga-mod') || 'action',
          label: target.getAttribute('data-slk') || 'none'
        };
        if (e.label == 'none' && (target.textContent || target.value)) {
          e.label = target.value || target.textContent;
          e.label = e.label.toString().replace(/^\s*|(\r\n|\n|\r)|\s*$/gm, '');
          if (!e.label.length) {
            e.label = 'none';
          }
        }//end if
        e.label = e.label.toString();
      }//end if
    }

    if (data) {
      for (var i in data) {
        e[i] = data[i];
      }
    }

    ga(gaExt.data.prefix + 'send', 'event', {
      eventCategory: e.category,
      eventAction: e.action,
      eventLabel: e.label,
      eventValue: e.value || 0,
      nonInteraction: e.nonInteraction || false,
      page: window.location.hash.substring(1) || window.location.pathname,
      userId: e.userId || null
    });
  },
  doModuleViewBeacon: function (mod) {
    var data;
    if (!gaExt.conf.ready || typeof mod.nodeType === 'undefined' || mod.nodeType !== 1 || gaExt.trackedMods.indexOf(mod) === -1) {
      return;
    }

    data = {
      category: gaExt.data.customDefinitions.document_group || mod.getAttribute('data-ga-mod'),
      action: 'moduleView',
      label: mod.getAttribute('data-ga-mod')
    };
    gaExt.doEventBeacon(mod, data);
  },
  sendModuleView: function () {
    if (!gaExt.conf.ready || !gaExt.trackedMods.length) {
      return;
    }
    for (var i = -1, l = gaExt.trackedMods.length; ++i < l;) {
      gaExt.doModuleViewBeacon(gaExt.trackedMods[i]);
    }
  },
  doPageViewBeacon: function (sendModuleView) {
    if (!gaExt.conf.ready) {
      return;
    }
    if (typeof sendModuleView === 'object') {
      ga(this.data.prefix + 'send', Object.assign({}, sendModuleView, {
        hitType: 'pageview'
      }));
    } else {
      ga(this.data.prefix + 'send', 'pageview');
    }

    if (!this.conf.tracker) {
      ga(this.updateStatus);
    }
    if (typeof sendModuleView === 'boolean' && sendModuleView) {
      ga(this.sendModuleView);
    }
  },
  get: function (key) {
    var tracker, value;
    if (this.conf.ready && this.conf.tracker) {
      tracker = this.conf.tracker;
      key = key.trim();
      value = tracker.get(key) || tracker.get(this.cdMap[key]);
    }//end if
    return value;
  },
  set: function (key, value) {
    var tracker;
    if (this.conf.ready && this.conf.tracker && this.cdMap[key]) {
      tracker = this.conf.tracker;
      key = key.trim();
      value = value || 'none';
      ga(this.data.prefix + 'set', this.cdMap[key], value.trim());
    }//end if
  },
  updateStatus: function (tracker) {
    if (!gaExt.conf.ready || gaExt.conf.tracker) {
      return;
    }
    gaExt.conf.tracker = ga.getByName(gaExt.data.trackerName);
    //setMark
    gaExt.setMark();
  },
  hasCookie: function (sKey) {
    if (!sKey) {
      return false;
    }
    return (new RegExp("(?:^|;\\s*)" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=")).test(document.cookie);
  },
  getCookie: function (sKey) {
    if (!sKey) {
      return null;
    }
    return decodeURIComponent(document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1")) || null;
  },
  setCookie: function (sKey, sValue, vEnd, sPath, sDomain, bSecure) {
    if (!sKey || /^(?:expires|max\-age|path|domain|secure)$/i.test(sKey)) {
      return false;
    }
    var sExpires = '';
    if (vEnd) {
      switch (vEnd.constructor) {
        case Number:
          sExpires = vEnd === Infinity ? '; expires=Fri, 31 Dec 9999 23:59:59 GMT' : '; max-age=' + vEnd;
          break;
        case String:
          sExpires = '; expires=' + vEnd;
          break;
        case Date:
          sExpires = '; expires=' + vEnd.toUTCString();
          break;
      }//end switch
    }//end if
    document.cookie = encodeURIComponent(sKey) + '=' + encodeURIComponent(sValue) + sExpires + (sDomain ? '; domain=' + sDomain : '') + (sPath ? '; path=' + sPath : '') + (bSecure ? '; secure' : '');
    return true;
  },
  isEmptyObject: function (obj) {
    for (var prop in obj) {
      if (obj.hasOwnProperty(prop)) {
        return false;
      }
    }//end for
    return true && JSON.stringify(obj) === JSON.stringify({});
  },
  parseData: function (_data) {
    var e, clear, data;

    //data source
    if (typeof _data !== 'object') {
      e = document.querySelector(gaExt.conf.selector);
      if (!e) {
        return false;
      }
    }

    if (typeof this.conf.ready === 'undefined') {
      clear = true;

      //conf
      if (typeof _data !== 'object') {
        if (!e.hasAttribute('data-conf')) {
          clear = false;
        } else {
          try {
            data = JSON.parse(e.getAttribute('data-conf'));
          } catch (err) { clear = false; }
        }//end if

        if (e && Object.keys(data).length > 0) {
          e.parentNode.removeChild(e);
        }
      } else {
        data = JSON.parse(JSON.stringify(_data));
      }

      if (clear && data.trackingID) {
        //data plug
        for (var i in data) {
          this.data[i] = data[i];
        }

        //cdMap
        if (typeof this.data.cdMap !== 'undefined') {
          this.cdMap = this.data.cdMap;
          delete (this.data.cdMap);
        }//end if

        //plugins
        if (typeof this.data.plugins === 'undefined' || !Array.isArray(this.data.plugins)) {
          this.data.plugins = ['displayfeatures'];
        }
        if (this.data.ecommerce && this.data.plugins.indexOf('ecommerce') === -1) {
          this.data.plugins.push('ecommerce');
        }
      } else {
        clear = false;
      }

      this.conf.ready = clear;
    }//end if

    return this.conf.ready;
  },
  setMark: function () {
    var data;
    if (!this.conf.ready || !this.conf.localStorage) {
      return;
    }

    data = {
      'name': this.get('cookieName'),
      'domain': this.get('cookieDomain'),
      'expires': this.get('cookieExpires')
    };
    data.value = this.getCookie(data.name);
    if (data.domain === 'none') {
      data.domain = location.hostname;
    }
    this.conf.localStorage.setItem(this.data.markId, JSON.stringify(data));
  },
  recoverMark: function () {
    var source, data;
    if (!this.conf.ready || !this.conf.localStorage) {
      return;
    }

    source = this.conf.localStorage.getItem(this.data.markId);
    if (source === null) {
      return;
    }

    data = {};
    try { data = JSON.parse(source); } catch (err) { data = {}; }

    if (this.isEmptyObject(data) || !data.name || this.getCookie(data.name)) {
      return;
    }
    this.setCookie(data.name, data.value, Number(data.expires), '/', data.domain);
  },
  register: function () {
    var m;
    if (!this.conf.ready || typeof this.data.trackerName !== 'undefined') {
      return;
    }

    this.data.trackerName = 'gaExt_' + new Date().getTime();
    this.data.prefix = this.data.trackerName + '.';

    //ga
    var i, l;
    ga = window.ga || function () { (ga.q = ga.q || []).push(arguments); };

    //recover mark
    this.recoverMark();

    //basic setting
    ga('create', this.data.trackingID, {
      name: this.data.trackerName,
      sampleRate: this.data.sampleRate,
      cookieDomain: this.data.cookieDomain,
      alwaysSendReferrer: this.data.alwaysSendReferrer,
      allowLinker: this.data.allowLinker,
      userId: this.data.userId
    });
    for (i = -1, l = this.data.plugins.length; ++i < l;) {
      ga(this.data.prefix + 'require', this.data.plugins[i]);
    }

    ga(this.data.prefix + 'set', 'transport', 'beacon');
    for (i in this.data.customDefinitions) {
      if (!this.cdMap[i]) {
        continue;
      }
      ga(this.data.prefix + 'set', this.cdMap[i], this.data.customDefinitions[i].toString());
    }//end for

    //trackedMods
    m = this;
    var addTrackedMods = function (node) {
      m.addModule(node, this.gaMods, false);
    };
    for (i in this.data.trackedMods) {
      var gaMods = this.data.trackedMods[i];
      [].slice.call(document.querySelectorAll(i)).forEach(addTrackedMods, { gaMods: gaMods });
    }//end for

    if (this.data.ecommerce && this.data.ecommerce.transaction && this.data.ecommerce.item) {
      //Transaction
      ga(this.data.prefix + 'ecommerce:addTransaction', this.data.ecommerce.transaction);
      //Item
      m = this.data.ecommerce.item;
      for (i = -1, l = m.length; ++i < l;) {
        ga(this.data.prefix + 'ecommerce:addItem', m[i]);
      }
      ga(this.data.prefix + 'ecommerce:send');
      ga(this.data.prefix + 'ecommerce:clear');
    }//end if
  },
  init: function (data) {
    var s;

    //get conf
    if (!this.parseData(data)) {
      return;
    }

    //localStorage
    if (typeof localStorage !== 'undefined') {
      s = true;
      try {
        localStorage.setItem('isapisupport', 'dummy');
        localStorage.removeItem('isapisupport');
      } catch (err) { s = false; }
      if (s) {
        this.conf.localStorage = localStorage;
      }
    }//end if

    //fetch ga script
    s = document.createElement('script');
    document.head.appendChild(s);
    s.onload = s.onreadystatechange = function () {
      this.parentNode.removeChild(this);

      //GA register
      gaExt.register();
      gaExt.doPageViewBeacon(true);
    };
    s.async = true;
    s.src = this.conf.scriptPath;
  }
};

/*init when DOMContentLoaded*/
(function () {
  var s, c = 0, max = 10000;//10 seconds
  if (typeof navigator.gaIID === 'undefined') {
    navigator.gaIID = '';
  }

  navigator.gaIID = setInterval(
    function () {
      c += 5;
      if (c >= max) {
        clearInterval(navigator.gaIID);
        return;
      }//end if
      if (document.body && document.querySelector(gaExt.conf.selector)) {
        clearInterval(navigator.gaIID);
        navigator.gaIID = null;
        gaExt.init();
      }//end if
    }, 5);

  if (document.currentScript) {
    s = document.currentScript;
    try { s.parentNode.removeChild(s); } catch (err) { }
  }//end if
})();

/*
Reference:
Analytics for Web: https://developers.google.com/analytics/devguides/collection/analyticsjs/
Google Analytics Debugger: https://chrome.google.com/webstore/detail/google-analytics-debugger/jnkmfdileelhofjcijamephohjechhna

programed by mei(李維翰), http://www.facebook.com/mei.studio.li
*/

export default gaExt;