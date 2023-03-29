var config = {
    paths: {
        'slick': 'js/slick.min',
        'grtyoutube' : 'js/YouTubePopUp.jquery',
        'drlnumberjs': 'js/intlTelInput-jquery',
    },
    deps: [
        "js/drl",

    ],
    shim: {
        'slick': {
            deps: ['jquery']
        },
        'drlnumberjs': {
            deps: ['jquery']
          },
    },
    shim: {
        'grtyoutube': {
            deps: ['jquery']
        }
    },
};
