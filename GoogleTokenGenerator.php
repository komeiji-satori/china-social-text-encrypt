<?php
class GoogleTokenGenerator {
	/**
	 * Generate and return a token.
	 *
	 * @param string $source Source language
	 * @param string $target Target language
	 * @param string $text   Text to translate
	 *
	 * @return mixed A token
	 */
	public static function generateToken($source, $target, $text) {
		return self::TL($text);
	}
	/**
	 * Generate a valid Google Translate request token.
	 *
	 * @param string $a text to translate
	 *
	 * @return string
	 */
	public static function TL($a) {
		$tkk = self::TKK();
		$b = $tkk[0];
		for ($d = [], $e = 0, $f = 0; $f < mb_strlen($a, 'UTF-8'); $f++) {
			$g = self::charCodeAt($a, $f);
			if (128 > $g) {
				$d[$e++] = $g;
			} else {
				if (2048 > $g) {
					$d[$e++] = $g >> 6 | 192;
				} else {
					if (55296 == ($g & 64512) && $f + 1 < mb_strlen($a, 'UTF-8') && 56320 == (self::charCodeAt($a, $f + 1) & 64512)) {
						$g = 65536 + (($g & 1023) << 10) + (self::charCodeAt($a, ++$f) & 1023);
						$d[$e++] = $g >> 18 | 240;
						$d[$e++] = $g >> 12 & 63 | 128;
					} else {
						$d[$e++] = $g >> 12 | 224;
					}
					$d[$e++] = $g >> 6 & 63 | 128;
				}
				$d[$e++] = $g & 63 | 128;
			}
		}
		$a = $b;
		for ($e = 0; $e < count($d); $e++) {
			$a += $d[$e];
			$a = self::RL($a, '+-a^+6');
		}
		$a = self::RL($a, '+-3^+b+-f');
		$a ^= $tkk[1] ? $tkk[1] + 0 : 0;
		if (0 > $a) {
			$a = ($a & 2147483647) + 2147483648;
		}
		$a = fmod($a, pow(10, 6));
		return $a . '.' . ($a ^ $b);
	}
	/**
	 * @return array
	 */
	private static function TKK() {
		return ['406398', (561666268 + 1526272306)];
	}
	/**
	 * Process token data by applying multiple operations.
	 * (Params are safe, no need for multibyte functions)
	 *
	 * @param int $a
	 * @param string $b
	 *
	 * @return int
	 */
	private static function RL($a, $b) {
		for ($c = 0; $c < strlen($b) - 2; $c += 3) {
			$d = $b[$c + 2];
			$d = 'a' <= $d ? ord($d[0]) - 87 : intval($d);
			$d = '+' == $b[$c + 1] ? self::unsignedRightShift($a, $d) : $a << $d;
			$a = '+' == $b[$c] ? ($a + $d & 4294967295) : $a ^ $d;
		}
		return $a;
	}
	/**
	 * Unsigned right shift implementation
	 * https://msdn.microsoft.com/en-us/library/342xfs5s(v=vs.94).aspx
	 * http://stackoverflow.com/a/43359819/2953830
	 *
	 * @param $a
	 * @param $b
	 *
	 * @return number
	 */
	private static function unsignedRightShift($a, $b) {
		if ($b >= 32 || $b < -32) {
			$m = (int) ($b / 32);
			$b = $b - ($m * 32);
		}
		if ($b < 0) {
			$b = 32 + $b;
		}
		if ($b == 0) {
			return (($a >> 1) & 0x7fffffff) * 2 + (($a >> $b) & 1);
		}
		if ($a < 0) {
			$a = ($a >> 1);
			$a &= 2147483647;
			$a |= 0x40000000;
			$a = ($a >> ($b - 1));
		} else {
			$a = ($a >> $b);
		}
		return $a;
	}
	/**
	 * Get the Unicode of the character at the specified index in a string.
	 *
	 * @param string $str
	 * @param int    $index
	 *
	 * @return null|number
	 */
	private static function charCodeAt($str, $index) {
		$char = mb_substr($str, $index, 1, 'UTF-8');
		if (mb_check_encoding($char, 'UTF-8')) {
			$ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');
			$result = hexdec(bin2hex($ret));
			return $result;
		}
		return;
	}
}