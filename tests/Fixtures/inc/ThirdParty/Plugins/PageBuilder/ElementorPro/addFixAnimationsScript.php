<?php
$fix_script = "class RocketElementorAnimation {

    constructor() {
        // Injects a span, that will later provide the device type from some CSS media query rules
        this.deviceMode = document.createElement('span');
        this.deviceMode.id = 'elementor-device-mode';
        document.body.appendChild(this.deviceMode);
    }


    /**
     * This function finds Elementor animations above the fold and starts them.
     */
    _detectAnimations() {
        // Lists which keys we will need to check in the animation settings
        let deviceValue = getComputedStyle(this.deviceMode, ':after').content.replace(/\"/g, '');
        this.animationSettingKeys = this._listAnimationSettingsKeys(deviceValue);

        // Loops over all elementor elements
        document.querySelectorAll('.elementor-invisible[data-settings]').forEach(element => {
            
            // Filter elements inside viewport only
            const rect = element.getBoundingClientRect();
            if (rect.bottom >= 0 && rect.top <= window.innerHeight) {
                try {
                    this._animateElement(element);
                } catch(err) {}
            }
        });
    }


    /**
     * Animates one element.
     * (Deeply inspired by Elementor's frontend.js code)
     */
    _animateElement(element) {
        // Let's read the animation informations
        const elementSettings = JSON.parse(element.dataset.settings);
        const delay = elementSettings._animation_delay || elementSettings.animation_delay || 0;
        const animation = elementSettings[this.animationSettingKeys.find(key => elementSettings[key])];
        
        if (animation === 'none') {
            element.classList.remove('elementor-invisible');
            return;
        }

        element.classList.remove(animation);
        
        if (this.currentAnimation) {
            element.classList.remove(this.currentAnimation);
        }
        this.currentAnimation = animation;

        let timer = setTimeout(() => {
            // Starts the actual animation
            element.classList.remove('elementor-invisible');
            element.classList.add('animated', animation);

            // Cleans the settings to avoid duplicated animation
            this._removeAnimationSettings(element, elementSettings);
        }, delay);

        // Cancels the pending animation if the real Elementor's scripts are actually loading.
        window.addEventListener('rocket-startLoading', function() {
            clearTimeout(timer);
        });
    }

    /**
     * Insipired by Elementor's code
     */
    _listAnimationSettingsKeys(deviceName = 'mobile') {
        const animationSettingSuffixes = [''];
        
        // According to Elementor's code, mobile device can also use tablet or desktop settings
        // and tablet device can also use desktop settings.
        // (This is why this switch has no \"break\" statements.)
        switch(deviceName) {
            case 'mobile':
                animationSettingSuffixes.unshift('_mobile');
            case 'tablet':
                animationSettingSuffixes.unshift('_tablet');
            case 'desktop':
                animationSettingSuffixes.unshift('_desktop');
        }
        
        const animationSettingPrefixes = ['animation', '_animation'];

        // Now let's combine all this together
        const results = [];
        animationSettingPrefixes.forEach(prefix => {
            animationSettingSuffixes.forEach(suffix => {
                results.push(prefix + suffix);
            })
        });

        return results;
    }

    /**
     * Delete keys related to the animation, so that Elementor doesn't replay the animation
     * a second time when it loads.
     */
    _removeAnimationSettings(element, settings) {
        this._listAnimationSettingsKeys().forEach(key => delete settings[key]);
        element.dataset.settings = JSON.stringify(settings);
    }


    static run() {
        const instance = new RocketElementorAnimation();
        requestAnimationFrame(instance._detectAnimations.bind(instance));
    }
}

document.addEventListener('DOMContentLoaded', RocketElementorAnimation.run);";

return [
	'vfs_dir' => 'public/',

	'structure' => [
		'wp-content' => [
			'plugins' => [
				'wp-rocket' => [
					'assets'=>[
						'js'=>[
							'elementor-animation.js'=>$fix_script
						]
					]
				]
			],
		],
	],
	'test_data' => [
		'testElementorProAddFixAnimationScript' => [
			'config'                  => '<html><head><title>Sample Page</title>' .
							                '</head><body></body></html>',
			'expected'              => '<html><head><title>Sample Page</title>' .
											'</head><body><script>'.$fix_script.'</script></body></html>',
		]
	]
];
