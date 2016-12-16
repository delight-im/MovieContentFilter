<?php

http_response_code(503);
header('Retry-After: 3600');

?>
<!DOCTYPE html>
<!--
 * Maintenance (https://github.com/delight-im/Maintenance)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
-->
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Maintenance</title>
		<style type="text/css">
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}
		html, body, div#container {
			width: 100%;
			height: 100%;
		}
		div#container {
			display: flex;
			align-items: center;
			justify-content: center;
		}
		div#content {
			padding: 8px;
			font-family: "Roboto", -apple-system, "San Francisco", "Segoe UI", "Helvetica Neue", sans-serif;
			text-align: center;
			font-size: 16px;
			color: #333;
		}
		div#content h1 {
			font-size: 32px;
		}
		div#content div.vertical-space {
			margin: 32px 0;
		}
		</style>
	</head>
	<body>
		<div id="container">
			<div id="content">
				<h1>We&rsquo;ll be back soon!</h1>
				<p>
					We&rsquo;re performing some maintenance at the moment and will be back shortly.
					Sorry for the inconvenience!
				</p>
				<div class="vertical-space"></div>
				<h1>Wir sind gleich zurück!</h1>
				<p>
					Wir f&uuml;hren gerade Wartungsarbeiten durch und sind in K&uuml;rze wieder da.
					Bitte entschuldigen Sie die Unannehmlichkeiten!
				</p>
			</div>
		</div>
	</body>
</html>
