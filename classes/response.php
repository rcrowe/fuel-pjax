<?php

namespace Pjax;

use Config;
use Input;
use Log;

class Response extends \Fuel\Core\Response
{
    /**
     * Sets (or returns) the body for the response
     *
     * @param   string  The response content
     *
     * @return  Response|string
     */
    public function body($value = false)
    {
        Config::load('pjax');

        $tag     = Config::get('tag', '{# PJAX #}');
        $is_pjax = (Input::server('HTTP_X_PJAX') or Input::get('_pjax'));

        if (func_num_args())
        {
            $this->body = (!$is_pjax) ? str_replace($tag, '', $value) : $this->filter_pjax($tag, $value);
            return $this;
        }

        return (!$is_pjax) ? str_replace($tag, '', $this->body) : $this->filter_pjax($tag, $this->body);
    }

    protected function filter_pjax($tag, $view)
    {
        Log::info('Pjax\View::forge - parsing view file for PJAX tags');

        $view    = (string)$view;
        $title   = '';
        $content = '';

        // Try to extract a title
        preg_match('@<title>([^<]+)</title>@', $view, $matches);
        $title = (isset($matches[0])) ? $matches[0] : '';

        // Get PJAX content
        $start = stripos($view, $tag);

        if($start === false)
        {
            // Couldnt find opening tag
            Log::info('Pjax\View::forge - could not find opeing tag: '.$tag);

            // Just return the whole view
            return $view;
        }

        $str = substr($view, $start);
        $end = strripos($str, $tag);

        if($end === 0)
        {
            // Means there was an opening but no closing tag
            Log::info('Pjax\View::forge - could not find closing tag: '.$tag);

            // Just return the whole view
            return $view;
        }

        Log::info('Pjax\View::forge - view file parsed as pjax content');

        $content = substr($str, (0 + strlen($tag)), ($end - strlen($tag)));

        return trim($title.$content);
    }
}