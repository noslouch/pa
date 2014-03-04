<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Structure_Helper
{
    public static function remove_double_slashes($str)
    {
        return preg_replace("#(^|[^:])//+#", "\\1/", $str);
    }

    /**
     * Resolves a given $value, if a closure, calls closure, otherwise returns $value
     *
     * @param mixed  $value  Value or closure to resolve
     * @return mixed
     */
    public static function resolveValue($value)
    {
        return (is_callable($value) && !is_string($value)) ? call_user_func($value) : $value;
    }

    public static function tidy_url($url)
    {
        return self::remove_double_slashes('/' . $url);
    }

    public static function get_slug($url)
    {
        $segments = explode('/', trim($url, '/'));

        return end($segments);
    }
}

function structure_array_get($array, $key, $default = NULL)
{
    if (is_null($key)) return $array;

    foreach (explode(':', $key) as $segment) {
        if ( ! is_array($array) or ! array_key_exists($segment, $array)) {
            return Structure_Helper::resolveValue($default);
        }
        $array = $array[$segment];
    }

    return $array;
}

/**
 * Picks the first value that isn't null or an empty string
 *
 * @return mixed
 */
function pick()
{
    $args = func_get_args();

    if (!is_array($args) || !count($args)) {
        return NULL;
    }

    foreach ($args as $arg) {
        if (!is_null($arg) && $arg !== '') {
            return $arg;
        }
    }
}

/**
 * Dump the given value and kill the script.
 *
 * @param  mixed  $value
 * @return void
 */
function structure_dd($value)
{
    echo "<pre>";
    var_dump($value);
    echo "</pre>";
    die;
}

/**
 * Print_r the given value and kill the script.
 *
 * @param  mixed  $value
 * @return void
 */
function rd($value)
{
    echo "<pre>";
  print_r($value);
  echo "</pre>";
  die;
}
