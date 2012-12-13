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
        $is_pjax = (Input::server('HTTP_X_PJAX') or Input::get('_pjax'));
		
        if (func_num_args())
        {
			Config::load('pjax');
			$tag = Config::get('tag', '{# PJAX #}');
			
            $this->body = (!$is_pjax) ? str_replace($tag, '', $value) : $this->filter_pjax($value);
			
            return $this;
        }

        return (!$is_pjax) ? $this->body : $this->filter_pjax($this->body);
    }

    protected function filter_pjax($view)
    {
        Log::info('Pjax\View::forge - parsing view file for PJAX tags');

        Config::load('pjax', true);

        $view    = (string)$view;
        $tag     = Config::get('pjax.tag', '{# PJAX #}');
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