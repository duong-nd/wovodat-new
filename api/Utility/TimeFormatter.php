<?php
	class TimeFormatter {
		public static function getJavascriptTimestamp($src) {
			return strtotime($src) * 1000;
		}
	}