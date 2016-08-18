<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 4/2/14
 * Time: 10:27 AM
 */

if(!function_exists('boolval')) {
    function boolval($x) {
        return $x ? true : false;
    }
}

function arrayval($x) {
    return is_array($x) ? $x : array();
}


/**
 * 	作用：array转xml
 */
function arrayToXml($arr)
{
    $xml = "<xml>";
    foreach ($arr as $key=>$val)
    {
        if (is_numeric($val))
        {
            $xml.="<".$key.">".$val."</".$key.">";

        }
        else
            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    }
    $xml.="</xml>";
    return $xml;
}

/**
 * 	作用：将xml转为array
 */
function xmlToArray($xml)
{
    //将XML转为array
    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
}

// 把字符串转成数组，支持汉字，只能是utf-8格式的
function StringToArray($str) {
    $result = array ();
    $len = strlen ( $str );
    $i = 0;
    while ( $i < $len ) {
        $chr = ord ( $str [$i] );
        if ($chr == 9 || $chr == 10 || (32 <= $chr && $chr <= 126)) {
            $result [] = substr ( $str, $i, 1 );
            $i += 1;
        } elseif (192 <= $chr && $chr <= 223) {
            $result [] = substr ( $str, $i, 2 );
            $i += 2;
        } elseif (224 <= $chr && $chr <= 239) {
            $result [] = substr ( $str, $i, 3 );
            $i += 3;
        } elseif (240 <= $chr && $chr <= 247) {
            $result [] = substr ( $str, $i, 4 );
            $i += 4;
        } elseif (248 <= $chr && $chr <= 251) {
            $result [] = substr ( $str, $i, 5 );
            $i += 5;
        } elseif (252 <= $chr && $chr <= 253) {
            $result [] = substr ( $str, $i, 6 );
            $i += 6;
        }
    }
    return $result;
}