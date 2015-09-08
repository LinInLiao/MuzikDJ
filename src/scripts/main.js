'use strict';

require('./app/mApp');

var loader = require('./utilities/loader');
var progress = new MaterialProgress(document.querySelector('#loader'));

function progressCb(e) {
    var p = Math.abs(100 * e.progress);

    progress.setProgress(p);
}

function completeCb() {
    // hide loader
    document.querySelector('#loader').style.display = 'none';
    // run app
    angular.bootstrap(document, ['mApp']);
}

/* bootstrap application */
angular.element(document).ready(function () {
    if (window.app.incompatible.isIncompatibleBrowser()) {
        return;
    }

    // show loader
    progress.setProgress(0);
    document.querySelector('#loader').style.display = 'block';

    // start loader
    loader.setProgressCb(progressCb);
    loader.setCompleteCb(completeCb);
    loader.createLoader();
});
