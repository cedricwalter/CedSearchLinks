<?php
/**
 * @package     CedSearchLinks
 * @subpackage  com_cedsearchlinks
 *
 * @copyright   Copyright (C) 2013-2017 galaxiis.com All rights reserved.
 * @license     The author and holder of the copyright of the software is Cédric Walter. The licensor and as such issuer of the license and bearer of the
 *              worldwide exclusive usage rights including the rights to reproduce, distribute and make the software available to the public
 *              in any form is Galaxiis.com
 *              see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class plgContentCedSearchLinksParser
{
    const PATTERN = "/#(\w+)/i";
    const START = '#';

    public function __construct(& $subject, $config)
    {
    }

    public static function active($text) {
        if (strpos($text, self::START) === false) {
            return false;
        }

        return true;
    }

    public static function parse($text)
    {
        $models = array();

        preg_match_all(self::PATTERN, strip_tags($text), $matches, PREG_SET_ORDER);

        // plugin only processes if there are any instances of the plugin in the text
        if ($matches) {
            foreach ($matches as $match) {
                if ($match[0] != "#160") {
                    $model = new stdClass();
                    $model->word = $match[1];
                    $model->match = $match[0];
                    $models[] = $model;
                }

            }
        }

        return $models;
    }

}