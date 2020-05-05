<?php

return [
	'testShouldRewriteInternalURLsToCDN' => [
		'original' => 'url("navigation.css")
			url(../../../fa-icon.woff)
			url(   "/navigation.css    " )
			url(\'http://google.com/image.jpg\')
			url(\'http://example.org/image.jpg\')
			url(\'    http://example.org/image.jpg\'    )
			url(http://example.org/test.gif)
			url(     http://example.org/image.png )
			url(data:image/gif;base64,R0lGODlhEAAQAMQAAORHHOVSKudfOulrSOp3WOyDZu6QdvCchPGolfO0o/XBs/fNwfjZ0frl3/zy7////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAkAABAALAAAAAAQABAAAAVVICSOZGlCQAosJ6mu7fiyZeKqNKToQGDsM8hBADgUXoGAiqhSvp5QAnQKGIgUhwFUYLCVDFCrKUE1lBavAViFIDlTImbKC5Gm2hB0SlBCBMQiB0UjIQA7)',
		'expected' => 'url("navigation.css")
			url(../../../fa-icon.woff)
			url(   "/navigation.css    " )
			url(\'http://google.com/image.jpg\')
			url(\'http://cdn.example.org/image.jpg\')
			url(\'    http://cdn.example.org/image.jpg\'    )
			url(http://cdn.example.org/test.gif)
			url(     http://cdn.example.org/image.png )
			url(data:image/gif;base64,R0lGODlhEAAQAMQAAORHHOVSKudfOulrSOp3WOyDZu6QdvCchPGolfO0o/XBs/fNwfjZ0frl3/zy7////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAkAABAALAAAAAAQABAAAAVVICSOZGlCQAosJ6mu7fiyZeKqNKToQGDsM8hBADgUXoGAiqhSvp5QAnQKGIgUhwFUYLCVDFCrKUE1lBavAViFIDlTImbKC5Gm2hB0SlBCBMQiB0UjIQA7)',
	],
];
