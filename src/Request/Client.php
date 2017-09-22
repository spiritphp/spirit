<?php

namespace Spirit\Request;

use Spirit\Request\Session;

class Client
{

    protected static function getSession($key)
    {
        if (!Session::has('user_agent')) {
            return null;
        }

        $s = Session::get('user_agent');

        if (isset($s[$key])) {
            return $s[$key];
        }

        return null;
    }

    protected static function setSession($key, $value)
    {
        $s = Session::get('user_agent');

        if (!$s || !is_array($s)) {
            $s = [];
        }

        $s[$key] = $value;

        Session::set('user_agent',$s);
    }

    public static function getOS($ua = false, $force = false)
    {
        if (!$force && $os = static::getSession('os')) {
            return $os;
        }

        $os_platform = "Unknown OS Platform";

        if (!isset($_SERVER['HTTP_USER_AGENT']) && !$ua) {
            return $os_platform;
        }

        if (!$ua) $ua = $_SERVER['HTTP_USER_AGENT'];


        $os_array = [
            'Android' => '/android/i',
            'Symbian' => '/(Symbian|SymbOS|Series60|Series40|SYB\-[0-9]+|\bS60\b)/i',
            'iOS' => '/(\biPhone.*Mobile|\biPod|\biPad)/i',
            'Bada' => '/\bBada\b/i',
            'JavaOS' => '/(J2ME\/|\bMIDP\b|\bCLDC\b)/i',
            'WindowsMobileOS' => '/(Windows CE.*(PPC|Smartphone|Mobile|[0-9]{3}x[0-9]{3})|Window Mobile|Windows Phone [0-9\.]+|WCE;)/i',
            'WindowsPhoneOS' => '/(Windows Phone 8\.0|Windows Phone OS|XBLWP7|ZuneWP7|Windows NT 6\.[23]; ARM;)/i',

            'Windows 10' => '/(windows 10|windows nt 10)/i',
            'Windows 8.1' => '/(windows 8\.1|windows nt 6\.3)/i',
            'Windows 8' => '/(windows 8|windows nt 6\.2)/i',
            'Windows 7' => '/(windows 7|windows nt 6\.1)/i',
            'Windows Vista' => '/windows nt 6\.0/i',
            'Windows Server' => '/windows nt 5\.2/i',
            'Windows XP' => '/(windows xp|windows nt 5\.1)/i',
            'Windows 2000' => '/(windows 2000|windows nt 5\.0)/i',
            'Windows CE' => '/windows ce/i',
            'Windows ME' => '/(win 9x 4\.90|windows me)/i',
            'Windows 98' => '/(windows 98|win98)/i',
            'Windows 95' => '/(windows_95|windows 95|win95)/i',
            'Windows 3.11' => '/win16/i',
            'Windows Unknown' => '/windows/i',

            'Mac OS X' => '/(macintosh|mac os x)/i',
            'Mac OS' => '/(macppc|macintel|mac_powerpc|macintosh)/i',

            'BlackBerry' => '/(blackberry|\bBB10\b|rim tablet os)/i',
            'PalmOS' => '/(PalmOS|avantgo|blazer|elaine|hiptop|palm|plucker|xiino)/i',
            'MeeGoOS' => '/MeeGo/i',

            'Linux' => '/(linux|x11)/i',
            'Open BSD' => '/openbsd/i',
            'Sun OS' => '/sunos/i',
            'QNX' => '/qnx/i',
            'UNIX' => '/unix/i',
            'BeOS' => '/beos/i',
            'OS/2' => '/os\/2/i',

            'Mobile' => '/webos/i'
        ];

        foreach ($os_array as $value => $regex) {
            if (preg_match($regex, $ua)) {
                $os_platform = $value;
                break;
            }

        }

        static::setSession('os', $os_platform);

        return $os_platform;

    }

    public static function getBrowser($ua = false, $force = false)
    {
        if (!$force && $browser = static::getSession('browser')) {
            return $browser;
        }

        $browser = "Unknown Browser";

        if (!isset($_SERVER['HTTP_USER_AGENT']) && !$ua) {
            return $browser;
        }

        if (!$ua) $ua = $_SERVER['HTTP_USER_AGENT'];

        $browser_array = [
            'Opera Mini' => '/opera mini/i',
            'Opera Mobile' => '/opera mobi/i',
            'UC Browser' => '/uc.*browser|ucweb/i',
            'Chrome' => '/chrome/i',
            'Firefox' => '/firefox/i',
            'Opera' => '/opera/i',
            'Internet Explorer' => '/msie|trident/i',
            'Dolfin' => '/dolfin/i',
            'Netscape' => '/netscape/i',
            'Skyfire' => '/Skyfire/i',
            'Bolt' => '/bolt/i',
            'TeaShark' => '/teashark/i',
            'Blazer' => '/Blazer/i',
            'Tizen' => '/Tizen/i',
            'baiduboxapp' => '/baiduboxapp/i',
            'baidubrowser' => '/baidubrowser/i',
            'DiigoBrowser' => '/DiigoBrowser/i',
            'Puffin' => '/Puffin/i',
            'Mercury' => '/\bMercury\b/i',
            'ObigoBrowser' => '/Obigo/i',
            'NetFront' => '/NF-Browser/i',
            'GenericBrowser' => '/NokiaBrowser|OviBrowser|OneBrowser|TwonkyBeamBrowser|SEMC.*Browser|FlyFlow|Minimo|NetFront|Novarra-Vision|MQQBrowser|MicroMessenger/i',
            'Handheld Browser' => '/mobile/i',
            'Safari' => '/webkit|safari|khtml/i',
        ];

        foreach ($browser_array as $value => $regex) {

            if (preg_match($regex, $ua)) {
                $browser = $value;
                break;
            }

        }

        static::setSession('browser', $browser);

        return $browser;
    }

    public static function isOperaMini($ua = false)
    {
        if (!$ua) $ua = $_SERVER['HTTP_USER_AGENT'];

        return preg_match("/opera mini/i", $ua);
    }

    public static function isMobile($ua = false)
    {
        if (!$ua) $ua = $_SERVER['HTTP_USER_AGENT'];

        return preg_match("/(iphone|ipod|ipad|opera mini|opera mobi|iemobile|android)/i", $ua);
    }

    public static function isSearchBot($ua = false)
    {
        if (!$ua) $ua = $_SERVER['HTTP_USER_AGENT'];

        return preg_match("/(yandex|google|stackrambler|aport|slurp|msnbot|bingbot|ia_archiver)/i", $ua);
    }

    public static function getIP()
    {
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            return '127.0.0.1';
        }

        $ip = $_SERVER['REMOTE_ADDR'];

        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return $ip;
        }

        return $ip;
    }

    public static function getBrowserAndOS()
    {
        return [
            'os' => static::getOS(),
            'browser' => static::getBrowser(),
        ];
    }

    public static function hash()
    {
        return md5(implode('|', static::getBrowserAndOS()));
    }
}