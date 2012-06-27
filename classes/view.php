<?php

namespace Pjax;

use Config;
use Input;
use Log;

class View extends \Fuel\Core\View
{
    /**
     * @var bool Was this view loaded with PJAX.
     */
    protected static $pjax = false;

    /**
     * @var bool Was a pjax view file loaded, ie, file-pjax.php compared to file.php.
     */
    protected $pjax_file_loaded = false;

    /**
     * Check for PJAX and return appropriate view data.
     */
    public static function forge($file = null, $data = null, $auto_filter = null)
    {
        Config::load('pjax', true);

        // Tag used to denote the pjax content
        $tag = Config::get('pjax.tag', '{# PJAX #}');

        // Is this a PJAX request
        if(!Input::server('HTTP_X_PJAX') AND !Input::get('_pjax'))
        {
            //Nope, return standard view
            $view = parent::forge($file, $data, $auto_filter);
            return str_replace($tag, '', $view);
        }

        Log::info('View file loaded WITH PJAX: '.$file);

        // Set that this view is being loaded with PJAX
        static::$pjax = true;

        // Grab the view, checking whether a pjax version of the file exists
        $view = parent::forge($file, $data, $auto_filter);

        // If we loaded a PJAX file then there's no more processing todo
        if($view->pjax_file_loaded)
        {
            return $view;
        }

        Log::info('Pjax\View::forge - parsing view file for PJAX tags');

        // Process same view file for PJAX support
        $pjax = array();

        // Try and get the pages title
        preg_match('@<title>([^<]+)</title>@', $view, $matches);
        $pjax['title'] = (isset($matches[0])) ? $matches[0] : '';

        // Get PJAX content
        $start = stripos($view, $tag);

        if($start === false)
        {
            // Couldnt find opening tag
            Log::warning('Pjax\View::forge - could not find opeing tag: '.$tag);

            // Return whole view
            static::$pjax = false;
            return str_replace($tag, '', $view);
        }

        $str = substr($view, $start);
        $end = strripos($str, $tag);

        if($end === 0)
        {
            // Means there was an opening but no closing tag
            Log::warning('Pjax\View::forge - could not find closing tag: '.$tag);

            // Return whole view
            static::$pjax = false;
            return str_replace($tag, '', $view);
        }

        Log::info('Pjax\View::forge - view file parsed as pjax content');

        $pjax['content'] = substr($str, (0 + strlen($tag)), ($end - strlen($tag)));

        return trim($pjax['title'].$pjax['content']);
    }

    public function set_filename($file)
    {
        // set find_file's one-time-only search paths
        \Finder::instance()->flash($this->request_paths);

        // Check if this request is being made by PJAX
        if(static::$pjax)
        {
            $pjax_file = explode('.', $file);
            $pjax_file = $pjax_file[0].Config::get('pjax.file', '-pjax');

            // locate the pjax view file
            if(($path = \Finder::search('views', $pjax_file, '.'.$this->extension, false, false)) !== false)
            {
                $this->pjax_file_loaded = true;
            }
            else
            {
                // PJAX file not found, carry on looking for normal view file
                if (($path = \Finder::search('views', $file, '.'.$this->extension, false, false)) === false)
                {
                    throw new \FuelException('The requested view could not be found: '.\Fuel::clean_path($file));
                }
            }

            Log::info('Pjax\View::forge - loaded WITH PJAX: '.$path);
        }
        else
        {
            // locate the view file
            if (($path = \Finder::search('views', $file, '.'.$this->extension, false, false)) === false)
            {
                throw new \FuelException('The requested view could not be found: '.\Fuel::clean_path($file));
            }

            Log::info('Pjax\View::forge - loaded WITHOUT PJAX: '.$path);
        }

        // Store the file path locally
        $this->file_name = $path;

        return $this;
    }
}