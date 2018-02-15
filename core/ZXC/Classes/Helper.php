<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 10/01/2018
 * Time: 22:01
 */

namespace ZXC\Classes;


use ZXC\Patterns\Singleton;

class Helper
{
    use Singleton;

    public static $alphabet = [
        'a',
        'A',
        'b',
        'B',
        'c',
        'C',
        'd',
        'D',
        'e',
        'E',
        'f',
        'F',
        'g',
        'G',
        'h',
        'H',
        'i',
        'I',
        'j',
        'J',
        'k',
        'K',
        'l',
        'L',
        'm',
        'M',
        'n',
        'N',
        'o',
        'O',
        'p',
        'P',
        'q',
        'Q',
        'r',
        'R',
        's',
        'S',
        't',
        'T',
        'u',
        'U',
        'v',
        'V',
        'w',
        'W',
        'z',
        'Z',
        'Y',
        'y',
        'x',
        'X',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        '0'
    ];

    public static function isAssoc($arr)
    {
        if (array() === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public static function isWindows()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return true;
        } else {
            return false;
        }
    }

    public static function isValidLogin($login = '')
    {
        if (!$login) {
            return false;
        }
        return preg_match('/^[A-Za-z][A-Za-z0-9]{3,20}$/', $login);
    }

    public static function isValidPassword($sourcePassword)
    {
        //TODO
    }

    public static function isStrengthPassword($password)
    {
        //TODO
        return true;
    }

    public static function isEmail($email = null, $mx = true)
    {
        if (!$email) {
            return false;
        }
        if (!$mx) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return true;
            }
        }
        return filter_var($email, FILTER_VALIDATE_EMAIL) && (getmxrr(substr($email, strrpos($email, '@') + 1),
                $hosts));

    }

    public static function getCleanEmail($email)
    {
        $result = strtolower(filter_var($email, FILTER_SANITIZE_EMAIL));
        return $result;
    }

    public static function getPasswordHash($password = null, $cost = 10)
    {
        if ($password === null) {
            throw new \InvalidArgumentException('Password is not defined');
        }
        $options = [
            'cost' => $cost,
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public static function passwordVerify($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public static function isIP($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    public static function equal($val1, $val2)
    {
        return $val1 === $val2;
    }

    public static function createHash()
    {
        return md5(uniqid() . time() . rand(0, 150));
    }

    public static function getResponse(int $code = 500, array $data = [])
    {
        return ['status' => $code, 'data' => $data];
    }

    public static function generateRandomText($minLength, $maxLength, $registry = true, $ignoreSymbols = [])
    {
        $charsCount = count(self::$alphabet) - 1;
        $length = rand($minLength, $maxLength);
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $symbol = self::$alphabet[rand(0, $charsCount)];
            if (in_array($symbol, $ignoreSymbols, true)) {
                while (in_array($symbol, $ignoreSymbols, true)) {
                    $symbol = self::$alphabet[rand(0, $charsCount)];
                }
            }
            if ($registry) {
                $str .= $symbol;
            } else {
                $str .= strtolower($symbol);
            }
        }
        return $str;
    }
}