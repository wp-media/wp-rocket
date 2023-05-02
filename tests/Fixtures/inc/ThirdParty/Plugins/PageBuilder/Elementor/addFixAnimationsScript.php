<?php
$fix_script = "class RocketElementorAnimation{constructor(){document.getElementById(\"elementor-device-mode\")||(this.deviceMode=document.createElement(\"span\"),this.deviceMode.id=\"elementor-device-mode-wpr\",this.deviceMode.setAttribute(\"class\",\"elementor-screen-only\"),document.body.appendChild(this.deviceMode))}_detectAnimations(){let e=getComputedStyle(this.deviceMode,\":after\").content.replace(/\"/g,\"\");this.animationSettingKeys=this._listAnimationSettingsKeys(e),document.querySelectorAll(\".elementor-invisible[data-settings]\").forEach(e=>{let t=e.getBoundingClientRect();if(t.bottom>=0&&t.top<=window.innerHeight)try{this._animateElement(e)}catch(i){}})}_animateElement(e){let t=JSON.parse(e.dataset.settings),i=t._animation_delay||t.animation_delay||0,n=t[this.animationSettingKeys.find(e=>t[e])];if(\"none\"===n)return void e.classList.remove(\"elementor-invisible\");e.classList.remove(n),this.currentAnimation&&e.classList.remove(this.currentAnimation),this.currentAnimation=n;let s=setTimeout(()=>{e.classList.remove(\"elementor-invisible\"),e.classList.add(\"animated\",n),this._removeAnimationSettings(e,t)},i);window.addEventListener(\"rocket-startLoading\",function(){clearTimeout(s)})}_listAnimationSettingsKeys(e=\"mobile\"){let t=[\"\"];switch(e){case\"mobile\":t.unshift(\"_mobile\");case\"tablet\":t.unshift(\"_tablet\");case\"desktop\":t.unshift(\"_desktop\")}let i=[];return[\"animation\",\"_animation\"].forEach(e=>{t.forEach(t=>{i.push(e+t)})}),i}_removeAnimationSettings(e,t){this._listAnimationSettingsKeys().forEach(e=>delete t[e]),e.dataset.settings=JSON.stringify(t)}static run(){let e=new RocketElementorAnimation;requestAnimationFrame(e._detectAnimations.bind(e))}}document.addEventListener(\"DOMContentLoaded\",RocketElementorAnimation.run);";

return [
	'vfs_dir' => 'wp-content/',
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
		'testElementorShouldAddFixAnimationScript' => [
			'config'   => [
				'delay_js'      => 1,
			],
			'html'                  => '<html><head><title>Sample Page</title>' .
							                '</head><body></body></html>',
			'expected'              => '<html><head><title>Sample Page</title>' .
											'</head><body><script>'.$fix_script.'</script></body></html>',
		],
		'testElementorShouldNotAddFixAnimationScript' => [
			'config'   => [
				'delay_js'      => 0,
			],
			'html'                  => '<html><head><title>Sample Page</title>' .
			                           '</head><body></body></html>',
			'expected'              => '<html><head><title>Sample Page</title>' .
			                           '</head><body></body></html>',
		]
	]
];
