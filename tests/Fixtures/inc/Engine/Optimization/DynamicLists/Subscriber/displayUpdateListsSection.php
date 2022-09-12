<?php

$content = <<<HTML
<div class="wpr-tools">
	<div class="wpr-tools-col">
		<div class="wpr-title3 wpr-tools-label wpr-icon-export">Update Inclusion and Exclusion Lists</div>
		<div class="wpr-field-description">Update Inclusion and Exclusion Lists</div>
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
