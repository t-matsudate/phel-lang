<?php

declare(strict_types=1);

namespace Phel\Printer\TypePrinter;

/**
 * @implements TypePrinterInterface<string>
 */
final class StringPrinter implements TypePrinterInterface
{
    private const SPECIAL_CHARACTERS = [
        9 => '\t',
        10 => '\n',
        11 => '\v',
        12 => '\f',
        13 => '\r',
        27 => '\e',
        34 => '\"',
        36 => '\$',
        92 => '\\\\',
    ];

    private bool $readable;

    public function __construct(bool $readable)
    {
        $this->readable = $readable;
    }

    /**
     * @param string $str
     */
    public function print($str): string
    {
        if (!$this->readable) {
            return $str;
        }

        return $this->readCharacters($str);
    }

    private function readCharacters(string $str): string
    {
        $return = '';
        for ($index = 0, $length = strlen($str); $index < $length; ++$index) {
            $asciiChar = ord($str[$index]);

            if (isset(self::SPECIAL_CHARACTERS[$asciiChar])) {
                $return .= self::SPECIAL_CHARACTERS[$asciiChar];
                continue;
            }

            if ($this->isAsciiCharacter($asciiChar)) {
                $return .= $str[$index];
                continue;
            }

            if ($this->isMaskCharacter($asciiChar)) {
                $maskIndex = $this->maskIndexValue($asciiChar);
                $return .= $this->readMask($asciiChar, $str, $index, $maskIndex);
                $index += $maskIndex;
                continue;
            }

            if ($asciiChar < 31 || $asciiChar > 126) {
                $return .= '\x' . str_pad(dechex($asciiChar), 2, '0', STR_PAD_LEFT);
                continue;
            }

            $return .= $str[$index];
        }

        return '"' . $return . '"';
    }

    /**
     * Characters U-00000000 - U-0000007F (same as ASCII).
     */
    private function isAsciiCharacter(int $asciiChar): bool
    {
        return $asciiChar >= 32 && $asciiChar <= 127;
    }

    private function isMaskCharacter(int $asciiChar): bool
    {
        return $this->isMask110XXXXX($asciiChar)
            || $this->isMask1110XXXX($asciiChar)
            || $this->isMask11110XXX($asciiChar);
    }

    /**
     * Characters U-00000080 - U-000007FF, mask 110XXXXX.
     */
    private function isMask110XXXXX(int $asciiChar): bool
    {
        return ($asciiChar & 0xE0) === 0xC0;
    }

    /**
     * Characters U-00000800 - U-0000FFFF, mask 1110XXXX.
     */
    private function isMask1110XXXX(int $asciiChar): bool
    {
        return ($asciiChar & 0xF0) === 0xE0;
    }

    /**
     * Characters U-00010000 - U-001FFFFF, mask 11110XXX.
     */
    private function isMask11110XXX(int $asciiChar): bool
    {
        return ($asciiChar & 0xF8) === 0xF0;
    }

    private function maskIndexValue(int $asciiChar): int
    {
        if ($this->isMask110XXXXX($asciiChar)) {
            return 1;
        }

        if ($this->isMask1110XXXX($asciiChar)) {
            return 2;
        }

        return 3;
    }

    private function readMask(int $asciiChar, string $str, int $index, int $maskIndex): string
    {
        $hex = $this->utf8ToUnicodePoint(substr($str, $index, $maskIndex + 1));

        if ($this->isMask110XXXXX($asciiChar)) {
            return sprintf('\u{%04s}', $hex);
        }

        if ($this->isMask1110XXXX($asciiChar)) {
            return sprintf('\u{%04s}', $hex);
        }

        return sprintf('\u{%04s}', $hex);
    }

    private function utf8ToUnicodePoint(string $str): string
    {
        $a = ($str = unpack('C*', $str)) ? ((int) $str[1]) : 0;
        if (0xF0 <= $a) {
            return dechex((($a - 0xF0) << 18) + ((((int) $str[2]) - 0x80) << 12) + ((((int) $str[3]) - 0x80) << 6) + ((int) $str[4]) - 0x80);
        }
        if (0xE0 <= $a) {
            return dechex((($a - 0xE0) << 12) + ((((int) $str[2]) - 0x80) << 6) + ((int) $str[3]) - 0x80);
        }
        if (0xC0 <= $a) {
            return dechex((($a - 0xC0) << 6) + ((int) $str[2]) - 0x80);
        }

        return (string) $a;
    }
}
