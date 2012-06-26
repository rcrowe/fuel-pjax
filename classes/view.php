<?php

namespace Pjax;

use Config;
use Input;

class View extends \Fuel\Core\View
{
    /**
     * Returns a new View object. If you do not define the "file" parameter,
     * you must call [static::set_filename].
     *
     *     $view = View::forge($file);
     *
     * @param   string  view filename
     * @param   array   array of values
     * @return  View
     */
    public static function forge($file = null, $data = null, $auto_filter = null)
    {
        Config::load('jax', true);

        $view = parent::forge($file, $data, $auto_filter);
        $tag  = Config::get('jax.tag', '{# PJAX #}');

        if(!Input::server('HTTP_X_PJAX') AND !Input::get('_pjax'))
        {
            // This isn't a PJAX request
            // send back the normal view after removing tags
            return str_replace($tag, '', $view);
        }

        // Get title
        preg_match('@<title>([^<]+)</title>@', $view, $matches);
        $pjax['title'] = (isset($matches[0])) ? $matches[0] : '';


        // Get PJAX content
        $start = stripos($view, $tag);

        if($start === false)
        {
            // Couldnt find opening tag
            // Return whole view
            return str_replace($tag, '', $view);
        }

        $str = substr($view, $start);
        $end = strripos($str, $tag);

        if($end === 0)
        {
            // Means there was an opening but no closing tag
            // Return whole view
            return str_replace($tag, '', $view);
        }

        $pjax['content'] = substr($str, (0 + strlen($tag)), ($end - strlen($tag)));


        return trim($pjax['title'].$pjax['content']);
    }
}