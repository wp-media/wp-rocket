<?php

$content = <<<HTML
<div class="wpr-tools">
	<div class="wpr-tools-col">
		<div class="wpr-title3 wpr-tools-label wpr-icon-export">Update Inclusion and Exclusion Lists</div>
		<div class="wpr-field-description">Compatibility lists are downloaded automatically every week. Click the button if you want to update them manually.<a href="https://docs.wp-rocket.me/article/1716-dynamic-exclusions-and-inclusions/?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="63234712b0f178684ee3b04a" target="_blank" rel="noopener noreferrer">More info</a></div>
		<div id="wpr-update-exclusion-msg" class="wpr-field-description"></div>
	</div>
	<div class="wpr-tools-col">
		<button id="wpr-update-exclusion-list" class="wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-refresh">
			Update lists
		</button>
	</div>
</div>
HTML;

return [
	'testShouldDisplayNothingWhenNoCap' => [
		'role' => 'editor',
		'expected' => null,
	],
	'testShouldDisplaySectionWhenCap' => [
		'role' => 'administrator',
		'expected' => $content,
	],
];
