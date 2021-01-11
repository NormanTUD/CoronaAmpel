<?php
	function red_text ($str) {
		print "<pre>&#10060; ERROR: ".htmlentities($str)."</pre>";
	}
	function ok ($str) {
		print "<pre>&#9989; OK: ".htmlentities($str)."</pre>";
	}

	function h ($str) {
		print "<h2>".htmlentities($str)."</h2>";
	}

	function print_diffs ($name, $a, $b) {
		$message = "$name failed! Expected type (".(gettype($a))."):\n====>\n".
			(print_r($b, true))."\n<====\ngot (".(gettype($b))."):\n====>\n".
			(print_r($a, true))."\n<====\n";
		return $message;
	}

	function is_equal ($name, $a, $b) {
		if(gettype($a) == gettype($b)) {
			if(gettype($a) == 'string') {
				if($a == $b) {
					ok($name);
					return 1;
				} else {
					$message = print_diffs($name, $a, $b);
					red_text($message);
				}
			} else {
				if (serialize($a) == serialize($b)) {
					ok($name);
					return 1;
				} else {
					$message = print_diffs($name, $a, $b);
					red_text($message);
				}
			}
		} else {
			$message = print_diffs($name, $a, $b);
			red_text($message);
		}
		return 0;
	}

	function is_unequal ($name, $a, $b) {
		if(!gettype($a) == gettype($b)) {
			ok($name);
			return 1;
		} else {
			if(gettype($a) == gettype($b)) {
				if(gettype($a) == 'string') {
					if($a == $b) {
						$message = print_diffs($name, $a, $b);
						red_text($message);;
					} else {
						ok($name);
						return 1;
					}
				} else {
					if (serialize($a) == serialize($b)) {
						$message = print_diffs($name, $a, $b);
						red_text($message);;
					} else {
						ok($name);
						return 1;
					}
				}
			} else {
				print_diffs($name, $a, $b);
				red_text($message);
			}
		}
		return 0;
	}

	function regex_matches ($name, $string, $regex) {
		if(gettype($string) == 'integer' || gettype($string) == 'float') {
			$string = (string) $string;
		}
		if(gettype($string) == 'string') {
			if(preg_match($regex, $string)) {
				ok($name);
				return 1;
			} else {
				$message = "$name failed! Expected:\n====>\n".
					red_text($string)."\n<===\nto match\n====>\n".
					red_text($regex)."\n<====\n";
				red_text($message);;
			}
		} else {
			$message = "Expected ====>\n$string\n<====\n to be string, not ".red_text(gettype($string));
			red_text($message);;
		}
		return 0;
	}

	function regex_fails ($name, $string, $regex) {
		if(gettype($string) == 'integer' || gettype($string) == 'float') {
			$string = (string) $string;
		}
		if(gettype($string) == 'string') {
			if(preg_match($regex, $string)) {
				$message = "$name failed! Expected\n:\n====>\n".
					red_text($string)."\n<===\nNOT to match\n====>\n".
					red_text($regex)."\n<====\n";
				red_text($message);;
			} else {
				ok($name);
				return 1;
			}
		} else {
			$message = "Expected ====>\n$string\n<====\n to be string, not ".red_text(gettype($string));
			red_text($message);;
		}
		return 0;
	}

	function is_equal_safe ($name, $a, $b) {
		if($a == $b) {
			ok($name);
			return 1;
		} else {
			red_text("!!! BASIC TEST FAILED!!! SOMETHING HAS GONE HORRIBLY WRONG WITH THE TESTING FRAMEWORK!!!\n");
			return 0;
		}
	}
?>
